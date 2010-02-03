<?php
class form_FormScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return form_persistentdocument_form
     */
    protected function initPersistentDocument()
    {
    	return form_FormService::getInstance()->getNewDocumentInstance();
    }
    
    public function endProcess ()
    {
        $form = $this->getPersistentDocument();
        if ($form->getPublicationstatus() == 'DRAFT')
        {
            $form->getDocumentService()->activate($form->getId());
        }
    }
}