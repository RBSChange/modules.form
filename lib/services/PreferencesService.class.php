<?php
/**
 * @package modules.form
 */
class form_PreferencesService extends f_persistentdocument_DocumentService
{
	/**
	 * @var form_PreferencesService
	 */
	private static $instance;

	/**
	 * @return form_PreferencesService
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
	 * @return form_persistentdocument_preferences
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/preferences');
	}

	/**
	 * Create a query based on 'modules_form/preferences' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/preferences');
	}
	
	/**
	 * @param form_persistentdocument_preferences $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$document->setLabel('&modules.form.bo.general.Module-name;');
	}
}