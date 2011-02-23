<?php
/**
 * form_persistentdocument_freecontent
 * @package modules.form
 */
class form_persistentdocument_freecontent extends form_persistentdocument_freecontentbase
{
	/**
	 * @param Integer $elementId
	 * @return Boolean
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