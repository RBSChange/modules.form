<?php
class form_ListMarkupService extends BaseService implements list_ListItemsService
{
	/**
	 * @var form_ListMarkupService
	 */
	private static $instance;


	/**
	 * @return website_ListTemplatesService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}


	/**
	 * @return array
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
			while (($entry = $dir->read()))
			{
				if ($entry{0} != '.' && is_dir($pathWhereToFindMarkups.DIRECTORY_SEPARATOR.$entry))
				{
					$items[] = new list_Item(
						f_Locale::translateUI('&modules.form.bo.markup.'.$entry.';'),
						$entry
						);
				}
			}
		}

		return $items;
	}

}