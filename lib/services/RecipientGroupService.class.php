<?php
/**
 * @package modules.form
 * @method form_RecipientGroupService getInstance()
 */
class form_RecipientGroupService extends f_persistentdocument_DocumentService
{
	/**
	 * @return form_persistentdocument_recipientGroup
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/recipientGroup');
	}

	/**
	 * Create a query based on 'modules_form/recipientGroup' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/recipientGroup');
	}
	
	/**
	 * @param form_persistentdocument_recipientGroup $document
	 * @param integer $parentNodeId
	 */
	protected function postInsert($document, $parentNodeId)
	{
		// Replace linked-to-root-module document model attribute.
		if ($document->getTreeId() === null)
		{
			TreeService::getInstance()->newLastChild(ModuleService::getInstance()->getRootFolderId('form'), $document->getId());
		}
	}
}