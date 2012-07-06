<?php
/**
 * form_persistentdocument_text
 * @package modules.form
 */
class form_persistentdocument_text extends form_persistentdocument_textbase
{
	/**
	 * @return string
	 */
	public function getBoValidator()
	{
		$validators = $this->getConstraintArray();
		foreach ($validators as $key => $value)
		{
			if ($key !== 'maxSize' && $key !== 'minSize')
			{
				return $key . ':' . $value;
			}
		}
		return null;
	}
	
	/**
	 * @param string $value
	 */
	public function setBoValidator($value)
	{
		$this->setValidators($value);
	}
	
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if ($this->getInitializeWithCurrentUserFirstname())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getFirstnameAsHtml();
			}
		}
		else if ($this->getInitializeWithCurrentUserLastname())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getLastnameAsHtml();
			}
		}
		return '';
	}
}