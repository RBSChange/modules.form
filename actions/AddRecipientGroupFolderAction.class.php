<?php
/**
 * form_AddRecipientGroupFolderAction
 * @package modules.form.actions
 */
class form_AddRecipientGroupFolderAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$parentFolder = DocumentHelper::getDocumentInstance($request->getParameter('parentref'), 'modules_generic/folder');
		switch (get_class($parentFolder))
		{
			case 'generic_persistentdocument_folder':
			case 'generic_persistentdocument_rootfolder':
				$folder = form_RecipientGroupFolderService::getInstance()->getFolder($parentFolder->getId());
				$result['id'] = $folder->getId();
				break;
				
			default:
				return $this->sendJSONError(f_Locale::translateUI('&modules.form.bo.general.Cant-create-only-in-folder;', true));
		}

		return $this->sendJSON($result);
	}
}