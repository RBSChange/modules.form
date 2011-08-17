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
		
		f_event_EventManager::dispatchEvent(self::FORM_SUBMITTED_EVENT_NAME, $form, array('response' => $response, 'request' => $request, 'referer' => $request->getParameter('backUrl')));
				
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
		$notification = $form->getNotification();
		if (!($notification instanceof notification_persistentdocument_notification))
		{
			return true;
		}
		
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

		if ($recipients->isEmpty())
		{
			return true;	
		}
		
		$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
		$ns = notification_NotificationService::getInstance();
		$configuredNotif = $ns->getConfiguredByCodeName($notification->getCodeName(), $website->getId(), $website->getLang());
		if ($configuredNotif instanceof notification_persistentdocument_notification)
		{
		 	$configuredNotif->setSendingModuleName('form');
			$configuredNotif->setSendingReplyTo($replyTo);
			$configuredNotif->setSendingSenderEmail($this->getOverrideNotificationSender($form));
			if ($form->getMessageSendingType() == self::SEND_EMAIL_AND_APPEND_TO_MAILBOX)
			{
				$configuredNotif->setSendingMailService(mailbox_MessageService::getInstance());
				Framework::debug(__METHOD__." Getting mailbox_MessageService instance.");
			}
			else
			{
				$configuredNotif->setSendingMailService(MailService::getInstance());
				Framework::debug(__METHOD__." Getting MailService instance.");
			}
			
			
			$callback = array($this, 'getResponseNotifParameters');
			$params = array(
				'form' => $form, 
				'response' => $response, 
				'replyTo' => $replyTo
			);		
			if (!$configuredNotif->getDocumentService()->sendNotificationCallback($configuredNotif, $recipients, $callback, $params))
			{
				return false;
			}
			
			if ($copyMail === null)
			{
				$copyRecipient = new mail_MessageRecipients();
				$copyRecipient->setTo(array($copyMail));
				return $configuredNotif->getDocumentService()->sendNotificationCallback($configuredNotif, $copyRecipient, $callback, $params);
			}
		}
		return true;
	}
	
	/**
	 * @param array $params
	 * @return array
	 */
	public function getResponseNotifParameters($params)
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
	
	/**
	 * @param form_persistentdocument_form $form
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	public function addTreeAttributes($form, $moduleName, $treeType, &$nodeAttributes)
	{
		// Used by action activation check.
		$nodeAttributes['responseCount'] = $form->getResponseCount();
		$nodeAttributes['activeResponse'] = $form->getActiveResponseCount();
	}
}