<?php
class form_PreviewFormAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$form = $this->getDocumentInstanceFromRequest($request);
		$preference = $this->getPreferences();
		if ($preference !== null && $preference->getPreviewpage() !== null)
		{
			$pageId = $preference->getPreviewpage()->getId();
			$request->setParameter(K::PAGE_REF_ACCESSOR, $pageId);
			
			$formParam = $request->getParameter('formParam', array());
			$formParam['cmpref'] = $form->getId();
			
			$request->setParameter('formParam', $formParam);
			$request->setParameter('DisablePublicationWorkflow', 'true');
			$context->getController()->forward('website', 'Display');
			
    	    return View::NONE;
		}
		return View::ERROR;
	}
	
	/**
	 * @return form_persistentdocument_preferences
	 */
	private function getPreferences()
	{
		return ModuleService::getInstance()->getPreferencesDocument('form');
	}
}