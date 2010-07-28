<?php
class form_FileService extends form_FieldService
{
	/**
	 * @var form_FileService
	 */
	private static $instance;

	/**
	 * @return form_FileService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}


	/**
	 * @return form_persistentdocument_file
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_form/file');
	}

	/**
	 * Create a query based on 'modules_form/file' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_form/file');
	}

	/**
	 * @param form_persistentdocument_file $field
	 * @param block_BlockRequest $request
	 * @param validation_Errors $errors
	 * @return void
	 */
	public function validate($field, $request, &$errors)
	{
		$errCount = $errors->count();
		$fileName = null;
		$fieldName = $field->getFieldName();
		if ($request instanceof block_BlockRequest)
		{
			$fileInfo = $request->getUploadedFileInformation($fieldName);
			if (isset($fileInfo['name']))
			{
				$fileName = $fileInfo['name'];
			}
		}
		else if ($request instanceof website_BlockActionRequest && $request->hasFile($fieldName))
		{
			$fileName = $request->getFile($fieldName)->getFilename();
		}

		if ($field->getRequired())
		{
			validation_ValidatorHelper::validate(new validation_Property($field->getLabel(), $fileName), 'blank:false', $errors);
		}
		
		if ($errors->count() == $errCount && f_util_StringUtils::isNotEmpty($fileName) && $field->getAllowedExtensions())
		{
			$ext = strtolower(f_util_FileUtils::getFileExtension($fileName));
			$allowedExt = explode(",", $field->getAllowedExtensions());
			if ( !empty($allowedExt) && ! in_array($ext, $allowedExt) )
			{
				$errors->append(f_Locale::translate('&modules.form.frontoffice.File-must-have-one-of-these-extensions;', array('file' => $field->getLabel(), 'extensions' => join(", ", $allowedExt))));
			}
		}
	}
	
    /**
     * @param form_persistentdocument_file $field
     * @param DOMElement $fieldElm
     * @param mixed $rawValue
     * @return string
     */
    public function buildXmlElementResponse($field, $fieldElm, $rawValue)
    {
    	if ($rawValue instanceof media_persistentdocument_file)
    	{
    		$rawValue->save(($field->getMediaFolder() !== null) ? $field->getMediaFolder()->getId() : null);
    		$media = media_MediaService::getInstance()->importFromTempFile($rawValue);
    		$media->save();
    		return $media->getId();
    	}
		if (f_util_ArrayUtils::isNotEmpty($rawValue) && $rawValue['error'] == 0 )
		{
			$media = MediaHelper::addUploadedFile($rawValue['name'], $rawValue['tmp_name'], $field->getMediaFolder());
			$mailValue = "<a href=\"".MediaHelper::getUrl($media)."\">".$media->getLabel()."</a>";
			$fieldElm->setAttribute('mailValue', $mailValue);
			return $media->getId();
		}
		return '';
    }	
}