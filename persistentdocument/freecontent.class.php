<?php
/**
 * form_persistentdocument_freecontent
 * @package modules.form
 */
class form_persistentdocument_freecontent extends form_persistentdocument_freecontentbase
{
	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	public function hasCondition()
	{
		return $this->getActivationQuestion() !== null;
	}
	
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
        if($this->hasCondition())
        {
        	$nodeAttributes['conditioned'] = 'conditioned';
        	
        	$activationLabel = FormHelper::getActivationLabel($this->getId());
        	$activationQuestionLabel = $this->getActivationquestion()->getLabel();
        	$nodeAttributes['fieldConditioned'] = f_Locale::translate('&modules.form.bo.widgets.mainList.activation;', array('value' => $activationLabel, 'question' => $activationQuestionLabel));
        }
	}
}