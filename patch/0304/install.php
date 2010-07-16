<?php
/**
 * form_patch_0304
 * @package modules.form
 */
class form_patch_0304 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/text.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'text');
		$newProp = $newModel->getPropertyByName('initializeWithCurrentUserLastname');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'text', $newProp);
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/text.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'text');
		$newProp = $newModel->getPropertyByName('initializeWithCurrentUserFirstname');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'text', $newProp);
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
		return '0304';
	}
}