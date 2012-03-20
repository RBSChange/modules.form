<?php
/**
 * form_patch_0360
 * @package modules.form
 */
class form_patch_0360 extends patch_BasePatch
{

	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$list = list_ListService::getInstance()->getByListId('modules_form/messagesendingtype');
		if ($list)
		{
			$list->delete();
		}
		
		$query = "ALTER TABLE `m_form_doc_baseform` DROP `messagesendingtype`";
		$this->executeSQLQuery($query);
	}
}