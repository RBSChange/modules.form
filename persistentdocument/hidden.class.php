<?php
class form_persistentdocument_hidden extends form_persistentdocument_hiddenbase
{
	/**
	 * @return string
	 */
	public function getSurroundingTemplate()
	{
		return 'Form-Hidden';
	}
	
	/**
	 * @return array<string,string>
	 */
	public function getConstraintArray()
	{
		// #55202: A hidden field is not supposed to have constraints.
		return array();
	}
}