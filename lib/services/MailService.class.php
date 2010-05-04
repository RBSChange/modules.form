<?php
class form_MailService extends form_TextService
{
	/**
	 * @var form_MailService
	 */
	private static $instance;

	/**
	 * @return form_MailService
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
	 * @return form_persistentdocument_mail
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/mail');
	}

	/**
	 * Create a query based on 'modules_form/mail' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/mail');
	}

	/**
	 * @param form_persistentdocument_mail $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @throws form_ReplyToFieldAlreadyExistsException
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{   
		if($document->getMultiline())
		{
			$document->setValidators('emails:true');
		}
		else
		{
			$document->setValidators('email:true');
		}
		
		if ($parentNodeId !== NULL)
		{
		    $form = DocumentHelper::getDocumentInstance($parentNodeId);
		}
		else
		{
		    $form = $this->getFormOf($document);
		}
		
	    if ($form === null)
		{
		    if (Framework::isWarnEnabled())
		    {
		        Framework::warn(__METHOD__ . ' the mail field document ('. $document->__toString() .')is not in a form');		        
		    }
		} 
		else if ($document->getUseAsReply())
		{
		    $oldReplyField = form_BaseFormService::getInstance()->getReplyToField($form);
		    if ($oldReplyField !== null && $oldReplyField !== $document)
			{
			    Framework::error(__METHOD__ . ' Old reply field :' . $oldReplyField->__toString());
				throw new form_ReplyToFieldAlreadyExistsException(f_Locale::translate('&modules.form.bo.errors.Mail-field-for-replyto-exists'));
			}
		}
		parent::preSave($document, $parentNodeId);
	}
}