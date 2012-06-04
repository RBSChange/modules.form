<?php
/**
 * form_BaseformService
 * @package modules.form
 */
class form_BaseformService extends f_persistentdocument_DocumentService
{
	const FORM_SUBMITTED_EVENT_NAME = 'formSubmitted';
	const FORM_VALIDATE_EVENT_NAME = 'formValidate';
	const FORM_INIT_DATA_EVENT_NAME = 'formInitData';

	// These values also appear in the 'modules_form/field' Document Model, in
	// the 'notEqual' validator.
	const CONTENT_REPLACEMENT_NAME    = 'FIELDS';
	const FORM_LABEL_REPLACEMENT_NAME = 'FORM_LABEL';

	/**
	 * @var form_BaseformService
	 */
	private static $instance;

	/**
	 * @return form_BaseformService
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
	 * @return form_persistentdocument_baseform
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/baseform');
	}

	/**
	 * Create a query based on 'modules_form/baseform' model.
	 * Return document that are instance of modules_form/baseform,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/baseform');
	}

	/**
	 * Create a query based on 'modules_form/baseform' model.
	 * Only documents that are strictly instance of modules_form/baseform
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_form/baseform', false);
	}

	/**
	 * @param String $formId
	 * @return form_persistentdocument_baseform
	 */
	public function getByFormId($formId)
	{
		$query = $this->createQuery()->add(Restrictions::eq('formid', $formId));
		return $query->findUnique();
	}

	/**
	 * Return mail field which is used for reply-to feature
	 * @param form_persistentdocument_baseform $document
	 * @return form_persistentdocument_mail
	 */
	public function getReplyToField($document)
	{
		$query = $this->pp->createQuery('modules_form/mail');
		$query->add(Restrictions::descendentOf($document->getId()));
		$query->add(Restrictions::eq('useAsReply', true));
		return $query->findUnique();
	}

	/**
	 * @param form_persistentdocument_baseform $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId)
	{
		if ($document->getFormid() === null)
		{
			$document->setFormid(uniqid('formid_'));
		}

		if ($document->getAcknowledgment())
		{
			$notification = $document->getAcknowledgmentNotification();
			if ($notification === null)
			{
				$this->createacknowledgmentNotification($document);
			}
		}
	}

	/**
	 * @param form_persistentdocument_baseform $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function postSave($document, $parentNodeId)
	{
		$this->updateNotifications($document);
	}

	/**
	 * @param form_persistentdocument_baseform $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		TreeService::getInstance()->setTreeNodeCache(false);
		$this->deleteFieldsAndGroups($document);
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document form_persistentdocument_baseform | form_persistentdocument_group
	 */
	private function deleteFieldsAndGroups($document)
	{
		foreach ($document->getChildrenGroups() as $group)
		{
			$this->deleteFieldsAndGroups($group);
			$group->getDocumentService()->purgeDocument($group);
		}
		foreach ($document->getChildrenFields() as $field)
		{
			$field->setIsLocked(false);
			$field->getDocumentService()->purgeDocument($field);
		}
	}
	
	/**
	 * @param form_persistentdocument_baseform $document
	 * @return boolean true
	 */
	public function isPublishable($document)
	{
		$fields = $this->getFields($document);
		if (count($fields) < 1)
		{
			$this->setActivePublicationStatusInfo($document, 'm.form.document.baseform.publication.no-field');
			return false;
		}
		foreach ($fields as $field)
		{
			if (!$field->isContextLangAvailable())
			{
				$this->setActivePublicationStatusInfo($document, 'm.form.document.baseform.publication.not-translated-field');
				return false;
			}
		}
		return parent::isPublishable($document);
	}

