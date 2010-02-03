<?php
class modules_form_tests_BooleanServiceTest extends f_tests_AbstractBaseTest
{

    public function prepareTestCase()
    {
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');
    }


	public function testInstance()
	{
		$field = form_BooleanService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_boolean', $field);
	}

}
