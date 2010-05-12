<?php
/**
 * form_patch_0303
 * @package modules.form
 */
class form_patch_0303 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->executeLocalXmlScript('newlist.xml');
	}

	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'form';
	}

	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0303';
	}
}