<?php
class form_ListActivationvaluesService extends BaseService
{
	/**
	 * @var form_ListActivationvaluesService
	 */
	private static $instance;
	
	/**
	 * @return form_ListActivationvaluesService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @return array<list_Item>
	 */
	public final function getItems()
	{
		try
		{
			$request = Controller::getInstance()->getContext()->getRequest();
			$questionId = intval($request->getParameter('questionId', 0));
			$question = DocumentHelper::getDocumentInstance($questionId);
		}
		catch (Exception $e)
		{
			if (Framework::isDebugEnabled())
			{
				Framework::debug(__METHOD__ . ' EXCEPTION: ' . $e->getMessage());
			}
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
				$trueLabel = $question->getVoTruelabel() . ' [' . $ls->transBO('m.uixul.bo.languages.' . $question->getLang(), array('ucf')) . ']';
				$falseLabel = $question->getVoFalselabel() . ' [' . $ls->transBO('m.uixul.bo.languages.' . $question->getLang(), array('ucf')) . ']';
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
	 * @return String
	 */
	public final function getDefaultId()
	{
		return null;
	}
}