<?php
class form_RecipientGroupService extends f_persistentdocument_DocumentService
{
	/**
	 * @var form_RecipientGroupService
	 */
	private static $instance;

	/**
	 * @return form_RecipientGroupService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

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
		return $this->pp->createQuery('modules_form/recipientGroup');
	}
}