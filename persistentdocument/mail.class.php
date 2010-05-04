<?php
/**
 * form_persistentdocument_mail
 * @package modules.form
 */
class form_persistentdocument_mail extends form_persistentdocument_mailbase
{
	/**
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    protected function addTreeAttributes ($moduleName, $treeType, &$nodeAttributes)
    {
        parent::addTreeAttributes($moduleName, $treeType, $nodeAttributes);
        $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Mail;');
    }
    
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if ($this->getInitializeWithCurrentUserEmail())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getEmailAsHtml();
			}
		}
		return '';
	}
}