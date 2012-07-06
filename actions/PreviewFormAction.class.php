<?php
class form_PreviewFormAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$form = $this->getDocumentInstanceFromRequest($request);
		$preference = $this->getPreferences();
		if ($preference !== null && $preference->getPreviewpage() !== null)
		{
			$pageId = $preference->getPreviewpage()->getId();
			$request->setParameter('pageref', $pageId);
			
			$formParam = $request->getParameter('formParam', array());
			$formParam['cmpref'] = $form->getId();
			
			$request->setParameter('formParam', $formParam);
			$request->setParameter('DisablePublicationWorkflow', 'true');
			$context->getController()->forward('website', 'Display');
			
			return change_View::NONE;
		}
		return change_View::ERROR;
	}
	
	/**
	 * @return form_persistentdocument_preferences
	 */
	private function getPreferences()
	{
		return ModuleService::getInstance()->getPreferencesDocument('form');
	}
}