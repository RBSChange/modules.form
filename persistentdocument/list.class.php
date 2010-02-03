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

    /**
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    protected function addTreeAttributes ($moduleName, $treeType, &$nodeAttributes)
    {
        parent::addTreeAttributes($moduleName, $treeType, $nodeAttributes);
        if ($this->getMultiple())
        {
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Multiple-selection-list;');
        } else
        {
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Single-selection-list;');
        }
        $nodeAttributes['fieldName'] = $this->getFieldName();
    }
}