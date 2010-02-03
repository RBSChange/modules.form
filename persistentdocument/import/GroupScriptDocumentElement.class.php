<?php
class form_GroupScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return form_persistentdocument_group
     */
    protected function initPersistentDocument()
    {
    	return form_GroupService::getInstance()->getNewDocumentInstance();
    }
}