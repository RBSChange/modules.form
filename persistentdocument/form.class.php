<?php
/**
 * form_persistentdocument_form
 * @package modules.form
 */
class form_persistentdocument_form extends form_persistentdocument_formbase implements form_FormReceiver
{
	/**
	 * @return Integer
	 */
	public function getActiveResponseCount()
	{
	    return $this->getResponseCount() - $this->getArchivedResponseCount();
	}

	/**
	 * @return string
	 */
	public function getSenderAddress()
	{
		$address = null;
		$pref = ModuleService::getPreferencesDocument('form');
		if ($pref !== null)
		{
			$sender = $pref->getSender();
			if ($sender !== null)
			{
				$addresses = $sender->getEmailAddresses();
				$address = $addresses[0];
			}
		}
		if ($address === null)
		{
			if (defined('MOD_NOTIFICATION_SENDER'))
			{
				$address = MOD_NOTIFICATION_SENDER;
			}
			else
			{
				$address = MOD_NOTIFICATION_DEFAULT_SENDER;
			}
		}
		return $address;
	}
	
	/**
	 * @return array
	 */
	public function getValidationRules()
	{
		$rules = array();
		foreach ($this->getDocumentService()->getFields($this) as $field)
		{
			$constraints = $field->getConstraintArray();
			if (f_util_ArrayUtils::isNotEmpty($constraints))
			{
				$rules[FormHelper::getModuleName()."Param[".$field->getFieldName()."]"] = $constraints;	
			}
		}
		return $rules;
	}
	
	/**
	 * @return string
	 */
	public function getValidationRulesJSON()
	{
		return JsonService::getInstance()->encode($this->getValidationRules());
	}
}