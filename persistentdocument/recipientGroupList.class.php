<?php
class form_persistentdocument_recipientGroupList extends form_persistentdocument_recipientGroupListbase
{

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
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Recipient-multiple-selection-list;');
        } else
        {
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Recipient-single-selection-list;');
        }
    }

}