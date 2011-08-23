<?php
class form_PasswordService extends form_FieldService
{
	/**
	 * @var form_PasswordService
	 */
	private static $instance;

	/**
	 * @return form_PasswordService
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
	 * @return form_persistentdocument_password
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/password');
	}

	/**
	 * Create a query based on 'modules_form/password' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/password');
	}
}