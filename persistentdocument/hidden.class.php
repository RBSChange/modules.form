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
}