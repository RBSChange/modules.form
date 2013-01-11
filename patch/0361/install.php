<?php
/**
 * form_patch_0361
 * @package modules.form
 */
class form_patch_0361 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->executeLocalXmlScript('lists.xml');
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/date.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'date');
		$newProp = $newModel->getPropertyByName('floatingStartDate');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'date', $newProp);
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/date.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'date');
		$newProp = $newModel->getPropertyByName('floatingEndDate');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'date', $newProp);
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/date.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'date');
		$newProp = $newModel->getPropertyByName('rangeType');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'date', $newProp);
		
		foreach (form_DateService::getInstance()->createQuery()->add(Restrictions::isNull('rangeType'))->find() as $field)
		{
			$field->setRangeType('fixed');
			$field->save();
		}
	}
}