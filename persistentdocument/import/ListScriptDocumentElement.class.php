<?php
/**
 * form_ListScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_ListScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_list
     */
    protected function initPersistentDocument()
    {
    	return form_ListService::getInstance()->getNewDocumentInstance();
    }
}