<?php
class modules_form_tests_FormServiceTest extends f_tests_AbstractBaseTest
{

    public function prepareTestCase()
    {
		$this->truncateAllTables();
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');;
    }


	private $formIndex = 1;
    private function getNewForm()
    {
		$form = form_FormService::getInstance()->getNewDocumentInstance();
		$form->setLabel('Test form '.($this->formIndex++));
		$form->setSubmitButton('Hop!');
		$form->setConfirmMessage('Thank you for testing!');
		$form->setMarkup('default');
		$form->save(ModuleService::getInstance()->getRootFolderId('form'));
		return $form;
    }


	public function testInstance()
	{
		$form = form_FormService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_form', $form);
	}


	public function testSave()
	{
		//
		// test automatic 'formid'
		//
		$form = $this->getNewForm();
		$this->assertNotEmpty($form->getFormid());

		//
		// test unique 'formid'
		//
		$form2 = $this->getNewForm();
		$form2->setFormid('testform2');
		$form2->save();

		$form3 = $this->getNewForm();
		$form3->setFormid('testform3');
		$form3->save();

		$form4 = $this->getNewForm();
		$form4->setFormid('testform3'); // same 'formid' here!
		try
		{
			$form4->save();
			$this->fail('Two forms have the same "formid"');
		}
		catch (ValidationException $e)
		{ }

		//
		// test notification
		//
		$form = $this->getNewForm();
		$this->assertType('notification_persistentdocument_notification', $form->getNotification());
		$this->assertEquals($form->getLabel(), $form->getNotification()->getLabel());
		$this->assertEquals($form->getFormid().'_notification', $form->getNotification()->getCodename());

		$newLabel = 'Another label for my wonderful form';
		$form->setLabel($newLabel);
		$form->save();
		$this->assertEquals($newLabel, $form->getLabel());
		$this->assertEquals($newLabel, $form->getNotification()->getLabel());
	}


	public function testCascadeDeleteNotification()
	{
		$form = $this->getNewForm();
		$codeName = $form->getNotification()->getCodename();
		$this->assertType('notification_persistentdocument_notification', $this->pp->createQuery('modules_notification/notification')->add(Restrictions::eq('codename', $codeName))->findUnique());
		$form->delete();
		$this->assertNull($this->pp->createQuery('modules_notification/notification')->add(Restrictions::eq('codename', $codeName))->findUnique());
	}


	public function testIsPublishable()
	{
		$form = $this->getNewForm();
		// form is empty (no field): isPublishable returns false
		$this->assertFalse($form->getDocumentService()->isPublishable($form));

		$field = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$field->save($form->getId());
		// form has one field: isPublishable returns true
		$this->assertTrue($form->getDocumentService()->isPublishable($form));
	}


	public function testGetFieldsAndGetFieldByName()
	{
		//
		// getFields
		//
		$form = $this->getNewForm();
		$this->assertEmpty($form->getDocumentService()->getFields($form));
		$this->assertEmpty($form->getNotification()->getAvailableParameters());

		// Add a field
		$field = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$field->save($form->getId());
		$fieldArray = $form->getDocumentService()->getFields($form);
		$this->assertCount(1, $fieldArray);
		$this->assertType('form_persistentdocument_field', $fieldArray[0]);
		$this->assertEquals($field, $fieldArray[0]);
		$expected = array('afield');
		$expected[] = form_FormService::CONTENT_REPLACEMENT_NAME;
		$expected[] = form_FormService::FORM_LABEL_REPLACEMENT_NAME;
		$this->assertEquals(implode(', ', $expected), $form->getNotification()->getAvailableParameters());

		// Add a second field
		$field2 = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field2->setLabel('A second field');
		$field2->setFieldName('asecondfield');
		$field2->save($form->getId());
		$fieldArray = $form->getDocumentService()->getFields($form);
		$this->assertCount(2, $fieldArray);
		$expected = array('afield', 'asecondfield');
		$expected[] = form_FormService::CONTENT_REPLACEMENT_NAME;
		$expected[] = form_FormService::FORM_LABEL_REPLACEMENT_NAME;
		$this->assertEquals(implode(', ', $expected), $form->getNotification()->getAvailableParameters());

		// Add a field group and a field in it
		$group = form_GroupService::getInstance()->getNewDocumentInstance();
		$group->setLabel('Field group');
		$group->save($form->getId());
		// A group is not a field: check if fields count is still 2
		$this->assertCount(2, $fieldArray);

		// Add a third field
		$field3 = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field3->setLabel('A third field');
		$field3->setFieldName('athirdfield');
		$field3->save($group->getId());
		$fieldArray = $form->getDocumentService()->getFields($form);
		$this->assertCount(3, $fieldArray);
		$expected = array('afield', 'asecondfield', 'athirdfield');
		$expected[] = form_FormService::CONTENT_REPLACEMENT_NAME;
		$expected[] = form_FormService::FORM_LABEL_REPLACEMENT_NAME;
		$this->assertEquals(implode(', ', $expected), $form->getNotification()->getAvailableParameters());

		//
		// getFieldByName
		//
		$this->assertEquals($field3, $form->getDocumentService()->getFieldByName($form, 'athirdfield'));
		$this->assertNull($form->getDocumentService()->getFieldByName($form, 'nonexistentfield'));
	}


