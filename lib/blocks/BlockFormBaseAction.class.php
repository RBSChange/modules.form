<?php
/**
 * @package modules.form
 */
class form_BlockFormBaseAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return string
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::NONE;
		}
		
		$form = $this->getDocumentParameter();
		if ($form === null || !$form->isPublished())
		{
			return website_BlockView::NONE;
		}
		
		if (!$this->checkAccess($form, $request))
		{
			return 'Forbidden';
		}
		
		$this->getContext()->addScript('modules.website.lib.js.form-validation');
		
		$request->setAttribute('form', $form);
		$request->setAttribute('moduleName', $this->getModuleName());
		
		if ($request->hasParameter('receiverIds'))
		{
			$receiverIds = explode(',', $request->getParameter('receiverIds'));
			$receiverLabels = $this->getReceiverLabels($receiverIds);
			$request->setAttribute('receiverLabels', $receiverLabels);
		}
		
		if (change_Controller::getInstance()->getStorage()->readForUser('form_success_parameters_noconfirmpage_' . $form->getId()))
		{
			$view = $this->getSuccessView($form, $request);
		}
		// Second case is for template compatibility.
		else if ($request->hasParameter($this->getSubmitName($form)) || $request->hasParameter('submit_' . $form->getId()))
		{
			try
			{
				$form->getDocumentService()->saveFormData($form, $request);
				$confirmpage = $form->getConfirmpage();
				if ($confirmpage instanceof website_persistentdocument_page && $confirmpage->isPublished())
				{
					change_Controller::getInstance()->getStorage()->writeForUser('form_success_parameters_confirmpage_' . $form->getId(), $request->getParameters());
					$this->redirectToUrl(LinkHelper::getDocumentUrl($confirmpage, $this->getLang(), array('formParam[id]' => $form->getId())));
					return website_BlockView::NONE;
				}
				change_Controller::getInstance()->getStorage()->writeForUser('form_success_parameters_noconfirmpage_' . $form->getId(), $request->getParameters());
				change_Controller::getInstance()->redirectToUrl(LinkHelper::getCurrentUrl());
				return website_BlockView::NONE;
			}
			catch (form_FormValidationException $e)
			{
				$request->setAttribute('errors', $e->getErrorCollection());
				$view = $this->getInputView($form, $request);
			}
		}
		else
		{
			$view = $this->getInputView($form, $request);
		}
		
		return $view;
	}
	
	/**
	 * @param integer[] $receiverIds
	 * @return string[]
	 */
	protected function getReceiverLabels($receiverIds)
	{
		$receiverLabels = array();
		foreach ($receiverIds as $receiverId)
		{
			$receiver = DocumentHelper::getDocumentInstanceIfExists($receiverId);
			if ($receiver !== null)
			{
				$receiverLabels[] = $receiver->getNavigationLabel();
			}
			elseif (f_util_StringUtils::isNotEmpty($receiverId))
			{
				$receiverLabels[] = $receiverId;
			}
		}
		return $receiverLabels;
	}
	
	/**
	 * Overload this method to handle specific restricted access.
	 * @param form_persistentdocument_form $form
	 * @param f_mvc_Request $request
	 * @return string
	 */
	protected function checkAccess($form, $request)
	{
		return true;
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @param f_mvc_Request $request
	 * @return string
	 */
	protected function getSuccessView($form, $request)
	{
		$user = change_Controller::getInstance()->getContext()->getUser();
		$attr = 'form_success_parameters_noconfirmpage_' . $form->getId();
		$parameters = $user->getAttribute($attr);
		$user->removeAttribute($attr);
		
		$message = $form->getConfirmMessageAsHtml();
		foreach ($parameters as $key => $value)
		{
			$message = str_replace('{' . $key . '}', htmlspecialchars($value), $message);
		}		
		$this->addMessage($message);
		
		if ($form->getUseBackLink())
		{
			$request->setAttribute('back', array(
				'url' => $parameters['backUrl'],
				'label' => LocaleService::getInstance()->trans('m.form.frontoffice.back', array('ucf'))
			));
		}
		
		return $this->getSuccessTemplateByFullName($form);
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return string
	 */
	protected function getSuccessTemplateByFullName($form)
	{
		return $this->getTemplateByFullName('modules_form', 'Form-Success');
	}

	/**
	 * @param form_persistentdocument_form $form
	 * @param f_mvc_Request $request
	 * @return string
	 */
	protected function getInputView($form, $request)
	{
		$context = $this->getContext();
		FormHelper::addScriptsAndStyles($context);
		
		$previousModuleName = FormHelper::getModuleName();
		$moduleName = $this->getModuleName();
		FormHelper::setModuleName($moduleName);
		
		$contents = $this->getContentsFromRequest($form->getDocumentNode()->getChildren(), $request, $form, 1);
		
		FormHelper::setModuleName($previousModuleName);				
		
		$request->setAttribute('elements', $contents);
		$request->setAttribute('selfUrl', $_SERVER['REQUEST_URI']);
		if ($request->getParameter('backUrl'))
		{
			$backUrl = $request->getParameter('backUrl');
		}
		else if (isset($_SERVER['HTTP_REFERER']))
		{
			$backUrl = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$backUrl = website_WebsiteService::getInstance()->getCurrentWebsite()->getUrl();
		}
		$request->setAttribute('requestParameters', $request->getParameters());
		$request->setAttribute('backUrl', $backUrl);
		$request->setAttribute('useCaptcha', $form->getDocumentService()->hasToUseCaptcha($form));
		$request->setAttribute('jQueryConditionalElement', $form->getDocumentService()->getJQueryForConditionalElementsOf($form));
		$request->setAttribute('submitName', $this->getSubmitName($form));
		
		return $this->getInputTemplateByFullName($form);
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return string
	 */
	protected function getSubmitName($form)
	{
		return 'submit_' . $form->getId() . '_' . $this->getBlockId();
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return string
	 */
	protected function getInputTemplateByFullName($form)
	{
		return $this->getTemplateByFullName('modules_form', 'markup/' . $form->getMarkup() . '/Form');
	}

	/**
	 * @param TreeNode[] $nodes
	 * @param block_BlockRequest $request
	 * @param form_persistentdocument_baseform $form
	 * @param integer $level
	 * @return array
	 */
	protected function getContentsFromRequest($nodes, $request, $form, $level = 1)
	{
		$contents = array();
		$markup = $form->getMarkup();
		foreach ($nodes as $node)
		{
			$document = $node->getPersistentDocument();
			if ($document instanceof form_persistentdocument_group)
			{
				$level++;
				$templateObject = change_TemplateLoader::getNewInstance()->setExtension('html')->load('modules', 'form', 'templates', 'markup', $markup, 'Form-Group');
				$elements = $this->getContentsFromRequest($node->getChildren(), $request, $form, $level);
				$attributes = array(
					'label' => $document->getLabelAsHtml(),
					'description' => $document->getDescriptionAsHtml(),
					'elements' => $elements,
					'level' => $level
				);
				$templateObject->setAttribute('group', $attributes);
			}
			else
			{
				if ($document instanceof form_persistentdocument_field)
				{
					$ls = LocaleService::getInstance();
					$lang = RequestContext::getInstance()->getLang();
					$templateObject = change_TemplateLoader::getNewInstance()->setExtension('html')->load('modules', 'form', 'templates', 'markup', $markup, $document->getSurroundingTemplate());
					$html = FormHelper::fromFieldDocument($document, $request->hasParameter($document->getFieldName()) ? $request->getParameter($document->getFieldName()) : $document->getDefaultValue());
					$attributes = array(
						'id' => $document->getId(),
						'label' => $ls->transformLab($document->getLabelAsHtml(), $lang),
						'description' => $document->getHelpTextAsHtml(),
						'required' => $document->getRequired(),
						'display' => f_util_ClassUtils::methodExists($document, 'getDisplay') ? $document->getDisplay() : '',
						'html' => $html
					);
				}
				else if ($document instanceof form_persistentdocument_freecontent)
				{
					$templateObject = change_TemplateLoader::getNewInstance()->setExtension('html')->load('modules', 'form', 'templates', 'markup', $markup, 'Form-FreeContent');
					$attributes = array(
						'id' => $document->getId(),
						'label' => $document->getLabelAsHtml(),
						'description' => $document->getTextAsHtml(),
						'required' => false,
						'html' => ''
					);
				}
				$templateObject->setAttribute('field', $attributes);
			}
			$contents[$document->getId()] = $templateObject->execute();
		}
		return $contents;
	}
}