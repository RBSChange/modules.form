<?php
/**
 * form_CheckModuleInitAction
 * @package modules.form.actions
 */
class form_CheckModuleInitAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();
		
		if (ModuleService::getInstance()->getPreferenceValue('form', 'enableRecipientGroupFolderCreation'))
		{
			$result['enableRecipientGroupFolderCreation'] = 'true';
		}
		else
		{
			$result['enableRecipientGroupFolderCreation'] = 'false';
			$result['recipientGroupFolderId'] = form_RecipientGroupFolderService::getInstance()->getFolder()->getId();
		}
		
		return $this->sendJSON($result);
	}
}