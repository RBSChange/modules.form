<?php
/**
 * @package modules.form
 * @method form_FreecontentService getInstance()
 */
class form_FreecontentService extends f_persistentdocument_DocumentService
{
	/**
	 * @return form_persistentdocument_freecontent
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/freecontent');
	}

	/**
	 * Create a query based on 'modules_form/freecontent' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/freecontent');
	}

	/**
	 * @param integer $fieldId
	 * @return boolean
	 */
	public function hasCondition($fieldId)
	{
		$field = DocumentHelper::getDocumentInstance($fieldId);
		
		return $field->getActivationQuestion() !== null;
	}
	
	/**
	 * @param form_persistentdocument_freecontent $newDocument
	 * @param form_persistentdocument_freecontent $originalDocument
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
	 * @param form_persistentdocument_freecontent $document
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