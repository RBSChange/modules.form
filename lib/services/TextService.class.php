<?php
class form_TextService extends form_FieldService
{
	/**
	 * @var form_TextService
	 */
	private static $instance;

	/**
	 * @return form_TextService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return form_persistentdocument_text
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/text');
	}

	/**
	 * Create a query based on 'modules_form/text' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/text');
	}

	/**
	 * @param form_persistentdocument_text $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		parent::preSave($document, $parentNodeId);
		$this->fixLengthConstraints($document);
		
		if ($document->getMultiline())
		{
		    if ($document->getRows() < 2)
		    {
		        $document->setRows(2);
		    }
		}
		else
		{
		    $document->setRows(1);
		}
	}


	/**
	 * @param form_persistentdocument_text $document
	 */
	protected function fixLengthConstraints($document)
	{
		$constraintArray = $document->getConstraintArray();
		$modified = false;
				
		// maxlength
		$maxLength = $document->getMaxlength();
		if ($maxLength)
		{
			if (!isset($constraintArray['maxSize']) || $constraintArray['maxSize'] != $maxLength )
			{
			    $modified = true;
				$constraintArray['maxSize'] = strval($maxLength);
			}
		}
		else if (isset($constraintArray['maxSize']))
		{
		    $modified = true;
			unset($constraintArray['maxSize']);
		}

		// minlength
		$minLength = $document->getMinlength();
		if ($maxLength)
		{
			if (!isset($constraintArray['minSize']) || $constraintArray['minSize'] != $minLength )
			{
			    $modified = true;
				$constraintArray['minSize'] = strval($minLength);
			}
		}
		else if (isset($constraintArray['minSize']))
		{
		    $modified = true;
			unset($constraintArray['minSize']);
		}
		
        if ($modified)
        {
            $document->setConstraintArray($constraintArray);
        }
	}
	
    /**
     * @param form_persistentdocument_text $field
     * @param DOMElement $fieldElm
     * @param mixed $rawValue
     * @return string
     */
    public function buildXmlElementResponse($field, $fieldElm, $rawValue)
    {
        $txtValue = parent::buildXmlElementResponse($field, $fieldElm, $rawValue); 
		if ($field->getMultiline())
		{
			$fieldElm->setAttribute('mailValue', f_util_HtmlUtils::textToHtml($txtValue));
		}
		return $txtValue;
    }
    
    /**
     * @param form_persistentdocument_text $document
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    public function addTreeAttributes ($document, $moduleName, $treeType, &$nodeAttributes)
    {
        parent::addTreeAttributes($document, $moduleName, $treeType, $nodeAttributes);
        $ls = LocaleService::getInstance();
        if ($document->getMultiline())
        {
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.multiline-text', array('ucf'));
        } else
        {
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.text', array('ucf'));
        }
    }
}