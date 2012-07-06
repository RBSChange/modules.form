<?php
/**
 * @package modules.form
 * @method form_RecipientGroupFolderService getInstance()
 */
class form_RecipientGroupFolderService extends generic_FolderService
{
	/**
	 * @return form_persistentdocument_recipientGroupFolder
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/recipientGroupFolder');
	}

	/**
	 * Create a query based on 'modules_form/recipientGroupFolder' model.
	 * Return document that are instance of modules_form/recipientGroupFolder,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/recipientGroupFolder');
	}
	
	/**
	 * Create a query based on 'modules_form/recipientGroupFolder' model.
	 * Only documents that are strictly instance of modules_form/recipientGroupFolder
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/recipientGroupFolder', false);
	}
	
	/**
	 * @return form_persistentdocument_recipientGroupFolder
	 */
	public function getFolder($parentId = null)
	{
		if ($parentId === null)
		{
			$parentId = ModuleService::getInstance()->getRootFolderId('form');
		}
		$folder = $this->createQuery()->add(Restrictions::childOf($parentId))->findUnique();
		if ($folder === null)
		{
			$folder = $this->getNewDocumentInstance();
			$folder->save($parentId);
		}
		return $folder;
	}
}