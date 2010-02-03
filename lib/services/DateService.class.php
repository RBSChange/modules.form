<?php
class form_DateService extends form_FieldService
{
	const DEFAULT_VALIDATORS = 'date:d/m/Y';
	const DEFAULT_START_DATE = '1970-01-01';
	const DEFAULT_END_DATE   = '2050-12-31';

	/**
	 * @var form_DateService
	 */
	private static $instance;

	/**
	 * @return form_DateService
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
	 * @return form_persistentdocument_date
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/date');
	}

	/**
	 * Create a query based on 'modules_form/date' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/date');
	}

	/**
	 * @param form_persistentdocument_date $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		$document->setValidators(self::DEFAULT_VALIDATORS);
	}
	
    /**
     * @param form_persistentdocument_date $field
     * @param DOMElement $fieldElm
     * @param mixed $rawValue
     * @return string
     */
    public function buildXmlElementResponse($field, $fieldElm, $rawValue)
    {
        $txtValue = parent::buildXmlElementResponse($field, $fieldElm, $rawValue); 
		if (!empty($txtValue))
		{
		    $txtValue = date_Calendar::getInstanceFromFormat($txtValue, f_Locale::translate('&framework.date.date.default-date-format;'))->toString();
		}
		return $txtValue;
    }
}