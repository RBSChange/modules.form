<?php
class modules_form_tests_DateServiceTest extends f_tests_AbstractBaseTest
{

    public function prepareTestCase()
    {
    	$this->truncateAllTables();
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');
    }

	private $formIndex = 1;
    private function getNewForm()
    {
		$form = form_FormService::getInstance()->getNewDocumentInstance();
		$form->setLabel('Test form '.($this->formIndex++));
		$form->setSubmitButton('Hop!');
		$form->setConfirmMessage('Thank you for testing!');
		$form->save(ModuleService::getInstance()->getRootFolderId('form'));
		return $form;
    }


	public function testInstance()
	{
		$field = form_DateService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_date', $field);
	}


	public function testPreSave()
	{
		$dateField = form_DateService::getInstance()->getNewDocumentInstance();
		$dateField->setLabel('Birth date');
		$dateField->setFieldName('birthdate');
		try
		{
			$dateField->save();
			$this->fail("Field saved without a parent form.");
		}
		catch (Exception $e)
		{ }

		$dateField->save($this->getNewForm());

		$this->assertEquals(form_DateService::DEFAULT_START_DATE, $dateField->getStartDate());
		$this->assertEquals(form_DateService::DEFAULT_END_DATE, $dateField->getEndDate());
	}

}