	/**
	 * this method is called before save the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param form_persistentdocument_baseform $newDocument
	 * @param form_persistentdocument_baseform $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$newDocument->setFormid(null);
		$newDocument->setAcknowledgmentNotification(null);
		$newDocument->setIsDuplicating(true);
	}

	/**
	 * this method is called after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param form_persistentdocument_baseform $newDocument
	 * @param form_persistentdocument_baseform $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$oldNotification = $originalDocument->getAcknowledgmentNotification();
		if ($oldNotification !== null)
		{
			$newNotification = $newDocument->getAcknowledgmentNotification();
			$this->duplicateNotificationInfo($oldNotification, $newNotification);
			$newNotification->save();
		}

		$items = $this->getChildrenOf($originalDocument);
		foreach ($items as $item)
		{
			if ($item instanceof form_persistentdocument_group ||
			$item instanceof form_persistentdocument_field ||
			$item instanceof form_persistentdocument_freecontent)
			{
				$this->duplicate($item->getId(), $newDocument->getId());
			}
		}
		$newDocument->setIsDuplicating(false);
	}

	/**
	 * @param form_persistentdocument_baseform $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);

		$acknowledgmentNotification = $document->getAcknowledgmentNotification();
		if ($acknowledgmentNotification !== null)
		{
			$backUri = join(',', array('form', 'openDocument', $document->getPersistentModel()->getBackofficeName(), $document->getId(), 'resume'));
			$openAcknowledgmentNotificationUri = join(',' , array('notification', 'openDocument', $acknowledgmentNotification->getPersistentModel()->getBackofficeName(), $acknowledgmentNotification->getId(), 'properties'));
			$resume['properties']['acknowledgmentNotification'] = array('uri' => $openAcknowledgmentNotificationUri, 'label' => f_Locale::translateUI('&modules.uixul.bo.doceditor.open;'), 'backuri' => $backUri);
		}

		return $resume;
	}

	/**
	 * Called by the FieldService whenever a field is removed from the given $form.
	 * @param form_persistentdocument_baseform $form
	 */
	public function onFieldDeleted($form)
	{
		if ($form->isContextLangAvailable())
		{
			$this->publishIfPossible($form->getId());
			$this->updateNotifications($form);
		}
	}

	/**
	 * Called by the FieldService whenever a field is added into the given $form.
	 * @param form_persistentdocument_baseform $form
	 */
	public function onFieldAdded($form)
	{
		if ($form->isContextLangAvailable())
		{
			$this->publishIfPossible($form->getId());
			$this->updateNotifications($form);
		}
	}

