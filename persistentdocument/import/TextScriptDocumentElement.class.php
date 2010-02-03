<?php
/**
 * form_TextScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_TextScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_text
     */
    protected function initPersistentDocument()
    {
    	return form_TextService::getInstance()->getNewDocumentInstance();
    }
}