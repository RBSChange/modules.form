<?php
class form_persistentdocument_group extends form_persistentdocument_groupbase
{
	/**
	 * @param integer $elementId
	 * @return boolean
	 */
	public function hasCondition()
	{
		return $this->getActivationQuestion() !== null;
	}
	
	/**
	 * @return form_persistentdocument_baseform
	 */
	public function getForm()
	{
		return form_BaseformService::getInstance()->getAncestorFormByDocument($this);
	}
}