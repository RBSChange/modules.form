<?php
/**
 * form_persistentdocument_text
 * @package modules.form
 */
class form_persistentdocument_text extends form_persistentdocument_textbase
{
    /**
     * @param string $moduleName
     * @param string $treeType
     * @param array<string, string> $nodeAttributes
     */
    protected function addTreeAttributes ($moduleName, $treeType, &$nodeAttributes)
    {
        parent::addTreeAttributes($moduleName, $treeType, $nodeAttributes);
        if ($this->getMultiline())
        {
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Multiline-text;');
        } else
        {
            $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.Text;');
        }
    }
    
    /**
     * @return String
     */
    public function getBoValidator()
    {
    	$validators = $this->getConstraintArray();
    	foreach ($validators as $key => $value)
    	{
    		if ($key !== 'maxSize' && $key !== 'minSize')
    		{
    			return $key . ':' . $value;
    		}
    	}
    	return null;
    }
    
    /**
     * @param String $value
     */
    public function setBoValidator($value)
    {
    	$this->setValidators($value);
    }
    
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if ($this->getInitializeWithCurrentUserFirstname())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getFirstnameAsHtml();
			}
		}
		else if ($this->getInitializeWithCurrentUserLastname())
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			if ($user !== null)
			{
				return $user->getLastnameAsHtml();
			}
		}
		return '';
	}
}