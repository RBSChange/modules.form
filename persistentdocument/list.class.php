<?php
/**
 * form_persistentdocument_list
 * @package modules.form
 */
class form_persistentdocument_list extends form_persistentdocument_listbase
{
	/**
	 * @return String
	 */
    public function getSurroundingTemplate ()
    {
        if ($this->getDisplay() == FormHelper::DISPLAY_BUTTONS)
        {
            return 'Form-Radio';
        }
        return parent::getSurroundingTemplate();
    }
}