<?php
class form_ListMarkupService extends change_BaseService implements list_ListItemsService
{
	/**
	 * @var form_ListMarkupService
	 */
	private static $instance;

	/**
	 * @return form_ListMarkupService
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
	 * @return list_Item[]
	 */
	public function getItems()
	{
		$items = array();

		$pathWhereToFindMarkupsArray = FileResolver::getInstance()
			->setPackageName('modules_form')
			->setDirectory('templates/markup')
			->getPaths('');

		foreach ($pathWhereToFindMarkupsArray as $pathWhereToFindMarkups)
		{
			$dir = dir($pathWhereToFindMarkups);
			while ($entry = $dir->read())
			{
				if ($entry{0} != '.' && is_dir($pathWhereToFindMarkups.DIRECTORY_SEPARATOR.$entry))
				{
					$items[] = $this->getItemByValue($entry);
				}
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
		return new list_Item(LocaleService::getInstance()->transBO('m.form.bo.markup.'.strtolower($value)), $value);
	}
}