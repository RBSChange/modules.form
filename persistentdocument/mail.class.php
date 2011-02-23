<?php
/**
 * form_persistentdocument_mail
 * @package modules.form
 */
class form_persistentdocument_mail extends form_persistentdocument_mailbase
{
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if ($this->getInitializeWithCurrentUserEmail())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getEmailAsHtml();
			}
		}
		return '';
	}
}