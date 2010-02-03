<?php
/**
 * form_FileScriptDocumentElement
 * @package modules.form.persistentdocument.import
 */
class form_FileScriptDocumentElement extends form_FieldScriptDocumentElement
{
    /**
     * @return form_persistentdocument_file
     */
    protected function initPersistentDocument()
    {
    	return form_FileService::getInstance()->getNewDocumentInstance();
    }
}