<?php
class form_ActionBase extends f_action_BaseAction
{
	
	/**
	 * Returns the form_FieldService to handle documents of type "modules_form/field".
	 *
	 * @return form_FieldService
	 */
	public function getFieldService()
	{
		return form_FieldService::getInstance();
	}
	
	/**
	 * Returns the form_TextService to handle documents of type "modules_form/text".
	 *
	 * @return form_TextService
	 */
	public function getTextService()
	{
		return form_TextService::getInstance();
	}
	
	/**
	 * Returns the form_HiddenService to handle documents of type "modules_form/hidden".
	 *
	 * @return form_HiddenService
	 */
	public function getHiddenService()
	{
		return form_HiddenService::getInstance();
	}
	
	/**
	 * Returns the form_RecipientGroupListService to handle documents of type "modules_form/recipientGroupList".
	 *
	 * @return form_RecipientGroupListService
	 */
	public function getRecipientGroupListService()
	{
		return form_RecipientGroupListService::getInstance();
	}
	
	/**
	 * Returns the form_GroupService to handle documents of type "modules_form/group".
	 *
	 * @return form_GroupService
	 */
	public function getGroupService()
	{
		return form_GroupService::getInstance();
	}
	
	/**
	 * Returns the form_ResponseService to handle documents of type "modules_form/response".
	 *
	 * @return form_ResponseService
	 */
	public function getResponseService()
	{
		return form_ResponseService::getInstance();
	}
	
	/**
	 * Returns the form_RecipientGroupService to handle documents of type "modules_form/recipientGroup".
	 *
	 * @return form_RecipientGroupService
	 */
	public function getRecipientGroupService()
	{
		return form_RecipientGroupService::getInstance();
	}
	
	/**
	 * Returns the form_PreferencesService to handle documents of type "modules_form/preferences".
	 *
	 * @return form_PreferencesService
	 */
	public function getPreferencesService()
	{
		return form_PreferencesService::getInstance();
	}
	
	/**
	 * Returns the form_PasswordService to handle documents of type "modules_form/password".
	 *
	 * @return form_PasswordService
	 */
	public function getPasswordService()
	{
		return form_PasswordService::getInstance();
	}
	
	/**
	 * Returns the form_FormService to handle documents of type "modules_form/form".
	 *
	 * @return form_FormService
	 */
	public function getFormService()
	{
		return form_FormService::getInstance();
	}
	
	/**
	 * Returns the form_ListService to handle documents of type "modules_form/list".
	 *
	 * @return form_ListService
	 */
	public function getListService()
	{
		return form_ListService::getInstance();
	}
	
	/**
	 * Returns the form_FreecontentService to handle documents of type "modules_form/freecontent".
	 *
	 * @return form_FreecontentService
	 */
	public function getFreecontentService()
	{
		return form_FreecontentService::getInstance();
	}
	
	/**
	 * Returns the form_DateService to handle documents of type "modules_form/date".
	 *
	 * @return form_DateService
	 */
	public function getDateService()
	{
		return form_DateService::getInstance();
	}
	
	/**
	 * Returns the form_FileService to handle documents of type "modules_form/file".
	 *
	 * @return form_FileService
	 */
	public function getFileService()
	{
		return form_FileService::getInstance();
	}
	
	/**
	 * Returns the form_BooleanService to handle documents of type "modules_form/boolean".
	 *
	 * @return form_BooleanService
	 */
	public function getBooleanService()
	{
		return form_BooleanService::getInstance();
	}
	
	/**
	 * Returns the form_MailService to handle documents of type "modules_form/mail".
	 *
	 * @return form_MailService
	 */
	public function getMailService()
	{
		return form_MailService::getInstance();
	}
	
}