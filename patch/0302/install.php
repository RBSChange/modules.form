<?php
/**
 * form_patch_0302
 * @package modules.form
 */
class form_patch_0302 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		// Update forms.
		$this->executeSQLQuery("DROP TABLE IF EXISTS `m_form_doc_baseform`");
		$this->executeSQLQuery("DROP TABLE IF EXISTS `m_form_doc_baseform_i18n`");
		$this->executeSQLQuery("RENAME TABLE `m_form_doc_form` TO `m_form_doc_baseform`;");
		$this->executeSQLQuery("RENAME TABLE `m_form_doc_form_i18n` TO `m_form_doc_baseform_i18n`;");
		
		$newPath = f_util_FileUtils::buildWebeditPath('modules/form/persistentdocument/baseform.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'form', 'baseform');
		$newProp = $newModel->getPropertyByName('saveResponse');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('form', 'baseform', $newProp);
		
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
		return '0302';
	}
}