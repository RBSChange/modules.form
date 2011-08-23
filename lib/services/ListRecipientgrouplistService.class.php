<?php
class form_ListRecipientgrouplistService extends BaseService implements list_ListItemsService
{
	/**
	 * @var form_ListRecipientgrouplistService
	 */
	private static $instance;

	/**
	 * @var form_FormService
	 */
	private $parentForm;

	/**
	 * @return form_ListRecipientgrouplistService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

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