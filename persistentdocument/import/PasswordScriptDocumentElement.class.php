<?php
/**
 * form_PasswordScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_PasswordScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_password
     */
    protected function initPersistentDocument()
    {
    	return form_PasswordService::getInstance()->getNewDocumentInstance();
    }
}