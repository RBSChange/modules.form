<?php
/**
 * @package modules.form
 * @method form_ListMarkupService getInstance()
 */
class form_ListMarkupService extends change_BaseService implements list_ListItemsService
{
	/**
	 * @return list_Item[]
	 */
	public function getItems()
	{
		$items = array();

		$pathWhereToFindMarkupsArray = change_FileResolver::getNewInstance()->getPaths('modules', 'form', 'templates', 'markup');
		foreach ($pathWhereToFindMarkupsArray as $pathWhereToFindMarkups)
		{
			$dir = dir($pathWhereToFindMarkups);
			$entry = $dir->read();
			while ($entry)
			{
				if ($entry{0} != '.' && is_dir($pathWhereToFindMarkups.DIRECTORY_SEPARATOR.$entry))
				{
					$items[] = $this->getItemByValue($entry);
				}
				$entry = $dir->read();
			}
		}

		return $items;
	}
	
	/**
	 * @see list_persistentdocument_dynamiclist::getItemByValue()
	 * @param string $value;
	 * @return list_Item
	 */
	public function getItemByValue($value)
	{
		return new list_Item(LocaleService::getInstance()->trans('m.form.bo.markup.'.strtolower($value)), $value);
	}
}