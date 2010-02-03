<?php
class modules_form_tests_ResponseServiceTest extends f_tests_AbstractBaseTest
{

    public function prepareTestCase()
    {
    	$this->truncateAllTables();
    	RequestContext::clearInstance();
		RequestContext::getInstance('fr en')->setLang('fr');
    }


	public function testInstance()
	{
		$response = form_ResponseService::getInstance()->getNewDocumentInstance();
		$this->assertType('form_persistentdocument_response', $response);
	}
}
