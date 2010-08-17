<?php
/**
 * form_patch_0305
 * @package modules.form
 */
class form_patch_0305 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/preferences.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'preferences');
		$newProp = $newModel->getPropertyByName('enableRecipientGroupFolderCreation');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'preferences', $newProp);	
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
		return '0305';
	}
}