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

		$this->setTemplateName('Form-Success');

		$user = $context->getGlobalContext()->getUser();
		$attr = 'form_success_parameters_noconfirmpage_'.$form->getId();
		$parameters = $user->getAttribute($attr);
		$user->removeAttribute($attr);

		$message = $form->getConfirmMessageAsHtml();
		foreach ($parameters as $k => $v)
		{
			$message = str_replace('{'.$k.'}', htmlspecialchars($v), $message);
		}
		$this->setAttribute('message', $message);
		
		$this->setAttribute("receiverLabels", $this->getParameter("receiverLabels"));

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
