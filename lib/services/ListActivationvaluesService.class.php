<?php
/**
 * @package modules.form
 * @method form_ListActivationvaluesService getInstance()
 */
class form_ListActivationvaluesService extends change_BaseService implements list_ListItemsService
{
	/**
	 * @return list_Item[]
	 */
	public final function getItems()
	{
		try
		{
			$request = change_Controller::getInstance()->getContext()->getRequest();
			$questionId = intval($request->getParameter('questionId', 0));
			$question = DocumentHelper::getDocumentInstance($questionId);
		}
		catch (Exception $e)
		{
			return array();
		}
		
		$ls = LocaleService::getInstance();
		$results = array();
		if ($question instanceof form_persistentdocument_boolean)
		{
			if ($question->isContextLangAvailable())
			{
				$trueLabel = $question->getTruelabel();
				$falseLabel = $question->getFalselabel();
			}
			else
			{
				$trueLabel = $question->getVoTruelabel() . ' [' . $ls->trans('m.uixul.bo.languages.' . $question->getLang(), array('ucf')) . ']';
				$falseLabel = $question->getVoFalselabel() . ' [' . $ls->trans('m.uixul.bo.languages.' . $question->getLang(), array('ucf')) . ']';
			}
			
			$results['true'] = new list_Item($trueLabel, 'true');
			$results['false'] = new list_Item($falseLabel, 'false');
		}
		else if ($question instanceof form_persistentdocument_list)
		{
			$results = $question->getDataSource()->getItems();
		}
		
		return $results;
	}
	
	/**
	 * @return string
	 */
	public final function getDefaultId()
	{
		return null;
	}
}