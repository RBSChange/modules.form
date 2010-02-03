<?php
class form_FreecontentScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return form_persistentdocument_freecontent
     */
    protected function initPersistentDocument()
    {
    	return form_FreecontentService::getInstance()->getNewDocumentInstance();
    }
}