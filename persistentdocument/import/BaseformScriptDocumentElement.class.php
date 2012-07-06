<?php
/**
 * form_BaseformScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_BaseformScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_baseform
	 */
	protected function initPersistentDocument()
	{
		return form_BaseformService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_form/baseform');
	}
}