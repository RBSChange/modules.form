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
	 * @var form_persistentdocument_baseform
	 */
	private $form;
	
	/**
	 * @return form_persistentdocument_baseform
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
	 * @param form_persistentdocument_baseform $form
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}
	
	/**
	 * @param integer $elementId
	 * @return boolean
	 */
	public function hasCondition()
	{
		return $this->getActivationQuestion() !== null;
	}
	
	/**
	 * @return boolean
	 */
	public function inGroup()
	{
		$parent = $this->getDocumentService()->getParentOf($this);
		return $parent instanceof form_persistentdocument_group;
	}
	
	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return '';
	}
}