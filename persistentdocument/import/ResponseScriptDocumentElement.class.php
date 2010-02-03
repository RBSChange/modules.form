<?php
class form_ResponseScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return form_persistentdocument_response
     */
    protected function initPersistentDocument()
    {
    	return form_ResponseService::getInstance()->getNewDocumentInstance();
    }
}