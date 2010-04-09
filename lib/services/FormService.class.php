<?php
class form_FormService extends f_persistentdocument_DocumentService
{
	const FORM_SUBMITTED_EVENT_NAME = 'formSubmitted';
	const FORM_VALIDATE_EVENT_NAME = 'formValidate';
	const FORM_INIT_DATA_EVENT_NAME = 'formInitData';

	const SEND_EMAIL_AND_APPEND_TO_MAILBOX = 2;
	const SEND_EMAIL_ONLY                  = 1;
	const DO_NOT_SEND_MESSAGE              = 0;

	// These values also appear in the 'modules_form/field' Document Model, in
	// the 'notEqual' validator.
	const CONTENT_REPLACEMENT_NAME    = 'FIELDS';
	const FORM_LABEL_REPLACEMENT_NAME = 'FORM_LABEL';

	const RECIPIENT_GROUP_FIELD_NAME  = 'recipientGroups';
	const RECIPIENT_GROUP_LIST_ID     = 'modules_form/recipientgrouplist';

	/**
	 * @var form_FormService
	 */
	private static $instance;

	/**
	 * @return form_FormService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}


	/**
	 * @return form_persistentdocument_form
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/form');
	}

	/**
	 * Create a query based on 'modules_form/form' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/form');
	}

	/**
	 * @param String $formId
	 * @return form_persistentdocument_form
	 */
	public function getFormByFormId($formId)
	{
		$query = $this->createQuery()->add(Restrictions::eq('formid', $formId));
		return $query->findUnique();
	}


	/**
	 * Called when a form is saved: sets a unique ID to the form if it has not been set.
	 *
	 * @param form_persistentdocument_form $document
	 * @param f_persistentdocument_PersistentTreeNode $parentNodeId
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		if (is_null($document->getFormid()))
		{
			$document->setFormid(uniqid('formid_'));
		}
	}

	/**
	 * Called when a form is created:
	 * - sets a unique ID to the form if it has not been set,
	 * - creates the notification that is bound to the form.
	 *
	 * @param form_persistentdocument_form $document
	 * @param f_persistentdocument_PersistentTreeNode $parentNodeId
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		if ($document->getNotification() === null)
		{
			$this->createNotification($document);
		}
	}


	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal).
	 * @return void
	 */
	protected function preUpdate($document, $parentNodeId)
	{
		if ($document->isPropertyModified('label'))
		{
			$notification = $document->getNotification();
			if ($notification !== null)
			{
				if (!$notification->isContextLangAvailable())
				{
					$notification->setSubject($notification->getVoSubject());
					$notification->setBody($notification->getVoBody());
				}
				$notification->setLabel($document->getLabel());
				$notification->save();
			}
		}

		// If there are more than one recipientGroup defined for this form,
		// the form MUST have a field to select the destination recipientGroup.
		if ($document->getRecipientGroupCount() > 1 && is_null($this->getFieldByName($document, self::RECIPIENT_GROUP_FIELD_NAME)))
		{
			throw new form_FormException("The form contains multiple recipients: it must hold a \"recipientGroupList\" field.");
		}
	}

	/**
	 * @param form_persistentdocument_form $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		if($document->getResponseCount())
		{
			$responses = $this->pp->createQuery('modules_form/response')->add(Restrictions::eq('parentForm.id', $document->getId()))->find();
			foreach ($responses as $response)
			{
				$response->delete();
			}
		}
	}


	/**
	 * Creates the notification that is bound to the form.
	 *
	 * @param form_persistentdocument_form $form
	 */
	protected function createNotification($form)
	{
		$notification = notification_NotificationService::getInstance()->getNewDocumentInstance();
		$notification->setLabel($form->getLabel());
		$notification->setCodename($form->getFormid().'_notification');
		$notification->setTemplate('default');
		$notification->setSubject($form->getLabel());
		$notification->setBody('{'.self::CONTENT_REPLACEMENT_NAME.'}');
		$notification->save(ModuleService::getInstance()->getSystemFolderId('notification', 'form'));
		$form->setNotification($notification);
	}


	/**
	 * Called by the FieldService whenever a field is removed from the given $form.
	 *
	 * @param form_persistentdocument_form $form
	 */
	public function onFieldDeleted($form)
	{
		$this->publishDocument($form);
		$this->updateNotification($form);
	}


