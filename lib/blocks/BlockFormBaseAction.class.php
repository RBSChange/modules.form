<?php
/**
 * from_BlockFormBaseAction
 * @package modules.form
 */
class form_BlockFormBaseAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}
		
		$form = $this->getDocumentParameter();
		if ($form === null || !$form->isPublished())
		{
			return website_BlockView::NONE;
		}
		
		$request->setAttribute('form', $form);
		$request->setAttribute('moduleName', $this->getModuleName());
		
		if ($request->hasParameter('receiverIds'))
		{
			$receiverIds = explode(',', $request->getParameter('receiverIds'));
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
				else if (f_util_StringUtils::isNotEmpty($receiverId))
				{
					$receiverLabels[] = $receiverId;
				}
			}
			$request->setAttributes('receiverLabels', $receiverLabels);
		}
		
		$agaviUser = $this->getContext()->getGlobalContext()->getUser();
		if ($agaviUser->hasAttribute('form_success_parameters_' . $form->getId()))
		{
			$view = $this->getSuccessView($form, $request);
		}
		else if ($request->hasParameter('submit_' . $form->getId()))
		{
			try
			{
				$form->getDocumentService()->saveFormData($form, $request);
				$agaviUser->setAttribute('form_success_parameters_' . $form->getId(), $request->getParameters());
				$view = $this->getSuccessView($form, $request);
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
	 * @param form_persistentdocument_form $form
	 * @param f_mvc_Request $request
	 * @return String
	 */
	protected function getSuccessView($form, $request)
	{
		$confirmpage = $form->getConfirmpage();
		if ($confirmpage instanceof website_persistentdocument_page && $confirmpage->isPublished())
		{
			HttpController::getInstance()->redirectToUrl(LinkHelper::getDocumentUrl($confirmpage, $this->getLang(), array('formParam[id]' => $form->getId())));
			return website_BlockView::NONE;
		}
		
		$user = $this->getContext()->getGlobalContext()->getUser();
		$attr = 'form_success_parameters_' . $form->getId();
		$parameters = $user->getAttribute($attr);
		$user->removeAttribute($attr);
		
		$message = $form->getConfirmMessage();
		foreach ($parameters as $key => $value)
		{
			$message = str_replace('{' . $key . '}', htmlspecialchars($value), $message);
		}
		
		$request->setAttribute('message', $message);
		if ($form->getUseBackLink())
		{
			$request->setAttribute('back', array('url' => $parameters['backUrl'], 'label' => f_Locale::translate('&modules.form.frontoffice.Back;')));
		}
		else
		{
			$request->setAttribute('back', false);
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
	 * @return String
	 */
	protected function getInputView($form, $request)
	{
		$context = $this->getContext();
		FormHelper::addScriptsAndStyles($context);
		
		$previousModuleName = FormHelper::getModuleName();
		$moduleName = $this->getModuleName();
		FormHelper::setModuleName($moduleName);
		
		$contents = $this->getContentsFromRequest($form->getDocumentNode()->getChildren(), $request, $form);
		
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
			$backUrl = website_WebsiteModuleService::getInstance()->getCurrentWebsite()->getUrl();
		}
		$request->setAttribute('requestParameters', $request->getParameters());
		$request->setAttribute('backUrl', $backUrl);
		$request->setAttribute('useCaptcha', $form->getDocumentService()->hasToUseCaptcha($form));
		$request->setAttribute('jQueryConditionalElement', $form->getDocumentService()->getJQueryForConditionalElementsOf($form));
		
		return $this->getInputTemplateByFullName($form);
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
	 * @param array<TreeNode> $nodes
	 * @param block_BlockRequest $request
	 * @param form_persistentdocument_baseform $form
	 * @return array
	 */
	protected function getContentsFromRequest($nodes, $request, $form)
	{
		$contents = array();
		$markup = $form->getMarkup();
		foreach ($nodes as $node)
		{
			$document = $node->getPersistentDocument();
			if ($document instanceof form_persistentdocument_group)
			{
				$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$markup)->load('Form-Group');
				$elements = $this->getContentsFromRequest($node->getChildren(), $contents, $request, $markup);
				$attributes = array(
		    		'id' => $document->getId(),
		    		'label' => $document->getLabel(),
		    		'description' => $document->getDescription(),
		    		'elements' => $elements
				);
			}
			else
			{
				if ($document instanceof form_persistentdocument_field)
				{
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$markup)->load($document->getSurroundingTemplate());
					$html = FormHelper::fromFieldDocument($document, $request->hasParameter($document->getFieldName()) ? $request->getParameter($document->getFieldName()) : $document->getDefaultValue());
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
					$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->setDirectory('templates/markup/'.$markup)->load('Form-FreeContent');
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
		return $contents;
	}
}