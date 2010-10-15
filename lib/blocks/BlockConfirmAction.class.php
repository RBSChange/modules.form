<?php
class form_BlockConfirmAction extends block_BlockAction
{
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String the view name
	 */
	public function execute($context, $request)
	{
		if (!$request->hasNonEmptyParameter('id'))
		{
			return block_BlockView::NONE;
		}
		$id = $request->getParameter('id');
		
		$form = DocumentHelper::getDocumentInstance($id);
		
		$user = $context->getGlobalContext()->getUser();
		$attr = 'form_success_parameters_confirmpage_' . $form->getId();
		$parameters = $user->getAttribute($attr);
		
		if ($parameters === null)
		{
			return block_BlockView::NONE;
		}
		
		$user->removeAttribute($attr);
		
		$message = $form->getConfirmMessage();
		foreach ($parameters as $k => $v)
		{
			$message = str_replace('{' . $k . '}', htmlspecialchars($v), $message);
		}
		
		$this->setParameter('message', $message);
		if ($form->getUseBackLink())
		{
			$this->setParameter('back', array(
				'url' => $parameters[form_FormConstants::BACK_URL_PARAMETER],
				'label' => f_Locale::translate('&modules.form.frontoffice.Back;')
			));
		}
		else
		{
			$this->setParameter('back', false);
		}
		
		$this->setParameter('form', $form);
		
		return block_BlockView::SUCCESS;
	}
	
	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 * @return String the view name
	 */
	public function executeBackoffice($context, $request)
	{
		return block_BlockView::NONE;
	}
}