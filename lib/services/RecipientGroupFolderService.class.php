<?php
/**
 * form_RecipientGroupFolderService
 * @package modules.form
 */
class form_RecipientGroupFolderService extends generic_FolderService
{
	/**
	 * @var form_RecipientGroupFolderService
	 */
	private static $instance;

	/**
	 * @return form_RecipientGroupFolderService
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
		return $this->pp->createQuery('modules_form/recipientGroupFolder');
	}
	
	/**
	 * Create a query based on 'modules_form/recipientGroupFolder' model.
	 * Only documents that are strictly instance of modules_form/recipientGroupFolder
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_form/recipientGroupFolder', false);
	}
	
	/**
	 * @return form_persistentdocument_recipientGroupFolder
	 */
	public function getFolder()
	{
		$folder = $this->createQuery()->findUnique();
		if ($folder === null)
		{
			$folder = $this->getNewDocumentInstance();
			$folder->save();
		}
		return $folder;
	}
}