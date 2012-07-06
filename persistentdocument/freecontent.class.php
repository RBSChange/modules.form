<?php
/**
 * form_persistentdocument_freecontent
 * @package modules.form
 */
class form_persistentdocument_freecontent extends form_persistentdocument_freecontentbase
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