<?php
/**
 * @package modules.form
 * @method form_PreferencesService getInstance()
 */
class form_PreferencesService extends f_persistentdocument_DocumentService
{
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
		return $this->getPersistentProvider()->createQuery('modules_form/preferences');
	}

	/**
	 * @param form_persistentdocument_preferences $document
	 * @param integer $parentNodeId
	 */
	protected function postSave($document, $parentNodeId)
	{
		if ($document->isPropertyModified('enableRecipientGroupFolderCreation'))
		{
			CacheService::getInstance()->boShouldBeReloaded();
		}
	}
}