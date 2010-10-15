<?php
class form_BlockFormAction extends block_BlockAction
{
	protected function getFormId()
	{
		return $this->getDocumentIdParameter();
	}

	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String the view name
	 */
	public function execute($context, $request)
	{
		$id = $this->getFormId();
		if (empty($id))
		{
			return block_BlockView::NONE;
		}
		
		$form = DocumentHelper::getDocumentInstance($id);
		if (!$form->isPublished())
		{
			return block_BlockView::NONE;
		}
		
		$form->getDocumentNode()->getDescendents();
		$this->setParameter('form', $form);
		
		if ($context->inBackofficeMode())
        {
            return block_BlockView::DUMMY;
        }

		if ($request->hasParameter("receiverIds"))
		{
			$receiverIds = explode(",", $request->getParameter("receiverIds"));
			$receiverLabels = array();
			foreach ($receiverIds as $receiverId)
			{
				if (is_numeric($receiverId))
				{
					try 
					{
						$receiver = DocumentHelper::getDocumentInstance($receiverId);
						$receiverLabels[] = $receiver->getLabel();
					}
					catch (Exception $e)
					{
						Framework::exception($e);
						$receiverLabels[] = $receiverId;
					}
				}
				elseif (f_util_StringUtils::isNotEmpty($receiverId))
				{
					$receiverLabels[] = $receiverId;
				}
			}
			$this->setParameter("receiverLabels", $receiverLabels);
		}

		if ($this->isSuccess($context, $request))
		{
			$view = block_BlockView::SUCCESS;
		}
		else if ($this->isFormPosted($request))
		{
			try
			{
				$form->getDocumentService()->saveFormData($form, $request);
				$user = $context->getGlobalContext()->getUser();
				$confirmpage = $form->getConfirmpage();
				if($confirmpage instanceof website_persistentdocument_page && $confirmpage->isPublished())
				{
					$user->setAttribute('form_success_parameters_confirmpage_'.$form->getId(), $request->getParameters());
					HttpController::getInstance()->redirectToUrl(LinkHelper::getDocumentUrl($confirmpage, $this->getLang(), array('formParam[id]'=>$form->getId())));
					return block_BlockView::NONE;
				}
				$user->setAttribute('form_success_parameters_noconfirmpage_'.$form->getId(), $request->getParameters());
				HttpController::getInstance()->redirectToUrl(LinkHelper::getCurrentUrl());
				return block_BlockView::NONE;
			}
			catch (form_FormValidationException $e)
			{
				$this->setParameter('errors', $e->getErrorCollection());
				$view = block_BlockView::INPUT;
			}
		}
		else
		{
			$view = block_BlockView::INPUT;
		}

		// Calls the BlockFormDecorator if present.
		$formId = $form->getFormid();
		$matches = null;
		if (preg_match('#^modules_([a-z]+)/([a-z]+)$#', $formId, $matches))
		{
			$extendClassName = $matches[1].'_BlockForm'.ucfirst($matches[2]).'Decorator';
			if (f_util_ClassUtils::classExists($extendClassName))
			{
				$instance = new $extendClassName($this);
				if ($instance instanceof form_BlockFormDecorator)
				{
					$instance->execute($context, $request);
				}
				else
				{
					Framework::warn("\"$extendClassName\" is not an instance of \"block_BlockDecorator\": form \"$formId\" won't be decorated.");
				}
			}
		}
		return $view;
	}


	/**
	 * @param block_BlockRequest $request
	 */
	protected final function isFormPosted($request)
	{
		return form_FormService::getInstance()->isPostedFormId($this->getFormId(), $request);
	}


	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return boolean the view name
	 */
	protected final function isSuccess($context, $request)
	{
		$user = $context->getGlobalContext()->getUser();
		return $user->hasAttribute('form_success_parameters_noconfirmpage_'.$this->getFormId());
	}
}
