<?php
class form_RecipientGroupListService extends form_ListService
{
	/**
	 * @var form_RecipientGroupListService
	 */
	private static $instance;

	/**
	 * @return form_RecipientGroupListService
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
	 * @return form_persistentdocument_recipientGroupList
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/recipientGroupList');
	}

	/**
	 * Create a query based on 'modules_form/recipientGroupList' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/recipientGroupList');
	}

	/**
	 * @param form_persistentdocument_recipientGroupList $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$document->setFieldName(form_FormService::RECIPIENT_GROUP_FIELD_NAME);
		$document->setDataSource(list_ListService::getInstance()->getDocumentInstanceByListId('modules_form/recipientgrouplist'));
		$document->setRequired(true);
		parent::preSave($document, $parentNodeId);
	}
	
    /**
     * @param form_persistentdocument_file $field
     * @param DOMElement $fieldElm
     * @param mixed $rawValue
     * @return string
     */
    public function buildXmlElementResponse($field, $fieldElm, $rawValue)
    {
		if (is_numeric($rawValue))
		{
		    try
		    {
    			$document = DocumentHelper::getDocumentInstance($rawValue);
    			$fieldElm->setAttribute('mailValue', $document->getLabel());
		    }
		    catch (Exception $e)
		    {
		        Framework::exception($e);
		    }
		}
		return $rawValue;
    }
    
    /**
     * @param form_persistentdocument_recipientGroupList $document
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    public function addTreeAttributes ($document, $moduleName, $treeType, &$nodeAttributes)
    {
        parent::addTreeAttributes($document, $moduleName, $treeType, $nodeAttributes);
        $ls = LocaleService::getInstance();
        if ($document->getMultiple())
        {
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.recipient-multiple-selection-list', array('ucf'));
        }
        else
        {
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.recipient-single-selection-list', array('ucf'));
        }
    }
}