<?php
/**
 * form_patch_0301
 * @package modules.form
 */
class form_patch_0301 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		if (!f_util_ClassUtils::methodExists('form_persistentdocument_form', 'getAcknowledgment'))
		{
			$this->log('compile-documents');
			$this->execChangeCommand('compile-documents');
		}
		
		// Add new properties to form and mail documents.
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/form.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'form');
		$newProp = $newModel->getPropertyByName('acknowledgment');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'form', $newProp);
		$newProp = $newModel->getPropertyByName('acknowledgmentNotification');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'form', $newProp);
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/mail.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'mail');
		$newProp = $newModel->getPropertyByName('acknowledgmentReceiver');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'mail', $newProp);
		
		// Create recipientGroupFolder and move all groups in it.
		$folder = form_RecipientGroupFolderService::getInstance()->getFolder();
		$folderId = $folder->getId();
		$rgs = form_RecipientGroupService::getInstance();
		foreach ($rgs->createQuery()->find() as $group)
		{
			$rgs->moveTo($group, $folderId);
		}
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
		return '0301';
	}
}