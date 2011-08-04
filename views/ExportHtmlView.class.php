<?php
class form_ExportHtmlView extends change_View
{
	public function _execute($context, $request)
	{
		$this->setTemplateName('Form-Responses', K::HTML);

		$form = $request->getAttribute('form');
		$this->setAttribute('form', $form);

		$query = form_ResponseService::getInstance()->createQuery()
			->add(Restrictions::eq('parentForm', $form))->addOrder(Order::desc('document_creationdate'));			
		if ($request->getAttribute('all') != 'all')
		{
		   $query->add(Restrictions::published());
		}		
		$responses = $query->find();			
		$this->setAttribute('responsesCount', count($responses));
		
		$responsesInfos = array();
		foreach ($responses as $response)
		{
			$responsesInfos[] = $response->getResponseInfos();
		}
		$this->setAttribute('responses', $responsesInfos);
	}
}