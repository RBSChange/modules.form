<?php
class form_ArchiveResponseAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$forms = $this->getDocumentInstanceArrayFromRequest($request);
		$responseArchivedCount = 0;
		foreach ($forms as $form) 
		{
			$responseArchivedCount += form_FormService::getInstance()->fileResponses($form);
		}
		if (Framework::isInfoEnabled())
		{
		    Framework::info('form/ArchiveResponse -> Filed responses : ' . $responseArchivedCount);
		}
        $request->setAttribute('message', $responseArchivedCount);
        return self::getSuccessView();
	}
}