	/**
	 * Called by the FieldService whenever a field is updated into the given $form.
	 * @param form_persistentdocument_baseform $form
	 */
	public function onFieldChanged($form)
	{
		if ($form->isContextLangAvailable())
		{
			$this->publishIfPossible($form->getId());
			$this->updateNotifications($form);
		}
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 */
	protected function updateNotifications($form)
	{
		$notifications = $this->getNotificationsToUpdate($form);
		if (count($notifications) > 0)
		{
			$fieldArray = $this->getFieldRemplacementsForNotification($form);
			foreach ($notifications as $notification)
			{
				if ($notification->isContextLangAvailable())
				{
					$notification->setAvailableparameters(implode("\n", $fieldArray));
					$notification->save();
				}
			}
		}
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @return string[]
	 */
	protected function getFieldRemplacementsForNotification($form)
	{
		$fieldArray = array();
		foreach ($this->getFields($form) as $fieldDoc)
		{
			$fieldArray[] = '{'. $fieldDoc->getFieldName() . '}=' . $fieldDoc->getlabel();
		}
		$fieldArray[] = '{'. self::CONTENT_REPLACEMENT_NAME. '}';
		$fieldArray[] = '{'. self::FORM_LABEL_REPLACEMENT_NAME. '}';
		return $fieldArray;
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 */
	protected function getNotificationsToUpdate($form)
	{
		$notifications = array();
		$notification = $form->getAcknowledgmentNotification();
		if ($notification !== null)
		{
			$notifications[] = $notification;
		}
		return $notifications;
	}

	/**
	 * Creates the acknowledgment notification for a form.
	 * @param form_persistentdocument_baseform $form
	 */
	protected function createacknowledgmentNotification($form)
	{
		$ns = notification_NotificationService::getInstance();
		$codeName = $form->getFormid().'_acknowledgmentNotification';
		$notification = $ns->createQuery()->add(Restrictions::eq('codename', $codeName))->findUnique();
		if ($notification === null)
		{
			$ls = LocaleService::getInstance();
			$notification = $ns->getNewDocumentInstance();
			$notification->setLabel($ls->transFO('m.form.document.form.acknowledgment-notification-label-prefix', array('ucf')) . ' ' . $form->getLabel());
			$notification->setCodename($codeName);
			$notification->setTemplate('default');
			$notification->setSubject($form->getLabel());
			$notification->setBody($this->getDefaultAcknowledgmentNotificationBody());
			if ($form->getId() > 0)
			{
				$notification->setAvailableparameters(implode("\n", $this->getFieldRemplacementsForNotification($form)));
			}
			$notification->save(ModuleService::getInstance()->getSystemFolderId('notification', 'form'));
		}
		$form->setAcknowledgmentNotification($notification);
	}

	/**
	 * @return string
	 */
	protected function getDefaultAcknowledgmentNotificationBody()
	{
		return '{'.self::CONTENT_REPLACEMENT_NAME.'}';
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param block_BlockRequest $request
	 * @param validation_Errors $errors
	 * @return void
	 */
	protected function validate($form, $request, &$errors)
	{
		$fields = $this->getFields($form);
		foreach ($fields as $field)
		{
			$field->getDocumentService()->validate($field, $request, $errors);
		}
		if (!$this->checkCaptcha($form))
		{
			$errors->append(f_Locale::translate("&modules.form.bo.general.Captcha-check-failed;"));
		}

		$eventParam = array('form' => $form, 'request' => $request, 'errors' => $errors);
		f_event_EventManager::dispatchEvent(self::FORM_VALIDATE_EVENT_NAME, $this, $eventParam);
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @return Array
	 */
	private function getConditionalElementsOf($form)
	{
		$pp = $this->getPersistentProvider();

		$fieldsQuery = $pp->createQuery('modules_form/field');
		$fieldsQuery = $this->setConditionalElementsFilter($fieldsQuery, $form);

		$freecontentsQuery = $pp->createQuery('modules_form/freecontent');
		$freecontentsQuery = $this->setConditionalElementsFilter($freecontentsQuery, $form);

		$groupsQuery = $pp->createQuery('modules_form/group');
		$groupsQuery = $this->setConditionalElementsFilter($groupsQuery, $form);

		return array_merge($fieldsQuery->find(), array_merge($freecontentsQuery->find(), $groupsQuery->find()));
	}

	/**
	 * @param f_persistentdocument_criteria_Query $query
	 * @param form_persistentdocument_baseform $form
	 * @return f_persistentdocument_criteria_Query
	 */
	private function setConditionalElementsFilter($query, $form)
	{
		return $query->add(Restrictions::descendentOf($form->getId()))->add(Restrictions::isNotNull('activationQuestion'));
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @return Array
	 */
	public function getJQueryForConditionalElementsOf($form)
	{
		$elements = $this->getConditionalElementsOf($form);

		$result = array();
		foreach ($elements as $element)
		{
			if ($element->hasCondition())
			{
				if ($element instanceof form_persistentdocument_group)
				{
					$zone = 'groupe'.$element->getId();
				}
				elseif ($element instanceof form_persistentdocument_freecontent)
				{
					$zone = 'freecontent'.$element->getId();
				}
				else
				{
					$zone = 'field'.$element->getId();
				}
					
				$elementId = $element->getId();

				$question = $element->getActivationQuestion();
				$questionId = $question->getId();
				$activationValue = $element->getActivationValue();
				$fieldId = $this->getFieldId($elementId);

				if ($question instanceof form_persistentdocument_boolean)
				{
					if($question->getDisplay() == FormHelper::DISPLAY_CHECKBOX)
					{
						$result[$elementId] = "conditionalForm.handleCheckboxBoolean('$zone', '$questionId', \"$activationValue\");\n";
					}
					elseif ($question->getDisplay() == FormHelper::DISPLAY_RADIO)
					{
						$result[$elementId] = "conditionalForm.handleRadio('$zone', '$questionId', \"$activationValue\");\n";
					}
				}
				elseif ($question->getDisplay() == FormHelper::DISPLAY_BUTTONS)
				{
					if($question->getMultiple())
					{
						$result[$elementId] = "conditionalForm.handleCheckbox('$zone', '$fieldId', \"$activationValue\");\n";
					}
					else
					{
						$result[$elementId] = "conditionalForm.handleRadio('$zone', '$questionId', \"$activationValue\");\n";
					}
				}
				elseif ($question->getDisplay() == FormHelper::DISPLAY_LIST)
				{
					$result[$elementId] = "conditionalForm.handleList('$zone', '$fieldId', \"$activationValue\");\n";
				}
			}
		}

		return $result;
	}

	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	private function getFieldId($elementId)
	{
		$element = DocumentHelper::getDocumentInstance($elementId);

		switch ($this->getQuestionFieldType($elementId))
		{
			case FormHelper::DISPLAY_LIST:
				$fieldName = 'field_'.$element->getActivationQuestion()->getId();
				break;
					
			case FormHelper::DISPLAY_RADIO:
			case FormHelper::DISPLAY_CHECKBOX:
				$fieldName = 'field_'.$element->getActivationQuestion()->getId().'_'.FormHelper::getActivationValue($elementId);
				break;
					
			case 'freecontent':
				$fieldName = 'freecontent'.$element->getActivationQuestion()->getId();
				break;
		}

		return $fieldName;
	}

	/**
	 * @param Integer $elementId
	 * @return String
	 */
	private function getQuestionFieldType($elementId)
	{
		$element = DocumentHelper::getDocumentInstance($elementId);

		$question = $element->getActivationQuestion();

		if ($question instanceof form_persistentdocument_boolean)
		{
			if($question->getDisplay() == FormHelper::DISPLAY_CHECKBOX)
			{
				return 'checkbox-boolean';
			}
		}
		elseif ($question instanceof form_persistentdocument_freecontent)
		{
			return 'freecontent';
		}
		else
		{
			if ($question->getDisplay() == FormHelper::DISPLAY_BUTTONS)
			{
				if($question->getMultiple())
				{
					return FormHelper::DISPLAY_CHECKBOX;
				}
				else
				{
					return FormHelper::DISPLAY_RADIO;
				}
			}
		}

		return $question->getDisplay();
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param block_BlockRequest $request
	 * @return void
	 */
	public function saveFormData($form, $request)
	{
		$errors = new validation_Errors();
		$this->validate($form, $request, $errors);
		if (!$errors->isEmpty())
		{
			throw new form_FormValidationException("Form does not validate", $errors);
		}

		$domDoc = new DOMDocument('1.0', 'utf-8');
		$domDoc->formatOutput = true;
		$rootElm = $domDoc->createElement('response');
		$rootElm->setAttribute('lang', RequestContext::getInstance()->getLang());
		$rootElm->setAttribute('date', date('Y-m-d H:i:s'));
		$domDoc->appendChild($rootElm);
		$fields = $this->getSortedFields($form);
		$replyTo = null;
		$acknowledgmentReceiver = null;
		$this->doMakeXmlResponse($domDoc, $rootElm, $request, $replyTo, $acknowledgmentReceiver, $form);

		$response = form_ResponseService::getInstance()->getNewDocumentInstance();
		$response->setContents($domDoc->saveXML());
		$response->setLabel(f_Locale::translate("&modules.form.bo.general.Form-response-title;", array('form' => $form->getLabel())));

		// Handle specific treatments.
		$result = $this->handleData($form, $fields, $response, $request, $replyTo);

		// Acknowledgment.
		if ($result['success'] && $form->getAcknowledgment() && $acknowledgmentReceiver !== null)
		{
			if (!$this->sendAcknowledgement($form, $response, $request, $result, $acknowledgmentReceiver, $replyTo))
			{
				Framework::info(__METHOD__ . " An error occured during acknowledgment sending to " . $acknowledgmentReceiver);
			}
		}
		return $result;
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $parent
	 */
	private function doMakeXmlResponse($domDoc, $rootElm, $request, &$replyTo, &$acknowledgmentReceiver, $node, $groupName = null, $level = 0)
	{
		$data = $request->getParameters();
		if ($node instanceof form_persistentdocument_field)
		{
			if (!$node->getDocumentService()->isConditionValid($node, $data))
			{
				return;
			}
				
			$fieldName = $node->getFieldName();
			if ($node instanceof form_persistentdocument_file)
			{
				if ($request instanceof block_BlockRequest)
				{
					$rawValue = $request->getUploadedFileInformation($fieldName);
				}
				else if ($request instanceof website_BlockActionRequest && $request->hasFile($fieldName))
				{
					$rawValue = $request->getFile($fieldName);
				}
				else
				{
					$rawValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
				}
			}
			else
			{
				$rawValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
			}
				
			$fieldElm = $domDoc->createElement('field');
			$fieldElm->setAttribute('name', $fieldName);
			$fieldElm->setAttribute('label', $node->getLabel());
			$fieldElm->setAttribute('type', $node->getType());
			$fieldElm->setAttribute('level', $level);
			if ($groupName !== null)
			{
				$fieldElm->setAttribute('groupName', $groupName);
			}
				
			if ($node instanceof form_persistentdocument_file)
			{
				$fieldElm->setAttribute('isFile', 'true');
			}
			$rootElm->appendChild($fieldElm);

			// Special raw data for uploaded file.
			$fieldValue = $node->getDocumentService()->buildXmlElementResponse($node, $fieldElm, $rawValue);
			if (!empty($fieldValue))
			{
				$fieldElm->appendChild($domDoc->createTextNode($fieldValue));
			}
			if ($node instanceof form_persistentdocument_mail)
			{
				if ($node->getUseAsReply())
				{
					$replyTo = $rawValue;
				}
				if ($node->getAcknowledgmentReceiver())
				{
					$acknowledgmentReceiver = $rawValue;
				}
			}
		}
		else if ($node instanceof form_persistentdocument_group)
		{
			if (!$node->getDocumentService()->isConditionValid($node, $data))
			{
				return;
			}
			foreach ($node->getDocumentService()->getChildrenOf($node) as $child)
			{
				$this->doMakeXmlResponse($domDoc, $rootElm, $request, $replyTo, $acknowledgmentReceiver, $child, $node->getLabel(), $level+1);
			}
		}
		else
		{
			foreach ($node->getDocumentService()->getChildrenOf($node) as $child)
			{
				$this->doMakeXmlResponse($domDoc, $rootElm, $request, $replyTo, $acknowledgmentReceiver, $child, $groupName, $level);
			}
		}
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param form_persistentdocument_field[] $fields
	 * @param form_persistentdocument_response $response
	 * @param block_BlockRequest $request
	 * @param string $replyTo
	 * @return array an associative array contaning at least the key "success" with a boolean value. This array will be accessible during the acknowledgment notification sending.
	 */
	protected function handleData($form, $fields, $response, $request, $replyTo)
	{
		return array('success' => true);
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param form_persistentdocument_response $response
	 * @param block_BlockRequest $request
	 * @param array $result
	 * @param String $acknowledgmentReceiver
	 * @param String $replyTo
	 * @return void
	 */
	protected function sendAcknowledgement($form, $response, $request, $result, $acknowledgmentReceiver, $replyTo)
	{
		$notification = $form->getAcknowledgmentNotification();
		if (!($notification instanceof notification_persistentdocument_notification))
		{
			return true;
		}

		$website = website_WebsiteService::getInstance()->getCurrentWebsite();
		$ns = notification_NotificationService::getInstance();
		$configuredNotif = $ns->getConfiguredByCodeName($notification->getCodeName(), $website->getId(), $website->getLang());
		if (!($configuredNotif instanceof notification_persistentdocument_notification))
		{
			return true;
		}
		$configuredNotif->setSendingModuleName('form');
		$configuredNotif->setSendingReplyTo($replyTo);
		$configuredNotif->setSendingSenderEmail($this->getOverrideNotificationSender($form));

		$callback = array($this, 'getAcknowledgmentNotifParameters');
		$params = array(
			'form' => $form, 
			'response' => $response, 
			'result' => $result,
			'replyTo' => $replyTo
		);
		
		$recipients = new mail_MessageRecipients();
		$recipients->setTo(array($acknowledgmentReceiver));
		return $configuredNotif->getDocumentService()->sendNotificationCallback($configuredNotif, $recipients, $callback, $params);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getAcknowledgmentNotifParameters($params)
	{
		$response = $params['response'];
		$form = $params['form'];
		
		$contentTemplate = TemplateLoader::getInstance()->setPackageName('modules_form')->setMimeContentType('html')->load('Form-MailContent');
		$contentTemplate->setAttribute('items', $response->getAllData());
		$contentTemplate->setAttribute('response', $response->getResponseInfos());
			
		$parameters = $response->getData();
		$parameters[self::CONTENT_REPLACEMENT_NAME] = $contentTemplate->execute();
		$parameters[self::FORM_LABEL_REPLACEMENT_NAME] = $form->getLabel();
		return $parameters;
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 */
	protected function getOverrideNotificationSender($form)
	{
		$defaultSender = ModuleService::getInstance()->getPreferenceValue('form', 'sender');
		if ($defaultSender !== null)
		{
			return f_util_ArrayUtils::firstElement($defaultSender->getEmailAddresses());
		}
		return null;
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @return array<form_persistentdocument_field>
	 */
	public function getFields($form)
	{
		$query = $this->getPersistentProvider()->createQuery('modules_form/field');
		$query->add(Restrictions::descendentOf($form->getId()));
		$fields = $query->find();
		return $fields;
	}

	/**
	 * Warning: this method is heavier than getFields, so if you don't need
	 * the fields to be ordered, please use getFields.
	 * @param form_persistentdocument_baseform $form
	 * @return array<form_persistentdocument_field>
	 */
	public function getSortedFields($form)
	{
		return $this->doGetSortedFields($form);
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $parent
	 */
	private function doGetSortedFields($parent)
	{
		$sortedFields = array();
		foreach ($parent->getDocumentService()->getChildrenOf($parent) as $child)
		{
			if ($child instanceof form_persistentdocument_field)
			{
				$sortedFields[] = $child;
			}
			else
			{
				$sortedFields = array_merge($sortedFields, $this->doGetSortedFields($child));
			}
		}
		return $sortedFields;
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param string $fieldName
	 * @return form_persistentdocument_field or null
	 */
	public function getFieldByName($form, $fieldName)
	{
		$query = $this->getPersistentProvider()->createQuery('modules_form/field');
		$query->add(Restrictions::descendentOf($form->getId()));
		$query->add(Restrictions::eq('fieldName', $fieldName));
		return $query->findUnique();
	}

	/**
	 * @param form_persistentdocument_field $field
	 * @param integer $parentNodeId
	 * @throws form_FormException When $field is not inside a form
	 * @throws form_FieldAlreadyExistsException When the name of $field is not available
	 */
	public function checkFieldNameAvailable($field, $parentNodeId)
	{
		$fieldName = $field->getFieldName();
		$parent = DocumentHelper::getDocumentInstance($parentNodeId);
		if ($parent instanceof form_persistentdocument_baseform)
		{
			$form = $parent;
		}
		else
		{
			$form = $this->getAncestorFormByDocument($parent);
			if ($form === null)
			{
				throw new form_FormException("Field \"".$field->__toString()."\" is not inside a form.");
			}
		}
		$query = $this->getPersistentProvider()->createQuery('modules_form/field');
		$query->add(Restrictions::descendentOf($form->getId()));
		$query->add(Restrictions::eq('fieldName', $fieldName));
		$result = $query->findUnique();
		if ($result !== null && $result->getId() != $field->getId())
		{
			throw new form_FieldAlreadyExistsException(f_Locale::translate('&modules.form.bo.errors.Field-name-alreay-used;', array('fieldName' => $fieldName)));
		}
	}

	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return form_persistentdocument_baseform
	 */
	public function getAncestorFormByDocument($document)
	{
		$ancestors = $document->getDocumentService()->getAncestorsOf($document);
		// There should not be nested forms, so return the first found one.
		foreach ($ancestors as $ancestor)
		{
			if ($ancestor instanceof form_persistentdocument_baseform)
			{
				return $ancestor;
			}
		}
		return null;
	}

	/**
	 * @param notification_persistentdocument_notification $oldNotification
	 * @param notification_persistentdocument_notification $newNotification
	 */
	protected function duplicateNotificationInfo($oldNotification, $newNotification)
	{
		$requestContext = RequestContext::getInstance();
		foreach ($requestContext->getSupportedLanguages() as $lang)
		{
			try
			{
				$requestContext->beginI18nWork($lang);
				if ($newNotification->isContextLangAvailable())
				{
					if ($oldNotification->getLabel() != $oldNotification->getSubject())
					{
						$newNotification->setSubject(f_Locale::translate('&modules.generic.backoffice.Duplicate-prefix;') . ' '.$oldNotification->getSubject());
					}
					$newNotification->setBody($oldNotification->getBody());
					$newNotification->setHeader($oldNotification->getHeader());
					$newNotification->setFooter($oldNotification->getFooter());
					$newNotification->setFooter($oldNotification->getFooter());
					$newNotification->setTemplate($oldNotification->getTemplate());
					$newNotification->setSenderEmail($oldNotification->getSenderEmail());
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
	 * @param form_persistentdocument_baseform $form
	 * @return boolean
	 */
	public function hasToUseCaptcha($form)
	{
		$currentUser = users_UserService::getInstance()->getCurrentFrontEndUser();
		if ($currentUser !== null)
		{
			return false;
		}
		return $form->getUseCaptcha();
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @return boolean
	 */
	protected function checkCaptcha($form)
	{
		$code = change_Controller::getInstance()->getContext()->getRequest()->getModuleParameter('form', 'CHANGE_CAPTCHA');
		return !$this->hasToUseCaptcha($form) || FormHelper::checkCaptchaForKey($code, strval('form' . $form->getId()));
	}

	/**
	 * @param form_persistentdocument_baseform $form
	 * @param integer[] $excludeIds
	 */
	public function getValidActivationFields($form, $excludeIds = array())
	{
		$query = form_FieldService::getInstance()->createQuery();
		$query->add(Restrictions::descendentOf($form->getId()));
		if (f_util_ArrayUtils::isNotEmpty($excludeIds))
		{
			$query->add(Restrictions::notin('id', $excludeIds));
		}
		$validFields = array();
		foreach ($query->find() as $field)
		{
			if ($field instanceof form_persistentdocument_boolean || $field instanceof form_persistentdocument_list)
			{
				$validFields[] = $field;
			}
		}
		return $validFields;
	}
}