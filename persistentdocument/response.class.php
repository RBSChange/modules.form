<?php
class form_persistentdocument_response extends form_persistentdocument_responsebase implements form_Response
{
	/**
	 * Returns an associative array fieldname => value.
	 *
	 * @return array<fieldname => value>
	 */
	public function getData()
	{
		$data = array();
		$xml = new DOMDocument();
		$xml->loadXML($this->getContents());
		$xpath = new DOMXPath($xml);
		$fieldList = $xpath->query('/response/field');
		for ($i = 0; $i < $fieldList->length; $i++)
		{
			$item = $fieldList->item($i);
			if ($item->hasAttribute('mailValue'))
			{
				$value = $item->getAttribute('mailValue');
			}
			else
			{
				$value = $item->nodeValue;
			}
			$data[$fieldList->item($i)->getAttribute('name')] = $value;
		}
		return $data;
	}
	
	/**
	 * Returns an associative array with all the response data.
	 *
	 * @return array<fieldname => array<label=>fieldLabel, type=>fieldType, value=>fieldValue>>.
	 */
	public function getAllData()
	{
		$data = array();
		$xml = new DOMDocument();
		$xml->loadXML($this->getContents());
		$xpath = new DOMXPath($xml);
		$fieldList = $xpath->query('/response/field');
		for ($i = 0; $i < $fieldList->length; $i++)
		{
			$item = $fieldList->item($i);
			$fieladData = array(
				'label' => $item->getAttribute('label'),
				'type' => $item->getAttribute('type'),
				'value' => $item->nodeValue
			);
			if ($item->hasAttribute('level'))
			{
				$fieladData['level'] = $item->getAttribute('level');
			}
			if ($item->hasAttribute('groupName'))
			{
				$fieladData['groupName'] = $item->getAttribute('groupName');
			}
			if ($item->hasAttribute('isFile'))
			{
				$fieladData['isFile'] = $item->getAttribute('isFile');
			}
			if ($item->hasAttribute('mailValue'))
			{
				$fieladData['mailValue'] = $item->getAttribute('mailValue');
			}
			$data[$item->getAttribute('name')] = $fieladData;
		}
		return $data;
	}
	
	/**
	 * @return array
	 */
	public function getResponseInfos()
	{
		return $this->getDocumentService()->getResponseInfos($this);
	}
	
	/**
	 * @param string $key
	 * @return string | null
	 */
	public function getResponseFieldValue($key)
	{
		$data = $this->getAllData();
		if (isset($data[$key]) && isset($data[$key]['value']))
		{
			return $data[$key]['value'];
		}
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getBoEditorModule()
	{
		return 'form';
	}
}