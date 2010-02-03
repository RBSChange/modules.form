<?php
class form_BlockFormSuccessView extends block_BlockView
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 */
	public function execute($context, $request)
	{
		$form = $this->getParameter('form');

		$confirmpage = $form->getConfirmpage();
		if($confirmpage instanceof website_persistentdocument_page && $confirmpage->isPublished())
		{
			HttpController::getInstance()->redirectToUrl(LinkHelper::getUrl($confirmpage, $context->getLang(), array('formParam[id]'=>$form->getId())));
		}

		$this->setTemplateName('Form-Success');

		$user = $context->getGlobalContext()->getUser();
		$attr = 'form_success_parameters_'.$form->getId();
		$parameters = $user->getAttribute($attr);
		$user->removeAttribute($attr);

		$message = $form->getConfirmMessage();
		foreach ($parameters as $k => $v)
		{
			$message = str_replace('{'.$k.'}', htmlspecialchars($v), $message);
		}

		$this->setAttribute("receiverLabels", $this->getParameter("receiverLabels"));

		$this->setAttribute('message', $message);
		if ($form->getUseBackLink())
		{
			$this->setAttribute(
			'back',
			array(
			'url' => $parameters[form_FormConstants::BACK_URL_PARAMETER],
			'label' => f_Locale::translate('&modules.form.frontoffice.Back;')
			)
			);
		}
		else
		{
			$this->setAttribute('back', false);
		}

		$this->setAttribute('form', $form);
	}
}
