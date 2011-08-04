<?php
class form_CaptchaAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		change_Controller::setNoCache();
		
		$generator = form_CaptchaGenerator::getInstance();
		$generator->setKey($request->getParameter('key'));
		$renew = $request->hasParameter('renew');
		
		// Set optionnal parameters.
		if ($request->hasParameter('ml'))
		{
			$generator->setCodeMaxLength(intval($request->getParameter('ml')));
			if ($renew)
			{
				$generator->setCodeMinLength(intval($request->getParameter('ml')));
			}
		}
		if ($request->hasParameter('iw'))
		{
			$generator->setWidth(intval($request->getParameter('iw')));
		}
		if ($request->hasParameter('ih'))
		{
			$generator->setHeight(intval($request->getParameter('ih')));
		}
		if ($request->hasParameter('fs'))
		{
			$generator->setFontSize(intval($request->getParameter('fs')));
		}
		if ($request->hasParameter('fd'))
		{
			$generator->setFontDepth(intval($request->getParameter('fd')));
		}
		
		// Renders the image.
		if ($renew)
		{
			$generator->generateCode();
		}
		$generator->render();
		return change_View::NONE;
	}

	/**
	 * @return boolean
	 */
	public function isSecure()
	{
		return false;
	}
}