<?php
class form_ListService extends form_FieldService
{
	/**
	 * @var form_ListService
	 */
	private static $instance;

	/**
	 * @return form_ListService
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
	 * @return form_persistentdocument_list
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/list');
	}

	/**
	 * Create a query based on 'modules_form/list' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/list');
	}

	/**
	 * @see form_FieldService::buildXmlElementResponse()
	 *
	 * @param form_persistentdocument_List $field
	 * @param DOMElement $fieldElm
	 * @param mixed $rawValue
	 * @return string
	 */
	public function buildXmlElementResponse($field, $fieldElm, $rawValue)
	{
		$listObject = $field->getDataSource();
		if (!$field->getMultiple() || !is_array($rawValue))
		{
			$realValue = f_util_Convert::toString($rawValue);
			if (!empty($realValue))
			{
				$item = $listObject->getItemByValue($realValue);
				if ($item != null)
				{
					$mailValue = $item->getLabel();
				}
				else
				{
					$mailValue = $realValue;
				}
				
				if (!empty($mailValue))
				{
					$fieldElm->setAttribute('mailValue', $mailValue);
				}
			}
			return $realValue;
		} 
		else if (is_array($rawValue))
		{
			$realValue = array();
			$mailValue = array();
			
			foreach ($rawValue as $val) 
			{
				$txtval = f_util_Convert::toString($val);
				if (!empty($txtval))
				{
					$realValue[] = $txtval;
					$item = $listObject->getItemByValue($txtval);
					if ($item != null)
					{
						$mailValue[] = $item->getLabel();
					}
					else
					{
						$mailValue[] = $txtval;
					}
				}
			}

			if (count($realValue) > 0)
			{
				$fieldElm->setAttribute('mailValue',  implode(" / ", $mailValue));
				return implode(' / ', $realValue);
			}
			return '';
		}
		
		return parent::buildXmlElementResponse($fieldElm, $fieldElm, $rawValue);
	}

    /**
     * @param form_persistentdocument_list $document
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
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.multiple-selection-list', array('ucf'));
        }
        else
        {
            $nodeAttributes['fieldType'] = $ls->transBO('m.form.bo.general.field.single-selection-list', array('ucf'));
        }
        $nodeAttributes['fieldName'] = $document->getFieldName();
    }
}