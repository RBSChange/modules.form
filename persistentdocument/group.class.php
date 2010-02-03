<?php
class form_persistentdocument_group extends form_persistentdocument_groupbase
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