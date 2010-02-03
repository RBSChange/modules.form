<?php
class form_ExportHtmlView extends f_view_BaseView
{

	public function _execute($context, $request)
	{
		$this->setTemplateName('Form-Responses', K::HTML);

		$form = $request->getAttribute('form');
		$this->setAttribute('form', $form);

		$domDoc = new DOMDocument();

		$query = f_persistentdocument_PersistentProvider::getInstance()->createQuery('modules_form/response')
			->add(Restrictions::eq('parentForm.id', $form->getId()))	
			->addOrder(Order::desc('document_creationdate'));
			
		if ($request->getAttribute('all') != 'all')
		{
		   $query->add(Restrictions::published());
		}
		
		$responses = $query->find();
			
		$this->setAttribute('responsesCount', count($responses));
		
		$responsesAttribute = array();
		foreach ($responses as $response)
		{
			$r['date'] = $response->getCreationdate();

			$domDoc->loadXML($response->getContents());
			$xpath = new DOMXPath($domDoc);
			$fieldList = $xpath->query('/response/field');
			$fields = array();
			for ($i = 0 ; $i < $fieldList->length ; $i++)
			{
				$fieldNode = $fieldList->item($i);
				$fieldType  = $fieldNode->getAttribute('type');
				$fieldValue = $fieldNode->nodeValue;
				if ($fieldNode->hasAttribute('mailValue'))
				{
				    $fieldValue = $fieldNode->getAttribute('mailValue');
				}
				else
				{
					$fieldValue = htmlspecialchars($fieldValue);
				}
				$fields[$fieldNode->getAttribute('name')] = array(
					'label' => $fieldNode->getAttribute('label'),
					'value' => $fieldValue
					);
			}
			$r['fields'] = $fields;

			$responsesAttribute[] = $r;
		}

		$this->setAttribute('responses', $responsesAttribute);
	}
}