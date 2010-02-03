<?php
class modules_form_tests_TextServiceTest extends f_tests_AbstractBaseTest
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
		$field = form_TextService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_text', $field);
	}

	public function testPreSave()
	{
		$form = $this->getNewForm();

		$ts = form_TextService::getInstance();
		$field = $ts->getNewDocumentInstance();
		$field->setLabel('A text field');
		$field->setFieldName('atextfield');
		$field->save($form->getId());
		// default values in the document model are:
		// maxlength=255
		// minlength=0
		$this->assertTrue('maxSize:255;minSize:0' == $field->getValidators() || 'minSize:0;maxSize:255' == $field->getValidators());

		$field->setMaxlength(80);
		$field->save();
		$this->assertTrue('maxSize:80;minSize:0' == $field->getValidators() || 'minSize:0;maxSize:80' == $field->getValidators());
		$field->setMinlength(10);
		$field->save();
		$this->assertTrue('maxSize:80;minSize:10' == $field->getValidators() || 'minSize:10;maxSize:80' == $field->getValidators());
	}

}
