<?php
/**
 * Class where to put your custom methods for document form_persistentdocument_baseform
 * @package modules.form.persistentdocument
 */
class form_persistentdocument_baseform extends form_persistentdocument_baseformbase 
{
	/**
	 * Return mail field which is used for reply-to feature
	 * @return form_persistentdocument_mail
	 */
	public function getReplyToField()
	{
		return $this->getDocumentService()->getReplyToField($this);
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
	 * @var boolean
	 */
	private $isDuplicating;

	/**
	 * @param boolean $value
	 */
	public function setIsDuplicating($value)
	{
		$this->isDuplicating = $value;
	}

	/**
	 * @return boolean
	 */
	public function getIsDuplicating()
	{
		return $this->isDuplicating;
	}
}