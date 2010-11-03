<?php
class form_FormService extends form_BaseformService
{
	const SEND_EMAIL_AND_APPEND_TO_MAILBOX = 2;
	const SEND_EMAIL_ONLY                  = 1;
	const DO_NOT_SEND_MESSAGE              = 0;
	
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
	 * Create a query based on 'modules_form/form' model.
	 * Only documents that are strictly instance of modules_form/form
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_form/form', false);
	}
	
	/**
	 * Called when a form is created:
	 * - creates the notification that is bound to the form.
	 * @param form_persistentdocument_form $document
	 * @param f_persistentdocument_PersistentTreeNode $parentNodeId
	 */
	protected function preInsert($document, $parentNodeId)
	{
		if ($document->getNotification() === null)
		{
			$this->createNotification($document);
		}
	}
	
	/**
	 * @param form_persistentdocument_form $document
	 * @return void
	 */
	protected function preDelete($document)
	{
		parent::preDelete($document);
		
		if ($document->getResponseCount())
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
	 * @param form_persistentdocument_form $form
	 */
	protected function getNotificationsToUpdate($form)
	{
		return array_merge(parent::getNotificationsToUpdate($form), array($form->getNotification()));
	}

	/**
	 * @param form_persistentdocument_form $form
	 * @param form_persistentdocument_response $response
	 * @param block_BlockRequest $request
	 * @param string $replyTo
	 * @return array an associative array contaning at least the key "success" with a boolean value. This array will be accessible during the acknowledgment notification sending.
	 */
	protected function handleData($form, $fields, $response, $request, $replyTo)
	{
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
				
		$sendingType = $form->getMessageSendingType();
		if ($sendingType !== self::SEND_EMAIL_AND_APPEND_TO_MAILBOX && $sendingType !== self::SEND_EMAIL_ONLY)
		{
			Framework::debug(__METHOD__ . " No message to send.");
			return true;
		}
		
		$copyMail = null;
		$data = $request->getParameters();
		foreach ($fields as $field)
		{
			if (!$field->getDocumentService()->isConditionValid($field, $data))
			{
				continue;
			}

			if ($field instanceof form_persistentdocument_mail)
			{
				if ($field->getIsReceiver())
				{
					$fieldName = $field->getFieldName();
					$copyMail = isset($data[$fieldName]) ? $data[$fieldName] : null;
				}
			}
		}

		if (Framework::isDebugEnabled())
		{
			Framework::debug(__METHOD__ . " A message has to be sent: ".$form->getMessageSendingType());
		}
		$result = array();
		$result['success'] = $this->sendEmail($form, $response, $request, $copyMail, $replyTo);
		if ($form->getSaveResponse())
		{
			$result['response'] = $response;
		}
		return $result;
	}
	
	/**
	 * @param form_persistentdocument_response $response
	 * @param form_persistentdocument_baseform $form
	 */
	protected function addResponseToForm($response, $form)
	{
		$response->setParentForm($form);
		$form->setResponseCount($form->getResponseCount()+1);
	}
	
	/**
	 * @param form_persistentdocument_baseform $document
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

		if ($request->hasParameter('receiverIds'))
		{
			$this->handleReceveirIds($request->getParameter('receiverIds'), $recipients);
		}

		// Determine the message recipients for this form.
		// Note that the following method may be overriden to allow the developper
		// to build specific mail recipients, depending on the request data for
		// example.
		$this->buildMessageRecipients($form, $recipients, $request);

		if (!$recipients->isEmpty())
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
			$contentTemplate->setAttribute('response', $response->getResponseInfos());

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
				return $ns->send($form->getNotification(), $recipients,	$parameters, 'form', $replyTo,
					$this->getOverrideNotificationSender($form)
				);
			}
			else
			{
				$copyRecipient = new mail_MessageRecipients();
				$copyRecipient->setTo(array($copyMail));
				$notification = $form->getNotification();
				$sender = $this->getOverrideNotificationSender($form);
				return $ns->send($notification, $recipients, $parameters, 'form', $replyTo, $sender)
					&& $ns->send($notification, $copyRecipient, $parameters, 'form', $replyTo, $sender);
			}
		}
		return true;
	}

	/**
	 * @param string $receiverIdStr
	 * @param mail_MessageRecipients $recipients
	 */
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
		// If there is no recipientGroup, we can exit this method.
		if ($form->getRecipientGroupCount() == 0)
		{
			return;
		}
		
		// Init arrays.
		$toArray = ($recipients->hasTo()) ? $recipients->getTo() : array();
		$ccArray = ($recipients->hasCC()) ? $recipients->getCC() : array();
		$bccArray = ($recipients->hasBCC()) ? $recipients->getBCC() : array();
		
		// The following holds the ID of the recipientGroups selected by the user.
		$selectedGroupArray = array();
		
		// Retrieve the recipientGroups selected by the user.
		// Depending on the selection type (single or multiple), this value may
		// be either a string or an array.
		if ($request->hasParameter(self::RECIPIENT_GROUP_FIELD_NAME))
		{
			$selectedGroupIds = $request->getParameter(self::RECIPIENT_GROUP_FIELD_NAME);
			if (is_string($selectedGroupIds))
			{
				$selectedGroupIds = array($selectedGroupIds);
			}
			
			// Convert each value into an integer.
			$selectedGroupIds = array_map('intval', $selectedGroupIds);
			
			foreach ($form->getRecipientGroupArray() as $recipientGroup)
			{
				if (in_array($recipientGroup->getId(), $selectedGroupIds))
				{
					$selectedGroupArray[] = $recipientGroup;
				}
			}
			
			// If the form holds more than one recipientGroups and if the request
			// contains no recipientGroup selection, we throw an Exception.
			if (count($selectedGroupArray) == 0)
			{
				throw new form_FormException('Unable to determine the recipients.');
			}
		}
		else
		{
			$selectedGroupArray = $form->getRecipientGroupArray();
		}
		
		// Iterates over the form's recipientGroups and skip the ones that have
		// not been selected by the user.
		foreach ($selectedGroupArray as $recipientGroup)
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
		
		// Update mail_MessageRecipients object.
		$recipients->setTo(array_unique($toArray));
		$recipients->setCC(array_unique($ccArray));
		$recipients->setBCC(array_unique($bccArray));
		
		if ($recipients->isEmpty() && Framework::isWarnEnabled())
		{
			Framework::warn(__METHOD__ . " recipients is empty.");
		}
	}

	/**
	 * @param form_persistentdocument_form $newDocument
	 * @param form_persistentdocument_form $originalDocument
	 * @param Integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		parent::preDuplicate($newDocument, $originalDocument, $parentNodeId);
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
		if ($oldNotification !== null)
		{
			$newNotification = $newDocument->getNotification();
			$this->duplicateNotificationInfo($oldNotification, $newNotification);
			$newNotification->save();
		}
		
		parent::postDuplicate($newDocument, $originalDocument, $parentNodeId);
	}
	
	/**
	 * @param form_persistentdocument_form $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		
		$resume['properties']['responseCount'] = strval($document->getResponseCount());
		
		$notification = $document->getNotification();
		$openNotificationUri = join(',' , array('notification', 'openDocument', $notification->getPersistentModel()->getBackofficeName(), $notification->getId(), 'properties'));
		$backUri = join(',', array('form', 'openDocument', $document->getPersistentModel()->getBackofficeName(), $document->getId(), 'resume'));
		$resume['properties']['notification'] = array('uri' => $openNotificationUri, 'label' => f_Locale::translateUI('&modules.uixul.bo.doceditor.open;'), 'backuri' => $backUri);
		
		return $resume;
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return array
	 */
	public function getResponseDataByForm($form, $offset = 0, $limit = null)
	{
		$query = form_ResponseService::getInstance()->createQuery()->add(Restrictions::eq('parentForm', $form))
			->setProjection(Projections::rowCount('count'));
		$row = $query->findUnique();
		$data = array('count' => $row['count'], 'startIndex' => $offset);
		
		$query = form_ResponseService::getInstance()->createQuery()->add(Restrictions::eq('parentForm', $form))
			->addOrder(Order::desc('document_creationdate'));
		if ($limit !== null)
		{
			$query->setFirstResult($offset)->setMaxResults($limit);
		}
		
		$responsesInfos = array();
		foreach ($query->find() as $response)
		{
			$responsesInfos[] = $response->getResponseInfos();
		}
		$data['responsesInfos'] = $responsesInfos;
		return $data;
	}
	
	// Deprecated.
	
	/**
	 * @deprecated use form_FieldService::fixRequiredConstraint()
	 */
	public function fixRequiredConstraint($document)
	{
		form_FieldService::getInstance()->fixRequiredConstraint($document);
	}
	
	/**
	 * @deprecated with no replacement
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
	 * @deprecated
	 */
	public function buildContentsFromRequest($nodes, &$contents, $request, $form)
	{
		$parameters = $request->getParameters();
		$parameters; // Avoid Eclipse warning...
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
		    		'id' => $document->getId(),
		    		'label' => $document->getLabel(),
		    		'description' => $document->getDescription(),
		    		'elements' => $elements
				);
				$html = $templateObject->setAttribute('group', $attributes);
			}
			else
			{
				if ($document instanceof form_persistentdocument_field)
				{
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load($document->getSurroundingTemplate());
					$html = FormHelper::fromFieldDocument($document, isset($parameters[$document->getFieldName()]) ? $parameters[$document->getFieldName()] : $document->getDefaultValue());
					$attributes = array(
			    		'id' => $document->getId(),
			    		'label' => $document->getLabel(),
			    		'description' => $document->getHelpText(),
			    		'required' => $document->getRequired(),
			    		'display' => f_util_ClassUtils::methodExists($document, 'getDisplay') ? $document->getDisplay() : '',
			    		'html' => $html
					);
				}
				else if ($document instanceof form_persistentdocument_freecontent)
				{
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$form->getMarkup())->load('Form-FreeContent');
					$attributes = array(
			    		'id' => $document->getId(),
			    		'label' => $document->getLabel(),
			    		'description' => $document->getText(),
			    		'required' => false,
			    		'html' => ''
			    	);
				}
				$templateObject->setAttribute('field', $attributes);
			}

			$contents[$document->getId()] = $templateObject->execute();
		}
	}
	
	/**
	 * @deprecated use sharethis module instead.
	 */
	public function getRecommandFormUrl()
	{
		try
		{
			$page = TagService::getInstance()->getDocumentByContextualTag(
				'contextual_website_website_modules_form_recommand-page',
				website_WebsiteModuleService::getInstance()->getCurrentWebsite()
			);
			$parameters = array('formParam[recommandFeature]' => website_WebsiteModuleService::getInstance()->getCurrentPageId());
			return LinkHelper::getDocumentUrl($page, null, $parameters);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return null;
	}
	
	/**
	 * @deprecated use getByFormId()
	 */
	public function getFormByFormId($formId)
	{
		return $this->getByFormId($formId);
	}
	
	/**
	 * @deprecated
	 */
	public function isPostedFormId($formId, $formRequest)
	{
		return !is_null($formRequest) && $formRequest->hasParameter('submit_' . $formId);
	}
	
	/**
	 * @deprecated
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
}