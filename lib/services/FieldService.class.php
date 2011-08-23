<?php
class form_FieldService extends f_persistentdocument_DocumentService
{
	const FIELD_NAME_REGEXP = '^[a-zA-Z][a-z_\-A-Z0-9]+$';

	private $deletedFieldsForms = array();
	
	/**
	 * @var form_FieldService
	 */
	private static $instance;

	/**
	 * @return form_FieldService
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
	 * @return form_persistentdocument_field
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/field');
	}

	/**
	 * Create a query based on 'modules_form/field' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/field');
	}

	/**
	 * @param form_persistentdocument_field $field
	 * @param block_BlockRequest $request
	 * @param validation_Errors $errors
	 * @return void
	 */
	public function validate($field, $request, &$errors)
	{
		$value = $request->getParameter($field->getFieldName());
		
		if(is_array($value))
		{			
			$isEmpty = f_util_ArrayUtils::isEmpty($value);
		}
		else
		{
			$isEmpty = f_util_StringUtils::isEmpty($value);
		}
		
		$data = $request->getParameters();
				
		if($field->getRequired() &&  $isEmpty)
		{
			if($this->isConditionValid($field, $data))
			{
				$errors->append(f_Locale::translate("&framework.validation.validator.Blank.message;", array('field' => $field->getLabel())));
			}
		}
		else if ( $field->getRequired() || ! $isEmpty )
		{
			validation_ValidatorHelper::validate(
				new validation_Property($field->getLabel(), f_util_Convert::fixDataType($value)),
				$field->getValidators(), $errors
				);
		}
	}

