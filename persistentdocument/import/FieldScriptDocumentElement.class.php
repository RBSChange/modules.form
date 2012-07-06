<?php
class form_FieldScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return form_persistentdocument_field
	 */
	protected function initPersistentDocument()
	{
		return form_FieldService::getInstance()->getNewDocumentInstance();
	}
}
