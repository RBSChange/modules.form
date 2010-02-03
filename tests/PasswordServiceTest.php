<?php
class modules_form_tests_PasswordServiceTest extends f_tests_AbstractBaseTest
{

    public function prepareTestCase()
    {
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');
    }


	public function testInstance()
	{
		$field = form_PasswordService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_password', $field);
	}

}
