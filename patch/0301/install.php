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
		// Update forms.		
		$this->executeSQLQuery("create table m_form_doc_baseform  select * from m_form_doc_form");
		$this->executeSQLQuery("create table m_form_doc_baseform_i18n  select * from m_form_doc_form_i18n");
		
		$this->executeSQLQuery("RENAME TABLE `m_form_doc_form` TO `m_form_doc_form_todelete`;");
		$this->executeSQLQuery("RENAME TABLE `m_form_doc_form_i18n` TO `m_form_doc_form_i18n_todelete`;");

		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/mail.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'mail');
		$newProp = $newModel->getPropertyByName('acknowledgmentReceiver');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'mail', $newProp);
		

		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/form.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'form');
		$newProp = $newModel->getPropertyByName('saveResponse');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'baseform', $newProp);
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/baseform.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'baseform');
		$newProp = $newModel->getPropertyByName('acknowledgment');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'baseform', $newProp);
		

		$newProp = $newModel->getPropertyByName('acknowledgmentNotification');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'baseform', $newProp);
		
		$this->execChangeCommand('compile-documents');
		
		foreach (form_FormService::getInstance()->createQuery()->find() as $form)
		{
			$form->setSaveResponse(true);
			$form->save();
		}
		
		// Update mail fields.
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/mail.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'mail');
		$newProp = $newModel->getPropertyByName('initializeWithCurrentUserEmail');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'mail', $newProp);
		
		// Update text fields.
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/text.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'text');
		$newProp = $newModel->getPropertyByName('disableAutocorrect');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'text', $newProp);
		
		$newProp = $newModel->getPropertyByName('disableAutocapitalize');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'text', $newProp);
		

		$newProp = $newModel->getPropertyByName('disableAutocomplete');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'text', $newProp);
		
		foreach (form_MailService::getInstance()->createQuery()->find() as $field)
		{
			$field->setDisableAutocorrect(true);
			$field->setDisableAutocapitalize(true);
			$field->save();
		}
		
		// Republish all forms.
		$bfs = form_BaseformService::getInstance();
		foreach ($bfs->createQuery()->find() as $form)
		{
			$bfs->publishIfPossible($form->getId());
		}
		

		// Create recipientGroupFolder and move all groups in it.
		$folder = form_RecipientGroupFolderService::getInstance()->getFolder();
		$folderId = $folder->getId();
		$rgs = form_RecipientGroupService::getInstance();
		foreach ($rgs->createQuery()->find() as $group)
		{
			$rgs->moveTo($group, $folderId);
		}
		
		$this->executeLocalXmlScript('newlist.xml');
		
			$this->log('If all went OK, you can delete m_form_doc_form_todelete and m_form_doc_form_i18n_todelete tables!');
		
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