	/**
	 * @param Integer $fieldId
	 * @return Boolean
	 */
	public function hasCondition($fieldId)
	{
		$field = DocumentHelper::getDocumentInstance($fieldId);
		return $field->getActivationQuestion() !== null;
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		$parentDoc = ($parentNodeId != null) ? DocumentHelper::getDocumentInstance($parentNodeId) : $this->getParentOf($document);
		if (!f_util_StringUtils::isEmpty($document->getFieldName()))
		{
			form_BaseformService::getInstance()->checkFieldNameAvailable($document, $parentDoc->getId());
		}
		
		$this->fixRequiredConstraint($document);
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function postSave($document, $parentNodeId)
	{
		$fieldName = $document->getFieldName();
		if (f_util_StringUtils::isEmpty($fieldName))
		{
			try
			{
				$this->tm->beginTransaction();
				
				$fieldName = 'f' . $document->getId();
				if (Framework::isDebugEnabled())
				{
					Framework::debug(__METHOD__ . ' Generate FieldName: ' . $fieldName . ' for document ' . $document->__toString());
				}
				$document->setFieldName($fieldName);
				
				$this->pp->updateDocument($document);
				$this->tm->commit();
			}
			catch (Exception $e)
			{
				$this->tm->rollBack($e);
				throw $e;
			}
		}
	}
		
	/**
	 * @param form_persistentdocument_field $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 * @throws Exception if no parent form is found.
	 */
	protected function postInsert($document, $parentNodeId)
	{
		$form = $this->getFormOf($document);
		$form->getDocumentService()->onFieldAdded($form);
	}

	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 * @throws form_FieldLockedException
	 */
	protected function preDelete($document)
	{
		if ($document->getIsLocked())
		{
			throw new form_FieldLockedException("Cannot delete a locked field: ".$document->__toString());
		}
		
		parent::preDelete($document);
		
		$this->addFormToList($document);
	}

	/**
	 * @param inquiry_persistentdocument_inquiryform $document
	 * @return void
	 */
	protected function preDeleteLocalized($document)
	{
		parent::preDeleteLocalized($document);
		
		$this->addFormToList($document);
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 */
	protected function postDelete($document)
	{
		parent::postDelete($document);
		
		$this->applyOnFieldDeleted($document);
	}

	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 */
	protected function postDeleteLocalized($document)
	{
		parent::postDeleteLocalized($document);
		
		$this->applyOnFieldDeleted($document);
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 */
	private function addFormToList($document)
	{
		$form = $this->getFormOf($document);
		if (!isset($this->deletedFieldsForms[$document->getId()]))
		{
			$this->deletedFieldsForms[$document->getId()] = $form;
		}
	}

	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 */
	private function applyOnFieldDeleted($document)
	{
		if (isset($this->deletedFieldsForms[$document->getId()]))
		{
			$form = $this->deletedFieldsForms[$document->getId()];
			$form->getDocumentService()->onFieldDeleted($form);
			unset($this->deletedFieldsForms[$document->getId()]);
		}
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 * @throws form_FieldLockedException
	 */
	protected function preUpdate($document)
	{
		if ($document->getIsLocked() && $document->isPropertyModified("fieldName"))
		{
			throw new form_FieldLockedException("Cannot update the field name of a locked field: ".$document->__toString()." ".$document->getFieldName());
		}
	}

	/**
	 * @param form_persistentdocument_field $document
	 * @return void
	 */
	protected function postUpdate($document)
	{
		$form = $this->getFormOf($document);
		$form->getDocumentService()->onFieldChanged($form);
	}

	/**
	 * Moves $field into the destination node identified by $destId.
	 * @param form_persistentdocument_field $field The field to move.
	 * @param integer $destId ID of the destination node.
	 * @param integer $beforeId
	 * @param integer $afterId
	 */
	public function moveTo($field, $destId, $beforeId = null, $afterId = null)
	{
		$fbs = form_BaseformService::getInstance();
		$fieldForm = $fbs->getAncestorFormByDocument($field);
		$destDocument = DocumentHelper::getDocumentInstance($destId);
		if ($destDocument instanceof form_persistentdocument_baseform)
		{
			$destForm = $destDocument;
		}
		else
		{
			$destForm = $fbs->getAncestorFormByDocument($destDocument);
		}
		
		if (!DocumentHelper::equals($destForm, $fieldForm))
		{
			throw new form_FormException(f_Locale::translate('&modules.form.bo.errors.Cannot-move-a-field-from-a-form-to-another-form;'));
		}

		return parent::moveTo($field, $destId, $beforeId, $afterId);
	}

	/**
	 * Returns the parent form of the given $field.
	 * @param form_persistentdocument_field $field The field to move.
	 * @return form_persistentdocument_baseform
	 * @throws Exception if no parent form is found.
	 */
	public function getFormOf($field)
	{
		$fbs = form_BaseformService::getInstance();
		$form = $fbs->getAncestorFormByDocument($field);
		if ($form === null)
		{
			throw new Exception("Could not find parent form for field \"".$field->__toString()."\".");
		}
		return $form;
	}

	/**
	 * Returns the parent group of the given $field.
	 * @param form_persistentdocument_field $field The field to move.
	 * @return form_persistentdocument_group
	 * @throws Exception if no parent group is found.
	 */
	public function getGroupOf($field)
	{
		$ancestors = $this->getAncestorsOf($field, 'modules_form/group');
		if (empty($ancestors))
		{
			throw new Exception("Could not find parent group for field \"".$field->__toString()."\".");
		}
		return $ancestors[count($ancestors)-1];
	}
	
	/**
	 * Locks a field so that the user won't be able to delete or edit it into
	 * the backoffice. The $field has to be saved for the changes to be saved.
	 * @param form_persistentdocument_field $field The field to lock.
	 */
	public function lockField($field)
	{
		$field->setIsLocked(true);
	}
	
	/**
	 * @param form_persistentdocument_field $field
	 * @param Array $data
	 * @return Boolean
	 */
	public function isConditionValid($field, $data)
	{
		if($field->hasCondition())
		{				
			$activationQuestion = $field->getActivationQuestion();
			$activationQuestionFieldName = $activationQuestion->getFieldName();
			$activationValue = $field->getActivationValue();

			if(is_array($data[$activationQuestionFieldName]))
			{		
				return in_array($activationValue, $data[$activationQuestionFieldName]);
			}
			else
			{
				return $data[$activationQuestionFieldName] == $activationValue;
			}
		}		
		
		try
		{
			$group = $this->getGroupOf($field);	
		}
		catch (Exception $e)
		{
			$e; // Avoid Eclipse warning...
			$group = null;
		}			
		
		if($group !== null)
		{
			return $this->isConditionValid($group, $data);
		}			
		
		return true;
	}	
	
	/**
	 * @param form_persistentdocument_field $newDocument
	 * @param form_persistentdocument_field $originalDocument
	 * @param Integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$requestContext = RequestContext::getInstance();
		foreach ($newDocument->getI18nInfo()->getLangs() as $lang)
		{
			try
			{
				$requestContext->beginI18nWork($lang);
				$newDocument->setLabel($originalDocument->getLabel());
				$requestContext->endI18nWork();
			}
			catch (Exception $e)
			{
				$requestContext->endI18nWork($e);
			}
		}
	}
	
	/**
	 * @param form_persistentdocument_field $field
	 * @param DOMElement $fieldElm
	 * @param mixed $rawValue
	 * @return string
	 */
	public function buildXmlElementResponse($field, $fieldElm, $rawValue)
	{ 
	    if (empty($rawValue))
	    {
	        return '';
	    }
	    
	    $retValue = '';
		if (is_array($rawValue))
		{
			foreach ($rawValue as $v)
			{
				$retValue .= f_util_Convert::toString($v) . ' ';
			}
			$retValue = trim($retValue);
		}
		else
		{
			$retValue = f_util_Convert::toString($rawValue);
		}
		return $retValue;    
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 */
	public function fixRequiredConstraint($document)
	{
		$constraintArray = $document->getConstraintArray();
		if ($document->getRequired())
		{
			if (!isset($constraintArray['blank']) || $constraintArray['blank'] != 'false')
			{
				$constraintArray['blank'] = 'false';
			}
		}
		$strArray = array();
		foreach ($constraintArray as $k => $v)
		{
			$strArray[] = $k.':'.$v;
		}
		$document->setValidators(join(";", $strArray));
	}
	
	/**
	 * @param form_persistentdocument_field $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */	
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
	    if ($document->getIsLocked())
        {
            $nodeAttributes['isLocked'] = 'isLocked';
        }	
        
        if ($treeType == 'wlist')
        {
	        $modelName = $document->getDocumentModelName();
		    $nodeAttributes['fieldType'] = f_Locale::translate('&modules.form.bo.general.field.'.ucfirst(substr($modelName, strpos($modelName, '/')+1)).';');
		    
	        if ($document->getRequired())
	        {
	            $nodeAttributes['required'] = 'required';
	            $nodeAttributes['fieldRequired'] = f_Locale::translate('&modules.uixul.bo.general.Yes;');
	        }
	        if ($document->hasCondition())
	        {
	        	$nodeAttributes['conditioned'] = 'conditioned';
	        	
	        	$activationLabel = FormHelper::getActivationLabel($document->getId());
	        	$activationQuestionLabel = $document->getActivationquestion()->getLabel();
	        	$nodeAttributes['fieldConditioned'] = f_Locale::translate('&modules.form.bo.general.Activation;', array('value' => $activationLabel, 'question' => $activationQuestionLabel));
	        }
        }
	}
	
}