	public function testCheckFieldNameAvailable()
	{
		$form = $this->getNewForm();
		$fs = $form->getDocumentService();

		$field = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$field->save($form->getId());

		// field name is available: no exception thrown
		$fs->checkFieldNameAvailable($field, $form->getId());

		$field2 = form_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field2->setLabel('A field');
		$field2->setFieldName('afield');

		// field name is NOT available => form_FieldAlreadyExistsException
		try
		{
			$fs->checkFieldNameAvailable($field2, $form->getId());
			$this->fail("A field with the same name already exists.");
		}
		catch (form_FieldAlreadyExistsException $e)
		{ }
	}


	/**
	 * @param array $parameters
	 * @return block_BlockRequest
	 */
	private function getBlockRequest($parameters = array())
	{
		$blockRequest = block_BlockRequest::getNewInstance();
		$blockRequest->setParameters($parameters);
		return $blockRequest;
	}


	public function testValidate()
	{
		$fs = form_FormServiceMock::getInstance();

		$form = $this->getNewForm();
		$textField = form_TextService::getInstance()->getNewDocumentInstance();
		$textField->setLabel('Firstname');
		$textField->setFieldName('firstname');
		$textField->setMinlength(5);
		$textField->save($form->getId());

		$blockRequest = $this->getBlockRequest(array('firstname' => 'RBS Change'));
		$errors = new validation_Errors();
		$fs->validate($form, $blockRequest, $errors);
		$this->assertEquals(0, $errors->count());

		$blockRequest = $this->getBlockRequest(array('firstname' => 'Fred'));
		$errors = new validation_Errors();
		$fs->validate($form, $blockRequest, $errors);
		$this->assertEquals(1, $errors->count());
	}


	public function testSaveFormData()
	{
		$form = $this->getNewForm();
		$textField = form_TextService::getInstance()->getNewDocumentInstance();
		$textField->setLabel('Firstname');
		$textField->setFieldName('firstname');
		$textField->save($form->getId());

		$fs = form_FormServiceMock::getInstance();

		$blockRequest = $this->getBlockRequest(array('firstname' => 'Fred'));

		// new form: no response!
		$this->assertEquals(0, $form->getResponseCount());

		// save form data: should add a response
		$fs->saveFormData($form, $blockRequest);

		$this->assertEquals(1, $form->getResponseCount());
		//$this->assertType('form_persistentdocument_response', $form->getResponse(0));

		$blockRequest->setParameter('firstname', 'John');
		$this->assertTrue($fs->saveFormData($form, $blockRequest));
		$this->assertEquals(2, $form->getResponseCount());

		$response = f_persistentdocument_PersistentProvider::getInstance()->createQuery('modules_form/response')->add(Restrictions::eq('parentForm.id', $form->getId()))->findUnique();
		$this->assertType('form_persistentdocument_response', $response);
		$response->delete();
		$this->assertEquals(1, $form->getResponseCount());

		// Create new contact
		$contact = contactcard_ContactService::getInstance()->getNewDocumentInstance();
		$contact->setMails('frederic.bonjour@rbs.fr');
		$contact->setLabel('frederic.bonjour@rbs.fr');
		$contact->setCommunicationlanguage('fr');
		$contact->setIndexingstatus(true);
		$contact->save(ModuleService::getInstance()->getRootFolderId('contactcard'));

		$form->addContact($contact);
		$form->save();
		$this->assertTrue($fs->saveFormData($form, $blockRequest));
	}

}



class form_FieldServiceMock extends form_FieldService
{

	/**
	 * @var form_FieldServiceMock
	 */
	private static $instance = null;

	/**
	 * @return form_FieldServiceMock
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			$className = get_class();
			self::$instance = new $className();
		}
		return self::$instance;
	}

	/**
	 * @return form_persistentdocument_date
	 */
	public function getNewDocumentInstance()
	{
		return parent::getNewDocumentInstance('modules_form/field');
	}
}

class form_FormServiceMock extends form_FormService
{
	/**
	 * @var form_FormServiceMock
	 */
	private static $instance = null;

	/**
	 * @return form_FormServiceMock
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			$className = get_class();
			self::$instance = new $className();
		}
		return self::$instance;
	}

	/**
	 * @param form_persistentdocument_form $form
	 * @param block_BlockRequest $request
	 * @param validation_Errors $errors
	 * @return void
	 */
	public function validate($form, $request, &$errors)
	{
		parent::validate($form, $request, $errors);
	}
}
