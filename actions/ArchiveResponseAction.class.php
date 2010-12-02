<?php
class form_ArchiveResponseAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$forms = $this->getDocumentInstanceArrayFromRequest($request);
		$archivedCount = 0;
		foreach ($forms as $form) 
		{
			$archivedCount += form_FormService::getInstance()->fileResponses($form);
		}
        return $this->sendJSON(array('message' => 
        	LocaleService::getInstance()->transBO('m.form.bo.actions.archiveresponse-success', 
        		array(), array('ArchivedCount' => $archivedCount))));
	}
}