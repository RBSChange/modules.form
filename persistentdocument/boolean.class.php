<?php
/**
 * form_persistentdocument_boolean
 * @package modules.form
 */
class form_persistentdocument_boolean extends form_persistentdocument_booleanbase
{
	/**
	 * @return string
	 */
	public function getSurroundingTemplate()
	{
		if ($this->getDisplay() == 'radio')
		{
			return 'Form-Radio';
		}
		return parent::getSurroundingTemplate();
	}
}