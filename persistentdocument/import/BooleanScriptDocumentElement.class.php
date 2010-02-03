<?php
/**
 * form_BooleanScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_BooleanScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_boolean
     */
    protected function initPersistentDocument()
    {
    	return form_BooleanService::getInstance()->getNewDocumentInstance();
    }
}