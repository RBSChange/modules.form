<?php
class form_BooleanService extends form_FieldService
{
	/**
	 * @var form_BooleanService
	 */
	private static $instance;

	/**
	 * @return form_BooleanService
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
	 * @return form_persistentdocument_boolean
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/boolean');
	}

	/**
	 * Create a query based on 'modules_form/boolean' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/boolean');
	}
	
	/**
	 * @param form_persistentdocument_List $field
	 * @param DOMElement $fieldElm
	 * @param mixed $rawValue
	 * @return string
	 */
	public function buildXmlElementResponse($field, $fieldElm, $rawValue)
	{
		if ($rawValue == 'true')
		{
			return $field->getTrueLabel();
		}
		elseif ($rawValue == 'false')
		{
			return $field->getFalseLabel();
		}
		return parent::buildXmlElementResponse($fieldElm, $fieldElm, $rawValue);
	}
}