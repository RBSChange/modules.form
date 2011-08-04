<?php
class form_ExportCsvView extends change_View
{

	public function _execute($context, $request)
	{
		$form = $request->getAttribute('form');
		$this->setAttribute('form', $form);

		$domDoc = new DOMDocument();

		$fieldNames = array('creationdate' => f_Locale::translate('&modules.form.bo.actions.SendDate;'));
		
		$query = f_persistentdocument_PersistentProvider::getInstance()->createQuery('modules_form/response')
			->add(Restrictions::eq('parentForm.id', $form->getId()))	
			->addOrder(Order::desc('document_creationdate'));
			
		if ($request->getAttribute('all') != 'all')
		{
		   $query->add(Restrictions::published());
		}
		
		$responses = $query->find();
					
		$responsesAttribute = array();
		foreach ($responses as $response)
		{
			$domDoc->loadXML($response->getContents());
			$xpath = new DOMXPath($domDoc);
			$fieldList = $xpath->query('/response/field');
			$fields = array('creationdate' => $response->getUICreationdate());
			
			for ($i = 0 ; $i < $fieldList->length ; $i++)
			{
				$fieldNode  = $fieldList->item($i);
				$fieldName  = $fieldNode->getAttribute('name');
				$fieldLabel = $fieldNode->getAttribute('label');
				$fieldType  = $fieldNode->getAttribute('type');
				$fieldValue = $fieldNode->nodeValue;
				if ($fieldType == 'file')
				{
					$fieldValue = intval($fieldNode->nodeValue);
					if ($fieldValue > 0)
					{
						$fieldValue = MediaHelper::getUrl($fieldValue);
					}
					else
					{
						$fieldValue = '';
					}
				} 
				else if ($fieldType == 'list' && $fieldNode->hasAttribute('mailValue'))
				{
					$fieldValue = $fieldNode->getAttribute('mailValue');					
				}
				if ( ! isset($fieldNames[$fieldName]) )
				{
					$fieldNames[$fieldName] = $fieldLabel;
				}
				$fields[$fieldName] = $fieldValue;
			}
			$responsesAttribute[] = $fields;
		}

		$fileName = "export_formulaire_".f_util_FileUtils::cleanFilename($form->getLabel()).'_'.date('Ymd_His').'.csv';
		$options = new f_util_CSVUtils_export_options();
		$options->separator = ";";
		
		$csv = f_util_CSVUtils::export($fieldNames, $responsesAttribute, $options);		
		header("Content-type: text/comma-separated-values");
		header('Content-length: '.strlen($csv));
		header('Content-disposition: attachment; filename="'.$fileName.'"');
		echo $csv;
		exit;
	}
}