	/**
	 * Called by the FieldService whenever a field is added into the given $form.
	 *
	 * @param form_persistentdocument_form $form
	 */
	public function onFieldAdded($form)
	{
		$this->publishDocument($form);
		$this->updateNotification($form);
	}


	/**
	 * Called by the FieldService whenever a field is updated into the given $form.
	 *
	 * @param form_persistentdocument_form $form
	 */
	public function onFieldChanged($form)
	{
		$this->updateNotification($form);
	}


	/**
	 * Updates the notification for the given $form.
	 *
	 * @param form_persistentdocument_form $form
	 */
	private function updateNotification($form)
	{
		$notification = $form->getNotification();
		if ( ! is_null($notification) )
		{
			$fieldDocArray = $this->getFields($form);
			foreach ($fieldDocArray as $fieldDoc)
			{
				$fieldArray[] = '{'. $fieldDoc->getFieldName() . '}=' . $fieldDoc->getlabel();
			}
		}
		$fieldArray[] = '{'. self::CONTENT_REPLACEMENT_NAME. '}';
		$fieldArray[] = '{'. self::FORM_LABEL_REPLACEMENT_NAME. '}';
		$notification->setAvailableparameters(implode("\n", $fieldArray));
		$notification->save();
	}


	/**
	 * @param form_persistentdocument_form $form
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
		if ($form->getUseCaptcha() && !FormHelper::checkCaptcha($request->getParameter(CAPTCHA_SESSION_KEY)))
		{
			$errors->append(f_Locale::translate("&modules.form.bo.general.Captcha-check-failed;"));
		}

		$eventParam = array('form' => $form, 'request' => $request, 'errors' => $errors);
		f_event_EventManager::dispatchEvent(self::FORM_VALIDATE_EVENT_NAME, $this, $eventParam);

	}

	/**
	 * @param form_persistentdocument_form $form
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
	 * @param form_persistentdocument_form $form
	 * @return f_persistentdocument_criteria_Query
	 */
	private function setConditionalElementsFilter($query, $form)
	{
		return $query->add(Restrictions::descendentOf($form->getId()))->add(Restrictions::isNotNull('activationQuestion'));
	}

	/**
	 * @param form_persistentdocument_form $form
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
	 * @param form_persistentdocument_form $form
	 * @param block_BlockRequest $request
	 * @return void
	 */
	public function saveFormData($form, $request)
	{
		$errors = new validation_Errors();
		$this->validate($form, $request, $errors);
		if ( ! $errors->isEmpty() )
		{
			throw new form_FormValidationException("Form does not validate", $errors);
		}

		$data = $request->getParameters();
		$domDoc = new DOMDocument('1.0', 'utf-8');
		$domDoc->formatOutput = true;
		$rootElm = $domDoc->createElement('response');
		$rootElm->setAttribute('lang', RequestContext::getInstance()->getLang());
		$rootElm->setAttribute('date', date('Y-m-d H:i:s'));
		$domDoc->appendChild($rootElm);
		$fields = $this->getFields($form);
		$copyMail = null;
		$replyTo = null;
		foreach ($fields as $field)
		{
			if (!$field->getDocumentService()->isConditionValid($field, $data))
			{
				continue;
			}

			$fieldName = $field->getFieldName();
			if ($field instanceof form_persistentdocument_file)
			{
				$rawValue = $request->getUploadedFileInformation($fieldName);
			}
			else
			{
				$rawValue = isset($data[$fieldName]) ? $data[$fieldName] : null;
			}

			$fieldElm = $domDoc->createElement('field');
			$fieldElm->setAttribute('name', $fieldName);
			$fieldElm->setAttribute('label', $field->getLabel());
			$fieldElm->setAttribute('type', $field->getType());

			$rootElm->appendChild($fieldElm);

			//Special raw data for uploaded file
			$fieldValue = $field->getDocumentService()->buildXmlElementResponse($field, $fieldElm, $rawValue);
			if (!empty($fieldValue))
			{
				$fieldElm->appendChild($domDoc->createTextNode($fieldValue));
			}
			if ($field instanceof form_persistentdocument_mail)
			{
				if ($field->getIsReceiver())
				{
					$copyMail = $rawValue;
				}
				if ($field->getUseAsReply())
				{
					$replyTo = $rawValue;
				}
			}
		}

		$rs = form_ResponseService::getInstance();

		$response = $rs->getNewDocumentInstance();
		$response->setContents($domDoc->saveXML());
		$response->setLabel(f_Locale::translate("&modules.form.bo.general.Form-response-title;", array('form' => $form->getLabel())));

		if ($form->getSaveResponse())
		{
			$tm = f_persistentdocument_TransactionManager::getInstance();
			try
			{
				$tm->beginTransaction();
				$this->addResponseToForm($response, $form);
				$response->save();
				$form->save();

				$tm->commit();
			}
			catch (Exception $e)
			{
				throw $tm->rollBack($e);
			}
		}

		f_event_EventManager::dispatchEvent(self::FORM_SUBMITTED_EVENT_NAME, $form, array('response' => $response, 'request' => $request, 'referer' => $request->getParameter(form_FormConstants::BACK_URL_PARAMETER)));

		switch ($form->getMessageSendingType())
		{
			case self::SEND_EMAIL_AND_APPEND_TO_MAILBOX :
			case self::SEND_EMAIL_ONLY :
				Framework::debug("[FormService] A message has to be sent: ".$form->getMessageSendingType());
				return $this->sendEmail($form, $response, $request, $copyMail, $replyTo);
				break;
			default :
				Framework::debug("[FormService] No message to send.");
				break;
		}
		return true;
	}


