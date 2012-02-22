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
		
		$results = array();
		if ($question instanceof form_persistentdocument_boolean)
		{
			$results['true'] = new list_Item($question->getTruelabel(), 'true');
			$results['false'] = new list_Item($question->getFalselabel(), 'false');
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