<?php
class form_ResponseService extends f_persistentdocument_DocumentService
{
	/**
	 * @var form_ResponseService
	 */
	private static $instance;

	/**
	 * @return form_ResponseService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return form_persistentdocument_response
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/response');
	}

	/**
	 * Create a query based on 'modules_form/response' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/response');
	}

	/**
	 * Replaces all the {fieldName} occurences in $string by the value of the
	 * corresponding field into the given $response.
	 *
	 * @param form_persistentdocument_response $response
	 * @param string $string
	 * @return string
	 */
	public function replaceFieldsValue($response, $string)
	{
		$matches = null;
		$responseData = $response->getData();
		if (preg_match_all('/\{('.substr(form_FieldService::FIELD_NAME_REGEXP, 1, -1).')\}/', $string, $matches))
		{
			foreach ($matches[1] as $index => $possibleFieldName)
			{
				if (isset($responseData[$possibleFieldName]))
				{
					$string = str_replace($matches[0][$index], $responseData[$possibleFieldName], $string);
				}
			}
		}
		return $string;
	}

	/**
	 * @param form_persistentdocument_response $document
	 * @return void
	 */
	protected function postDelete($document)
	{
		$form = $document->getParentForm();
		$form->setResponseCount($form->getResponseCount()-1);
		$form->save();
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return Integer
	 */
	public function fileForForm($form)
	{
	    $count = 0;
	    $responses = $this->createQuery()
	                    ->add(Restrictions::eq('parentForm.id', $form->getId()))
	                    ->add(Restrictions::published())
	                    ->find();
	    foreach ($responses as $response) 
	    {
	    	$this->file($response->getId());
	    	$count++;
	    }
	   
	    return $count;
	}
}