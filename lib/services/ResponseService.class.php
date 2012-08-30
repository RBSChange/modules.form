<?php
/**
 * @package modules.form
 * @method form_ResponseService getInstance()
 */
class form_ResponseService extends f_persistentdocument_DocumentService
{
	/**
	 * @return form_persistentdocument_response
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/response');
	}

	/**
	 * Create a query based on 'modules_form/response' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_form/response');
	}

	/**
	 * Replaces all the {fieldName} occurences in $string by the value of the
	 * corresponding field into the given $response.
	 *
	 * @param form_persistentdocument_response $response
	 * @param string $string
	 * @return string
	 */
	public function replaceFieldsValue($response, $string)
	{
		$matches = null;
		$responseData = $response->getData();
		if (preg_match_all('/\{('.substr(form_FieldService::FIELD_NAME_REGEXP, 1, -1).')\}/', $string, $matches))
		{
			foreach ($matches[1] as $index => $possibleFieldName)
			{
				if (isset($responseData[$possibleFieldName]))
				{
					$string = str_replace($matches[0][$index], $responseData[$possibleFieldName], $string);
				}
			}
		}
		return $string;
	}

	/**
	 * @param form_persistentdocument_response $document
	 * @return void
	 */
	protected function postDelete($document)
	{
		$form = $document->getParentForm();
		$form->setResponseCount($form->getResponseCount()-1);
		$form->save();
	}
	
	/**
	 * @param form_persistentdocument_form $form
	 * @return integer
	 */
	public function fileForForm($form)
	{
		$count = 0;
		$responses = $this->createQuery()
						->add(Restrictions::eq('parentForm.id', $form->getId()))
						->add(Restrictions::published())
						->find();
		foreach ($responses as $response) 
		{
			$this->file($response->getId());
			$count++;
		}
	
		return $count;
	}
	
	/**
	 * @param form_persistentdocument_response $response
	 * @return array
	 */
	public function getResponseInfos($response)
	{
		$domDoc = new DOMDocument();
		$domDoc->loadXML($response->getContents());
		$xpath = new DOMXPath($domDoc);
		$fieldList = $xpath->query('/response/field');
		$i = 0;
		$contents = $this->getResponseContents($i, $fieldList, 0, null);
		$formattedDate = date_Formatter::toDefaultDateTimeBO($response->getUICreationdate());
		return array('date' => $response->getUICreationdate(), 'formattedDate' => $formattedDate, 'contents' => $contents);
	}

	/**
	 * @param integer $i
	 * @param DOMNodeList $fieldList
	 * @param integer $level
	 * @param string $groupName
	 * @return array
	 */
	private function getResponseContents(&$i, $fieldList, $level, $groupName)
	{
		$contents = array();
		while ($i < $fieldList->length)
		{
			$node = $fieldList->item($i);
			$nodeLevel = ($node->hasAttribute('level')) ? $node->getAttribute('level') : 0;
			$nodeGroupName = ($node->hasAttribute('groupName')) ? $node->getAttribute('groupName') : null;
			if ($nodeLevel > $level)
			{
				$contents[] = array(
					'isGroup' => true, 
					'label' => $nodeGroupName, 
					'contents' => $this->getResponseContents($i, $fieldList, $nodeLevel, $nodeGroupName)
				);
			}
			else if ($groupName != $nodeGroupName)
			{
				return $contents;
			}
			else 
			{
				$i++;
				$contents[] = $this->getFieldInfos($node);
			}
		}
		return $contents;
	}
	
	/**
	 * @param DOMNode $node
	 * @return array
	 */
	private function getFieldInfos($node)
	{
		$value = htmlspecialchars($node->nodeValue);
		if ($node->getAttribute('type') == 'date')
		{
			$mailValue = date_Formatter::toDefaultDate($value);
		}
		else
		{
			$mailValue = ($node->hasAttribute('mailValue')) ? $node->getAttribute('mailValue') : $value;
		}
		$infos = array(
			'isGroup' => false,
			'label' => $node->getAttribute('label'),
			'mailValue' => $mailValue,
			'value' => $value
		);
		if ($value && $node->hasAttribute('isFile') && $node->getAttribute('isFile') == 'true')
		{
			try 
			{
				$file = DocumentHelper::getDocumentInstance($value);
				$infos['isFile'] = true;
				$infos['href'] = LinkHelper::getUIActionLink('media', 'BoDisplay')->setQueryParameter('cmpref', $value)
					->setQueryParameter('lang', $file->getI18nInfo()->getVo())->setQueryParameter('forceDownload', 'true')->getUrl();
				$infos['linklabel'] = $file->getLabel();
			}
			catch (Exception $e)
			{
				$e; // Avoid Eclipse warning...
				$infos['mailValue'] = LocaleService::getInstance()->trans('m.form.bo.general.unexisting-file' /* @TODO CHECK */, array('ucf'), array('id' => $value));
			}
		}
		return $infos;
	}
	
	/**
	 * @param form_persistentdocument_response $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		
		$resume['responsedata'] = $document->getResponseInfos();
		
		return $resume;
	}
}