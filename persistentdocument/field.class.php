<?php
/**
 * form_persistentdocument_field
 * @package modules.form
 */
class form_persistentdocument_field extends form_persistentdocument_fieldbase
{
	/**
	 * @return array<string,string>
	 */
	public function getConstraintArray()
	{
		$cp = new validation_ContraintsParser();
		return $cp->getConstraintArrayFromDefinition($this->getValidators());
	}
	
	/**
	 * @param array<string,string> $constraints
	 */
	public function setConstraintArray($constraints)
	{   
	    if (f_util_ArrayUtils::isNotEmpty($constraints))
	    {
    		$strArray = array();
    		foreach ($constraints as $k => $v)
    		{
    			$strArray[] = $k.':'.$v;
    		}
    		$this->setValidators(join(";", $strArray));
		}
		else
		{
		    $this->setValidators(null);
		}
	}	

	/**
	 * Returns the field's type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return substr(get_class($this), strlen('form_persistentdocument_'));
	}

	/**
	 * @return string
	 */
	public function getSurroundingTemplate()
	{
		return 'Form-Field';
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
	    if ($this->getIsLocked())
        {
            $nodeAttributes['isLocked'] = 'isLocked';
        }	
        
        if ($treeType == 'wlist')
        {
	        $modelName = $this->getDocumentModelName();
		    $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.'.ucfirst(substr($modelName, strpos($modelName, '/')+1)).';');
		    
	        if ($this->getRequired())
	        {
	            $nodeAttributes['required'] = 'required';
	            $nodeAttributes['fieldRequired'] = f_Locale::translate('&modules.uixul.bo.general.Yes;');
	        }
	        if ($this->hasCondition())
	        {
	        	$nodeAttributes['conditioned'] = 'conditioned';
	        	
	        	$activationLabel = FormHelper::getActivationLabel($this->getId());
	        	$activationQuestionLabel = $this->getActivationquestion()->getLabel();
	        	$nodeAttributes['fieldConditioned'] = f_Locale::translate('&modules.form.bo.general.Activation;', array('value' => $activationLabel, 'question' => $activationQuestionLabel));
	        }
        }
	}
	
	/**
	 * @var form_persistentdocument_form
	 */
	private $form;
	
	/**
	 * @return form_persistentdocument_form
	 */
	public function getForm()
	{
	    if ($this->form === NULL)
	    {
	        $this->form = $this->getDocumentService()->getFormOf($this);
	    }
	    return $this->form;
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 */
	public function setForm($form)
	{
	    $this->form = $form;
	}
	
	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	public function hasCondition()
	{
		return $this->getActivationQuestion() !== null;
	}
	
	/**
	 * @return Boolean
	 */
	public function inGroup()
	{
		$parent = $this->getDocumentService()->getParentOf($this);
		
		return $parent instanceof form_persistentdocument_group;
	}
}