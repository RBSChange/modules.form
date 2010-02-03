<?php
/**
 * form_persistentdocument_form
 * @package modules.form
 */
class form_persistentdocument_form extends form_persistentdocument_formbase implements form_FormReceiver
{
	private $saveResponse = true;

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

	/**
	 * @return array
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
	 */
	public function getSenderAddress()
	{
		$address = null;
		$pref = ModuleService::getPreferencesDocument('form');
		if ( ! is_null($pref) )
		{
			if ( ! is_null($sender = $pref->getSender()) )
			{
				$addresses = $sender->getEmailAddresses();
				$address = $addresses[0];
			}
		}
		if ( is_null($address) )
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
	 * @param string $label
	 * @deprecated Use setSubmitButton() instead, as the name of the field has changed.
	 */
	public function setSubmitLabel($label)
	{
		$this->setSubmitButton($label);
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
	 * @return string
	 * @deprecated Use getCcAddressArray() instead.
	 */
	public function getCcAddresses()
	{
		return implode(',', $this->getCcAddressArray());
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
	 * @param Boolean $saveResponse
	 */
	public function setSaveResponse($saveResponse)
	{
		$this->saveResponse = $saveResponse;
	}

	/**
	 * @return Boolean
	 */
	public function getSaveResponse()
	{
		return $this->saveResponse;
	}
	
	/**
	 * @return Integer
	 */
	public function getActiveResponseCount()
	{
	    return $this->getResponseCount() - $this->getArchivedResponseCount();
	}

	/**
	 * Returns the CSS class name to be set in the generated HTML.
	 * @return String
	 */
	public function getFormCssClassName()
	{
		return str_replace('/', '_', $this->getFormid());
	}

	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
		$nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.document.form.document-name;');
		$nodeAttributes['description'] = f_util_StringUtils::htmlToText($this->getDescription(), true, true);
		$nodeAttributes['responseCount'] = $this->getResponseCount();
		$nodeAttributes['activeResponse'] = $this->getActiveResponseCount();
	}

	/**
	 * Return mail field which is used for reply-to feature
	 * @return form_persistentdocument_mail
	 */
	public function getReplyToField()
	{
		return form_FormService::getInstance()->getReplyToField($this);
	}
}