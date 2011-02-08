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
	
	// Deprecated methods.

	/**
	 * @param string $label
	 * @deprecated Use setSubmitButton() instead, as the name of the field has changed.
	 */
	public function setSubmitLabel($label)
	{
		$this->setSubmitButton($label);
	}

	/**
	 * @return string
	 * @deprecated Use getBccAddressArray() instead.
	 */
	public function getBccAddresses()
	{
		return implode(',', $this->getBccAddressArray());
	}
	
	/**
	 * @return array
	 * @deprecated
	 */
	public function getBccAddressArray()
	{
		$addresses = array();
		foreach ($this->getRecipientGroupArray() as $recipientGroup)
		{
			foreach ($recipientGroup->getBccArray() as $contact)
			{
				$addresses = array_merge($addresses, $contact->getEmailAddresses());
			}
		}
		return array_unique($addresses);
	}

	/**
	 * @return string
	 * @deprecated Use getEmailAddressArray() instead.
	 */
	public function getEmailAddresses()
	{
		return implode(',', $this->getEmailAddressArray());
	}

	/**
	 * @return array
	 * @deprecated Sorry, there is no
	 */
	public function getEmailAddressArray()
	{
		$addresses = array();
		foreach ($this->getRecipientGroupArray() as $recipientGroup)
		{
			foreach ($recipientGroup->getToArray() as $contact)
			{
				$addresses = array_merge($addresses, $contact->getEmailAddresses());
			}
		}
		return array_unique($addresses);
	}

	/**
	 * @return string
	 * @deprecated Use getCcAddressArray() instead.
	 */
	public function getCcAddresses()
	{
		return implode(',', $this->getCcAddressArray());
	}

	/**
	 * @return array
	 * @deprecated
	 */
	public function getCcAddressArray()
	{
		$addresses = array();
		foreach ($this->getRecipientGroupArray() as $recipientGroup)
		{
			foreach ($recipientGroup->getCcArray() as $contact)
			{
				$addresses = array_merge($addresses, $contact->getEmailAddresses());
			}
		}
		return array_unique($addresses);
	}
}