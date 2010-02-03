<?php
class modules_form_tests_FieldServiceTest extends f_tests_AbstractBaseTest
{
	/**
	 * @var f_persistentdocument_TransactionManager
	 */
	private $tm;

    public function prepareTestCase()
    {
    	$this->truncateAllTables();
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');
		$this->tm = $this->getTransactionManager();
    }


	private $formIndex = 1;
    private function getNewForm()
    {
		$form = form_FormService::getInstance()->getNewDocumentInstance();
		$form->setLabel('Test form '.($this->formIndex++));
		$form->setSubmitButton('Hop!');
		$form->setConfirmMessage('Thank you for testing!');
		try
		{
			$this->tm->beginTransaction();
			$form->save(ModuleService::getInstance()->getRootFolderId('form'));
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
			throw $e;
		}
		return $form;
    }


	public function testPreSave()
	{
		$field = form_fieldservicetest_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		try
		{
			$field->save();
			$this->fail("Field saved without a parent form.");
		}
		catch (Exception $e)
		{ }

		$form = $this->getNewForm();
		$field->save($form->getId());
	}

/*
	public function testGetFormOf()
	{
		$field = form_fieldservicetest_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$form = $this->getNewForm();
		$field->save($form->getId());
		$this->assertEquals($form, $field->getDocumentService()->getFormOf($field));
	}


	public function testLockedFields()
	{
		$field = form_fieldservicetest_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$field->setIsLocked(true);
		$form = $this->getNewForm();
		$field->save($form->getId());
		$this->assertTrue($field->getIsLocked());

		$field->setLabel('A new field');
		try
		{
			$field->save();
			$this->fail("A locked field has been saved.");
		}
		catch (form_FieldLockedException $e)
		{ }

		try
		{
			$field->delete();
			$this->fail("A locked field has been deleted.");
		}
		catch (form_FieldLockedException $e)
		{ }
	}


	public function testPublication()
	{
		$field = form_fieldservicetest_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$form = $this->getNewForm();
		$field->save($form->getId());
		$this->assertTrue($field->getDocumentService()->isPublicated($field));
		$this->assertTrue($field->getDocumentService()->isPublishable($field));
	}

	public function testMove()
	{
		$form1 = $this->getNewForm();
		$form2 = $this->getNewForm();

		// Save field into form1
		$field = form_fieldservicetest_FieldServiceMock::getInstance()->getNewDocumentInstance();
		$field->setLabel('A field');
		$field->setFieldName('afield');
		$field->save($form1->getId());

		// Test move the field into another form: NOT allowed
		try
		{
			$field->getDocumentService()->moveTo($field, $form2->getId());
			$this->fail("Field has been moved into another form.");
		}
		catch (form_FormException $e)
		{ }

		// Test move the field into a group, inside the same form: allowed
		$group1 = form_GroupService::getInstance()->getNewDocumentInstance();
		$group1->setLabel('Field group 1');
		$group1->save($form1->getId());
		$field->getDocumentService()->moveTo($field, $group1->getId());
		$this->assertEquals($group1, $field->getDocumentService()->getParentOf($field));
		$this->assertEquals($form1, $field->getDocumentService()->getFormOf($field));

		// Test move the field into a group of another form: NOT allowed
		$group2 = form_GroupService::getInstance()->getNewDocumentInstance();
		$group2->setLabel('Field group 2');
		$group2->save($form2->getId());
		try
		{
			$field->getDocumentService()->moveTo($field, $group2->getId());
			$this->fail("Field has been moved into another form.");
		}
		catch (form_FormException $e)
		{ }
		// nothing should have changed: check field is still at its previous place
		$this->assertEquals($group1, $field->getDocumentService()->getParentOf($field));
		$this->assertEquals($form1, $field->getDocumentService()->getFormOf($field));
	}
*/
}


class form_fieldservicetest_FieldServiceMock extends form_FieldService
{

	/**
	 * @var form_fieldservicetest_FieldServiceMock
	 */
	private static $instance;

	/**
	 * @return form_fieldservicetest_FieldServiceMock
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
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
		return $this->getNewDocumentInstanceByModelName('modules_form/field');
	}
}
