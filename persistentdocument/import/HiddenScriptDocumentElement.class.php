<?php
/**
 * form_HiddenScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_HiddenScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_hidden
     */
    protected function initPersistentDocument()
    {
    	return form_HiddenService::getInstance()->getNewDocumentInstance();
    }
}