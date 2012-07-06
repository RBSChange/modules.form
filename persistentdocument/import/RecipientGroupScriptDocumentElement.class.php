<?php
class form_RecipientGroupScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_recipientGroup
	 */
	protected function initPersistentDocument()
	{
		return form_RecipientGroupService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_form/recipientGroup');
	}
}