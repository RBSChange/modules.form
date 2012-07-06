<?php
/**
 * @package modules.form
 * @method form_ListRecipientgrouplistService getInstance()
 */
class form_ListRecipientgrouplistService extends change_BaseService implements list_ListItemsService
{
	/**
	 * @var form_FormService
	 */
	private $parentForm;

	/**
	 * @param form_FormService $form
	 */
	public final function setParentForm($form)
	{
		$this->parentForm = $form;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		$items = array();
		if ($this->parentForm instanceof form_persistentdocument_form)
		{
			foreach ($this->parentForm->getRecipientGroupArray() as $recipientGroup)
			{
				$items[] = new list_Item(
					$recipientGroup->getLabel(),
					$recipientGroup->getId()
					);
			}
		}
		return $items;
	}
}