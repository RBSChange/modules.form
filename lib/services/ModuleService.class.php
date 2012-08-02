<?php
/**
 * @package modules.form.lib.services
 */
class form_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var form_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return form_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
}