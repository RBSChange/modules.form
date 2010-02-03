<?php
/**
 * form_MailScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_MailScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_mail
     */
    protected function initPersistentDocument()
    {
    	return form_MailService::getInstance()->getNewDocumentInstance();
    }
}