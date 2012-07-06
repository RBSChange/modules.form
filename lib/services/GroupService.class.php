<?php
/**
 * @package modules.form
 * @method form_GroupService getInstance()
 */
class form_GroupService extends f_persistentdocument_DocumentService
{
	/**
	 * @return form_persistentdocument_group
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/group');
	}

	/**
	 * Create a query based on 'modules_form/group' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/group');
	}


	/**
	 * @param form_persistentdocument_group $document
	 * @return boolean true
	 */
	public function isPublishable($document)
	{
		return true;
	}
	
	/**
	 * @param form_persistentdocument_group $group
	 * @param Array $data
	 * @return boolean
	 */
	public function isConditionValid($group, $data)
	{
		if ($group->hasCondition())
		{				
			$activationQuestion = $group->getActivationQuestion();
			$activationQuestionFieldName = $activationQuestion->getFieldName();
			$activationValue = $group->getActivationValue();

			if (is_array($data[$activationQuestionFieldName]))
			{		
				return in_array($activationValue, $data[$activationQuestionFieldName]);
			}
			else
			{
				return $data[$activationQuestionFieldName] == $activationValue;
			}
		}		
		
		return true;
	}
	
	/**
	 * @param form_persistentdocument_group $newDocument
	 * @param form_persistentdocument_group $originalDocument
	 * @param integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$requestContext = RequestContext::getInstance();

		foreach ($requestContext->getSupportedLanguages() as $lang)
		{
			try
			{
				$requestContext->beginI18nWork($lang);
				if ($newDocument->isContextLangAvailable())
				{
					$newDocument->setLabel($originalDocument->getLabel());
				}
				$requestContext->endI18nWork();
			}
			catch (Exception $e)
			{
				$requestContext->endI18nWork($e);
			}
		}
	}
	
	/**
	 * this method is call before save the duplicate document.
	 * $newDocument has a id affected
	 * Traitment of the children of $originalDocument
	 * 
	 * @param form_persistentdocument_group $newDocument
	 * @param form_persistentdocument_group $originalDocument
	 * @param integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$items = $this->getChildrenOf($originalDocument);
		foreach ($items as $item) 
		{
			if ($item instanceof form_persistentdocument_group || 
				$item instanceof form_persistentdocument_field ) 
			{
				$this->duplicate($item->getId(), $newDocument->getId());
			}
		}
	}
	
	/**
	 * @param form_persistentdocument_group $document
	 * @param array<string, string> $attributes
	 * @param integer $mode
	 * @param string $moduleName
	 */
	public function completeBOAttributes($document, &$attributes, $mode, $moduleName)
	{
		if (($mode & DocumentHelper::MODE_CUSTOM) && $document->hasCondition())
		{
			$activationLabel = FormHelper::getActivationLabel($document->getId());
			$activationQuestionLabel = $document->getActivationquestion()->getLabel();
			$attributes['fieldConditioned'] = LocaleService::getInstance()->trans('m.form.bo.general.activation', array('ucf'), array('value' => $activationLabel, 'question' => $activationQuestionLabel));
		}
	}
}