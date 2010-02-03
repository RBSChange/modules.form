<?php
/**
 * form_DateScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_DateScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_date
     */
    protected function initPersistentDocument()
    {
    	return form_DateService::getInstance()->getNewDocumentInstance();
    }
}