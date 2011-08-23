<?php
class form_GroupService extends f_persistentdocument_DocumentService
{
	/**
	 * @var form_GroupService
	 */
	private static $instance;

	/**
	 * @return form_GroupService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

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
		return $this->pp->createQuery('modules_form/group');
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
	 * @return Boolean
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
	 * @param Integer $parentNodeId
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
	 * @param Integer $parentNodeId
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
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
		if ($document->hasCondition())
		{
			$nodeAttributes['conditioned'] = 'conditioned';
			
			$activationLabel = FormHelper::getActivationLabel($document->getId());
			$activationQuestionLabel = $document->getActivationquestion()->getLabel();
			$nodeAttributes['fieldConditioned'] = LocaleService::getInstance()->transBO('m.form.bo.general.activation', array('ucf'), array('value' => $activationLabel, 'question' => $activationQuestionLabel));
		}
	}
}