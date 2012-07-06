<?php
/**
 * @package modules.form
 * @method form_HiddenService getInstance()
 */
class form_HiddenService extends form_FieldService
{
	/**
	 * @return form_persistentdocument_hidden
	 */
	public function getNewDocumentInstance ()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/hidden');
	}

	/**
	 * Create a query based on 'modules_form/hidden' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery ()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/hidden');
	}

	/**
	 * @param form_persistentdocument_hidden $document
	 * @param integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preSave ($document, $parentNodeId = null)
	{
		parent::preSave($document, $parentNodeId);
		
		$document->setRequired(false);
		$document->setHelpText(null);
		$recommand = $document->getIsRecommand();
		if (!empty($recommand))
		{
			$document->setFieldName('recommandFeature');
		}
	}

	/**
	 * @param form_persistentdocument_hidden $field
	 * @param DOMElement $fieldElm
	 * @param mixed $rawValue
	 * @return string
	 */
	public function buildXmlElementResponse($field, $fieldElm, $rawValue)
	{
		switch ($field->getIsRecommand())
		{
			case 'site':
				return website_WebsiteService::getInstance()->getCurrentWebsite()->getUrl();
				break;
			case 'page':
				try
				{
					return LinkHelper::getDocumentUrl(DocumentHelper::getDocumentInstance($rawValue));
				} 
				catch (Exception $e) // if no link for the given id, send at least the site URL...
				{
					Framework::exception($e);	
				}
				return website_WebsiteService::getInstance()->getCurrentWebsite()->getUrl();
		}
		return parent::buildXmlElementResponse($field, $fieldElm, $rawValue); 
	}
}