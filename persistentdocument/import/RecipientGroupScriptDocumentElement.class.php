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
}