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
		
		// Here we must use instanceof and not getDocumentModelName to work with injection.
		$results = array();
		if ($question instanceof form_persistentdocument_boolean)
		{
			$trueLabel = $question->getTruelabel();
			$falseLabel = $question->getFalselabel();
			
			$results[$trueLabel] = new list_Item($trueLabel,$trueLabel);
			$results[$falseLabel] = new list_Item($falseLabel,$falseLabel);
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