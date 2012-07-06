<?php
class form_PreferencesScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_preferences
	 */
	protected function initPersistentDocument()
	{
		$document = ModuleService::getInstance()->getPreferencesDocument('form');
		return ($document !== null) ? $document : form_PreferencesService::getInstance()->getNewDocumentInstance();
	}
}