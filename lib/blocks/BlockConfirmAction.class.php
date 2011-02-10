<?php
class form_BlockConfirmAction extends website_BlockAction
{
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackoffice() || !$request->hasNonEmptyParameter('id'))
		{
			return website_BlockView::NONE;
		}

		$form = form_persistentdocument_form::getInstanceById($request->getParameter('id'));
		$request->setAttribute('form', $form);
		
		$user = Controller::getInstance()->getContext()->getUser();
		$attr = 'form_success_parameters_confirmpage_' . $form->getId();
		$parameters = $user->getAttribute($attr);		
		if ($parameters === null)
		{
			return website_BlockView::NONE;
		}		
		$user->removeAttribute($attr);
		
		$message = $form->getConfirmMessageAsHtml();
		foreach ($parameters as $k => $v)
		{
			$message = str_replace('{' . $k . '}', htmlspecialchars($v), $message);
		}
		$request->setAttribute('message', $message);
		
		if ($form->getUseBackLink())
		{
			$request->setAttribute('back', array(
				'url' => $parameters['backUrl'],
				'label' => LocaleService::getInstance()->transFO('m.form.frontoffice.back', array('ucf'))
			));
		}
		
		return website_BlockView::SUCCESS;
	}
}