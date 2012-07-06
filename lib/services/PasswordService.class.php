<?php
/**
 * @package modules.form
 * @method form_PasswordService getInstance()
 */
class form_PasswordService extends form_FieldService
{
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
		return $this->getPersistentProvider()->createQuery('modules_form/password');
	}
}