<?php
class form_ArchiveResponseAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
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
			LocaleService::getInstance()->trans('m.form.bo.actions.archiveresponse-success', 
				array(), array('ArchivedCount' => $archivedCount))));
	}
}