	protected function addResponseToForm($response, $form)
	{
		$response->setParentForm($form);
		$form->setResponseCount($form->getResponseCount()+1);
	}


	/**
	 * @param form_persistentdocument_form $form
	 * @param form_persistentdocument_response $response
	 * @param block_BlockRequest $request
	 * @param String $copyMail
	 * @param String $replyTo
	 * @return void
	 */
	private function sendEmail($form, $response, $request, $copyMail, $replyTo)
	{
		$recipients = new mail_MessageRecipients();

		if ( $request->hasParameter('receiverIds') )
		{
			$this->handleReceveirIds($request->getParameter('receiverIds'), $recipients);
		}

		// Determine the message recipients for this form.
		// Note that the following method may be overriden to allow the developper
		// to build specific mail recipients, depending on the request data for
		// example.
		$this->buildMessageRecipients($form, $recipients, $request);

		if ( ! $recipients->isEmpty() )
		{
			if ($form->getMessageSendingType() == self::SEND_EMAIL_AND_APPEND_TO_MAILBOX)
			{
				$messageService = mailbox_MessageService::getInstance();
				Framework::debug(__METHOD__." Getting mailbox_MessageService instance.");
			}
			else
			{
				$messageService = MailService::getInstance();
				Framework::debug(__METHOD__." Getting MailService instance.");
			}

			$contentTemplate = TemplateLoader::getInstance()->setPackageName('modules_form')->setMimeContentType(K::HTML)->load('Form-MailContent');
			$contentTemplate->setAttribute('items', $response->getAllData());

			$parameters = $response->getData();
			$parameters[self::CONTENT_REPLACEMENT_NAME] = $contentTemplate->execute();
			$parameters[self::FORM_LABEL_REPLACEMENT_NAME] = $form->getLabel();

			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__." Form \"".$form->getLabel()."\" (id=".$form->getId().")");
				Framework::debug(__METHOD__." Parameters: ".var_export($parameters, true));
				if ($recipients->hasTo())
				{
					Framework::debug(__METHOD__." To      : ".join(", ", $recipients->getTo()));
				}
				if ($recipients->hasBCC())
				{
					Framework::debug(__METHOD__." CC      : ".join(", ", $recipients->getCC()));
				}
				if ($recipients->hasBCC())
				{
					Framework::debug(__METHOD__." BCC     : ".join(", ", $recipients->getBcc()));
				}
				Framework::debug(__METHOD__." ReplyTo : ".$replyTo);
			}
			$ns = notification_NotificationService::getInstance();
			$ns->setMessageService($messageService);
			if ($copyMail === null)
			{
				return $ns->send($form->getNotification(), $recipients,
				$parameters, 'form', $replyTo,
				$this->getOverrideNotificationSender($form));
			}
			else
			{
				$copyRecipient = new mail_MessageRecipients();
				$copyRecipient->setTo(array($copyMail));
				$notification = $form->getNotification();
				$sender = $this->getOverrideNotificationSender($form);
				return $ns->send($notification, $recipients,
				$parameters, 'form', $replyTo, $sender)
				&& $ns->send($notification, $copyRecipient,
				$parameters, 'form', $replyTo, $sender);
			}
		}
		return true;
	}

	/**
	 * @param form_persistentdocument_form $form
	 */
	private function getOverrideNotificationSender($form)
	{
		$notification = $form->getNotification();
		$preferenceDocument = ModuleService::getInstance()->getPreferencesDocument('form');
		if ($preferenceDocument !== null)
		{
			$defaultSender = $preferenceDocument->getSender();
			if ($defaultSender !== null)
			{
				return f_util_ArrayUtils::firstElement($defaultSender->getEmailAddresses());
			}
		}
		return null;
	}

	private function handleReceveirIds($receiverIdStr, &$recipients)
	{
		$emailAddressArray = array();

		// Let's check if $receiverIdStr contains a list of email addresses...
		$errors = new validation_Errors();
		$validate = new validation_EmailsValidator();
		$validate->validate(new validation_Property(null, $receiverIdStr), $errors);

		// Errors have been found: $receiverIdStr does not seem to contain
		// email addresses, maybe it contains contacts ID.
		if ( ! $errors->isEmpty() )
		{
			// try to get the component and get its emails
			$receiverIdArray = explode(',', $receiverIdStr);
			foreach ($receiverIdArray as $receiverId)
			{
				$receiverId = f_util_Convert::fixDataType($receiverId);
				if (is_integer($receiverId))
				{
					try
					{
						// Merge the previously found email addresses with
						// the one of the contact with ID $receiverId.
						$emailAddressArray = array_merge(
						$emailAddressArray,
						DocumentHelper::getDocumentInstance($receiverId)->getEmailAddresses()
						);
					}
					catch (Exception $e)
					{
						Framework::exception($e);
					}
				}
			}
		}
		else
		{
			$emailAddressArray = explode(',', $receiverIdStr);
			$emailAddressArray = array_map("trim", $emailAddressArray);
		}

		$recipients->setTo($emailAddressArray);
	}

	/**
	 * Builds the mail_MessageRecipients according to the recipients selected by
	 * the frontoffice user.
	 * This method may be overriden via the injection mechanism to allow the
	 * developper build a specific mail_MessageRecipients to suit the needs of
	 * the project.
	 *
	 * @param form_persistentdocument_form $form
	 * @param mail_MessageRecipients $recipients
	 * @param block_BlockRequest $request
	 */
	protected function buildMessageRecipients($form, &$recipients, &$request)
	{
		$formRecipientCount = $form->getRecipientGroupCount();

		// If there is no recipientGroup, we can exit this method.
		if ($formRecipientCount == 0)
		{
			return;
		}

		// Init to's
		if ($recipients->hasTo())
		{
			$toArray = $recipients->getTo();
		}
		else
		{
			$toArray = array();
		}

		// Init cc's
		if ($recipients->hasCC())
		{
			$ccArray = $recipients->getCC();
		}
		else
		{
			$ccArray = array();
		}

		// Init bcc's
		if ($recipients->hasBCC())
		{
			$bccArray = $recipients->getBCC();
		}
		else
		{
			$bccArray = array();
		}

		// The following holds the ID of the recipientGroups selected by the user.
		$selectedGroupArray = array();

		// If there is only one recipientGroup, it is automatically selected.
		if ($formRecipientCount == 1)
		{
			array_push($selectedGroupArray, $form->getRecipientGroup(0)->getId());
		}
		// Retrieve the recipientGroups selected by the user.
		// Depending on the selection type (single or multiple), this value may
		// be either a string or an array.
		else if ($formRecipientCount > 1)
		{
			$selectedGroupArray = $request->getParameter(self::RECIPIENT_GROUP_FIELD_NAME);
			if (is_string($selectedGroupArray))
			{
				$selectedGroupArray = array($selectedGroupArray);
			}
			// Convert each value into an integer.
			$selectedGroupArray = array_map("intval", $selectedGroupArray);

			// If the form holds more than one recipientGroups and if the request
			// contains no recipientGroup selection, we throw an Exception.
			if (count($selectedGroupArray) == 0)
			{
				throw new form_FormException("Unable to determine the recipients.");
			}
		}

		// Iterates over the form's recipientGroups and skip the ones that have
		// not been selected by the user.
		foreach ($form->getRecipientGroupArray() as $recipientGroup)
		{
			if (in_array($recipientGroup->getId(), $selectedGroupArray))
			{
				foreach ($recipientGroup->getToArray() as $contact)
				{
					$toArray = array_merge($toArray, $contact->getEmailAddresses());
				}
				foreach ($recipientGroup->getCcArray() as $contact)
				{
					$ccArray = array_merge($ccArray, $contact->getEmailAddresses());
				}
				foreach ($recipientGroup->getBccArray() as $contact)
				{
					$bccArray = array_merge($bccArray, $contact->getEmailAddresses());
				}
			}
		}

		// Update mail_MessageRecipients object.
		$recipients->setTo(array_unique($toArray));
		$recipients->setCC(array_unique($ccArray));
		$recipients->setBCC(array_unique($bccArray));

		if ($recipients->isEmpty() && Framework::isWarnEnabled())
		{
			Framework::warn(__METHOD__." recipients is empty.");
		}
	}


	/**
	 * @param form_persistentdocument_form $form
	 * @param form_persistentdocument_response $response
	 * @return void
	 */
	private function getEmailHeader($form, $response)
	{
		return $response->getDocumentService()->replaceFieldsValue($response, $form->getEmailHeader());
	}


	/**
	 * @param form_persistentdocument_form $form
	 * @param form_persistentdocument_response $response
	 * @return void
	 */
	private function getEmailSubject($form, $response)
	{
		return $response->getDocumentService()->replaceFieldsValue($response, $form->getEmailSubject());
	}


	/**
	 * @param form_persistentdocument_form $form
	 * @param form_persistentdocument_response $response
	 * @return void
	 */
	private function getEmailFooter($form, $response)
	{
		return $response->getDocumentService()->replaceFieldsValue($response, $form->getEmailFooter());
	}

	/**
	 * @param form_persistentdocument_form $form
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
	 * @param form_persistentdocument_form $form
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
	 * @param array<TreeNode> $nodes
	 * @param array $contents
	 * @param array<string,string> $parameters
	 * @param form_persistentdocument_form $form
	 * @deprecated Use buildContentsFromRequest instead (since 2.0.2).
	 */
	public function buildContents($nodes, &$contents, &$parameters, $form)
	{
		$eventParam = array('form' => $form, 'parameters' => &$parameters);
		f_event_EventManager::dispatchEvent(self::FORM_INIT_DATA_EVENT_NAME, $this, $eventParam);

		foreach ($nodes as $node)
		{
			// $document is a form_persistentdocument_field
			$document = $node->getPersistentDocument();

			$html = '';
			if ($document instanceof form_persistentdocument_group)
			{
				$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load('Form-Group');
				$elements = array();
				$this->buildContents($node->getChildren(), $elements, $parameters, $form);
				$attributes = array(
		    		'id'       => $document->getId(),
		    		'label'    => $document->getLabel(),
		    		'description'    => $document->getDescription(),
		    		'elements' => $elements
				);
				$html = $templateObject->setAttribute('group', $attributes);
			}
			else
			{
				$html = FormHelper::fromFieldDocument($document, isset($parameters[$document->getFieldName()]) ? $parameters[$document->getFieldName()] : '');
				$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load($document->getSurroundingTemplate());
				$attributes = array(
		    		'id'       => $document->getId(),
		    		'label'    => $document->getLabel(),
		    		'description' => $document->getHelpText(),
		    		'required' => $document->getRequired(),
		    		'display'  => f_util_ClassUtils::methodExists($document, 'getDisplay') ? $document->getDisplay() : '',
		    		'html'     => $html
				);
				$templateObject->setAttribute('field', $attributes);
			}

			$contents[$document->getId()] = $templateObject->execute();
		}
	}

	/**
	 * @param array<TreeNode> $nodes
	 * @param array $contents
	 * @param block_BlockRequest $request
	 * @param form_persistentdocument_form $form
	 * @since 2.0.2
	 */
	public function buildContentsFromRequest($nodes, &$contents, $request, $form)
	{
		$parameters = $request->getParameters();
		$eventParam = array('form' => $form, 'parameters' => &$parameters, 'isPosted' => $this->isPostedFormId($form->getId(), $request));
		f_event_EventManager::dispatchEvent(self::FORM_INIT_DATA_EVENT_NAME, $this, $eventParam);
		$request->setParametersByRef($parameters);

		foreach ($nodes as $node)
		{
			// $document is a form_persistentdocument_field
			$document = $node->getPersistentDocument();

			$html = '';
			if ($document instanceof form_persistentdocument_group)
			{
				$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load('Form-Group');
				$elements = array();
				$this->buildContentsFromRequest($node->getChildren(), $elements, $request, $form);
				$attributes = array(
		    		'id'       => $document->getId(),
		    		'label'    => $document->getLabel(),
		    		'description'    => $document->getDescription(),
		    		'elements' => $elements
				);
				$html = $templateObject->setAttribute('group', $attributes);
			}
			else
			{
				if ($document instanceof form_persistentdocument_field)
				{
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load($document->getSurroundingTemplate());
					$html = FormHelper::fromFieldDocument($document, isset($parameters[$document->getFieldName()]) ? $parameters[$document->getFieldName()] : '');
					$attributes = array(
			    		'id'       => $document->getId(),
			    		'label'    => $document->getLabel(),
			    		'description' => $document->getHelpText(),
			    		'required' => $document->getRequired(),
			    		'display'  => f_util_ClassUtils::methodExists($document, 'getDisplay') ? $document->getDisplay() : '',
			    		'html'     => $html
					);
				}
				else if ($document instanceof form_persistentdocument_freecontent)
				{
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load('Form-FreeContent');
					$attributes = array(
			    		'id'       => $document->getId(),
			    		'label'    => $document->getLabel(),
			    		'description' => $document->getText(),
			    		'required' => false,
			    		'html'     => ''
			    		);
				}
				$templateObject->setAttribute('field', $attributes);
			}

			$contents[$document->getId()] = $templateObject->execute();
		}
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
	 * @param form_persistentdocument_field $field
	 * @param integer $parentNodeId
	 *
	 * @throws form_FormException When $field is not inside a form
	 * @throws form_FieldAlreadyExistsException When the name of $field is not available
	 */
	public function checkFieldNameAvailable($field, $parentNodeId)
	{
		$fieldName = $field->getFieldName();
		$form = DocumentHelper::getDocumentInstance($parentNodeId);
		if ( ! ($form instanceof form_persistentdocument_form) )
		{
			$ds = f_persistentdocument_DocumentService::getInstance();
			$ancestors = $ds->getAncestorsOf($form, 'modules_form/form');
			if (count($ancestors) == 0)
			{
				throw new form_FormException("Field \"".$field->__toString()."\" is not inside a form.");
			}
			$form = $ancestors[0];
		}
		$query = $this->getPersistentProvider()->createQuery('modules_form/field');
		$query->add(Restrictions::descendentOf($form->getId()));
		$query->add(Restrictions::eq('fieldName', $fieldName));
		$result = $query->findUnique();
		if ( ! is_null($result) && $result->getId() != $field->getId() )
		{
			throw new form_FieldAlreadyExistsException(f_Locale::translate('&modules.form.bo.errors.Field-name-alreay-used;', array('fieldName' => $fieldName)));
		}
	}

	/**
	 * Return mail field which is used for reply-to feature
	 * @param form_persistentdocument_form $document
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
	 * @param form_persistentdocument_form
	 */
	public function getPreviewAttributes($document)
	{
		$attributes = array();
		$query = f_persistentdocument_PersistentProvider::getInstance()->createQuery('modules_form/field');
		$query->add(Restrictions::descendentOf($document->getId()));
		$fields = $query->find();
		$attributes['fieldsCount'] = count($fields);
		$attributes['responsesCount'] = $document->getResponseCount();
		return $attributes;
	}


	/**
	 * @param form_persistentdocument_form $document
	 * @return boolean true
	 */
	public function isPublishable($document)
	{
		return parent::isPublishable($document) && count($this->getFields($document)) > 0;
	}


	/**
	 * Returns the URL of the page tagged with the following contextual tag:
	 * contextual_website_website_modules_form_recommand-page
	 *
	 * @return String
	 */
	public function getRecommandFormUrl()
	{
		try
		{
			return LinkHelper::getUrl(
			website_WebsiteModuleService::getDocumentByContextualTag(
					'contextual_website_website_modules_form_recommand-page',
			website_WebsiteModuleService::getInstance()->getCurrentWebsite()
			),
			null,
			array('formParam[recommandFeature]' => website_WebsiteModuleService::getInstance()->getCurrentPageId())
			);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return null;
	}

	/**
	 * @param Integer $formId
	 * @param block_BlockRequest $formRequest
	 * @return Boolean
	 */
	public function isPostedFormId($formId, $formRequest)
	{
		return !is_null($formRequest) && $formRequest->hasParameter('submit_' . $formId);
	}

	/**
	 * @param form_persistentdocument_form $form
	 * @param block_BlockRequest $formRequest
	 * @param validation_Errors $errors
	 * @param array<String> $scriptArray
	 * @return String
	 */
	public function renderForm($form, $formRequest, $errors, &$scriptArray)
	{
		$scriptArray[] = 'modules.form.lib.js.date-picker.date';
		$scriptArray[] = 'modules.form.lib.js.date-picker.date_'.RequestContext::getInstance()->getLang();
		$scriptArray[] = 'modules.form.lib.js.date-picker.jquery-bgiframe';
		$scriptArray[] = 'modules.form.lib.js.date-picker.jquery-dimensions';
		$scriptArray[] = 'modules.form.lib.js.date-picker.jquery-datePicker';
		$scriptArray[] = 'modules.form.lib.js.form';

		$markup = $form->getMarkup();
		if (!$markup)
		{
			$markup = 'default';
		}

		try
		{
			$template = TemplateLoader::getInstance()->setMimeContentType(K::HTML)
			->setPackageName('modules_form')->setDirectory('templates/markup/'.$markup)->load('Form');

			$template->setAttribute('form', $form);

			$template->setAttribute('selfUrl', $_SERVER['REQUEST_URI']);
			if ($formRequest->hasParameter(form_FormConstants::BACK_URL_PARAMETER))
			{
				$template->setAttribute('backUrl', $formRequest->getParameter(form_FormConstants::BACK_URL_PARAMETER));
			}
			else
			{
				$template->setAttribute('backUrl', $_SERVER['HTTP_REFERER']);
			}

			if (!is_null($errors))
			{
				$template->setAttribute('errors', $errors);
			}

			$fieldContents = array();
			$this->buildContentsFromRequest($form->getDocumentNode()->getChildren(), $fieldContents, $formRequest, $form);
			$template->setAttribute('requestParameters', $formRequest->getParameters());
			$template->setAttribute('elements', $fieldContents);

			return $template->execute(true);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return null;
	}

	/**
	 * @param form_persistentdocument_form $newDocument
	 * @param form_persistentdocument_form $originalDocument
	 * @param Integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$newDocument->setFormid(null);
		$newDocument->setNotification(null);
		$newDocument->setResponseCount(0);
		$newDocument->setArchivedResponseCount(0);
	}

	/**
	 * this method is call before save the duplicate document.
	 * $newDocument has a id affected
	 * Traitment of the children of $originalDocument
	 *
	 * @param form_persistentdocument_form $newDocument
	 * @param form_persistentdocument_form $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$oldNotification = $originalDocument->getNotification();
		$newNotification = $newDocument->getNotification();
		$this->duplicateNotificationInfo($oldNotification, $newNotification);
		$newNotification->save();

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
	}

	/**
	 * @param notification_persistentdocument_notification $oldNotification
	 * @param notification_persistentdocument_notification $newNotification
	 */
	private function duplicateNotificationInfo($oldNotification, $newNotification)
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
	 * @param form_persistentdocument_form $document
	 * @return Integer
	 */
	public function fileResponses($document)
	{
		try
		{
			$this->tm->beginTransaction();
			$count = form_ResponseService::getInstance()->fileForForm($document);
			if ($count > 0)
			{
				$document->setArchivedResponseCount($document->getArchivedResponseCount() + $count);
				if ($document->isModified())
				{
					$this->pp->updateDocument($document);
				}
			}
			$this->tm->commit();
			return $count;
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
		return 0;
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		$openNotificationUri = join(',' , array('notification', 'openDocument', 'modules_notification_notification', $document->getNotification()->getId(), 'properties'));
		$backUri = join(',', array('form', 'openDocument', 'modules_form_form', $document->getId(), 'resume'));
		$resume["properties"]["notification"] = array("uri" => $openNotificationUri, "label" => f_Locale::translateUI("&modules.uixul.bo.doceditor.open;"), "backuri" => $backUri);
		return $resume;
	}
}
