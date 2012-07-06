<?php
class form_RecipientGroupListScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_recipientGroupList
	 */
	protected function initPersistentDocument()
	{
		return form_RecipientGroupListService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_form/recipientGroupList');
	}
}