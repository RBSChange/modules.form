<?php
/**
 * form_RecipientGroupFolderScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_RecipientGroupFolderScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_recipientGroupFolder
	 */
	protected function initPersistentDocument()
	{
		return form_RecipientGroupFolderService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_form/recipientGroupFolder');
	}
}