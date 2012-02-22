<?php
/**
 * form_patch_0350
 * @package modules.form
 */
class form_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		foreach (form_FieldService::getInstance()->createQuery()->add(Restrictions::isNotNull('activationQuestion'))->find() as $doc)
		{
			$this->fixActivationValue($doc);
		}
		foreach (form_FreecontentService::getInstance()->createQuery()->add(Restrictions::isNotNull('activationQuestion'))->find() as $doc)
		{
			$this->fixActivationValue($doc);
		}
		foreach (form_GroupService::getInstance()->createQuery()->add(Restrictions::isNotNull('activationQuestion'))->find() as $doc)
		{
			$this->fixActivationValue($doc);
		}
	}
	
	/**
	 * @param form_persistentdocument_field|form_persistentdocument_group|form_persistentdocument_freecontent $doc
	 */
	private function fixActivationValue($doc)
	{
		$activationQuestion = $doc->getActivationQuestion();
		if ($activationQuestion instanceof form_persistentdocument_boolean)
		{
			$activationValue = $doc->getActivationValue();
			if ($activationValue == $activationQuestion->getTrueLabel())
			{
				$doc->setActivationValue('true');
				$doc->save();
			}
			elseif ($activationValue == $activationQuestion->getFalseLabel())
			{
				$doc->setActivationValue('false');
				$doc->save();
			}
		}
	}
}