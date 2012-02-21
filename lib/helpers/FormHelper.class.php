<?php
abstract class FormHelper
{
	const DISPLAY_LIST     = 'list';
	const DISPLAY_RADIO    = 'radio';
	const DISPLAY_CHECKBOX = 'checkbox';
	const DISPLAY_BUTTONS  = 'buttons';

	/**
	 * @var string
	 */
	private static $moduleName = 'form';
	
	/**
	 * @param string $moduleName
	 */
	public static function setModuleName($moduleName = 'form')
	{
		if ($moduleName !== null)
		{
			self::$moduleName = $moduleName;
		}
		else
		{
			self::$moduleName = 'form';
		}
	}
	
	/**
	 * @return string
	 */
	public static function getModuleName()
	{
		return self::$moduleName;
	}
	
	/**
	 * Generate a <input type="text"/> element.
	 *
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function textBox($name, $id, $value, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		if (!isset($attributes['size']))
		{
			$attributes['size'] = 20;
		}
		return self::field('Text', $attributes);
	}

	/**
	 * Generate a <input type="hidden"/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @return string
	 */
	public static function hiddenBox($name, $id, $value)
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		return self::field('Hidden', $attributes);
	}

	/**
	 * Generate a <input type="password"/> element.
	 * @param string $name
	 * @param string $id
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function passwordBox($name, $id, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		if (!isset($attributes['size']))
		{
			$attributes['size'] = 20;
		}
		return self::field('Password', $attributes);
	}

	/**
	 * Generate a <textarea/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function multilineTextBox($name, $id, $value, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		if (!isset($attributes['cols']))
		{
			$attributes['cols'] = 50;
		}
		if (!isset($attributes['rows']))
		{
			$attributes['rows'] = 3;
		}
		return self::field('MultilineText', $attributes);
	}

	/**
	 * Generate a <select/> element, with multiple selection enabled.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $items
	 * @param boolean $multiple
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function listBox($name, $id, $value, $items, $attributes = array())
	{
		if ( ! is_array($value) )
		{
			$value = array($value);
		}
		return self::listField($name, $id, $value, $items, self::SELECTION_MULTIPLE, $attributes);
	}

	/**
	 * Generate a <select/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $items
	 * @param boolean $multiple
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function comboBox($name, $id, $value, $items, $attributes = array())
	{
		if ( is_array($value) )
		{
			$value = $value[0];
		}
		return self::listField($name, $id, $value, $items, self::SELECTION_SINGLE, $attributes);
	}

	/**
	 * Generate a <input type="checkbox"/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param boolean $checked
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function checkBox($name, $id, $value, $checked = false, $attributes = array())
	{
		$attributes['name']    = $name;
		$attributes['id']      = $id;
		$attributes['value']   = $value ? $value : '1';
		$attributes['checked'] = $checked;
		return self::field('Checkbox', $attributes);
	}

	/**
	 * Generate a <input type="file"/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function uploadFileBox($name, $id, $value, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		if (!isset($attributes['size']))
		{
			$attributes['size'] = 20;
		}
		return self::field('File', $attributes);
	}

	/**
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $attributes
	 * @return string
	 */
	public static function dateBox($name, $id, $value, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		$attributes['size' ] = 10;
		$attributes['class'] = 'date-picker';
		return self::field('Date', $attributes);
	}

	/**
	 * @param form_persistentdocument_field $field
	 * @param string $value
	 */
	public static function fromFieldDocument($field, $value = '')
	{
		if ($field instanceof form_persistentdocument_field)
		{
			$type = ucfirst(substr(get_class($field), strlen('form_persistentdocument_')));
			$methodName = 'from'.$type.'FieldDocument';
			if (f_util_ClassUtils::methodExists(get_class(), $methodName))
			{
				return self::$methodName($field, $value);
			}
		}
		return "Unknown field type: ".get_class($field, $value);
	}

	/**
	 * Builds an associative array ready to be given to a URL builder from the
	 * given associative array $data "fieldName" => "fieldValue".
	 * @param array<fieldName=>fieldValue> $data
	 * @return array<"formParam"=>array<fieldName=>fieldValue>>
	 */
	public static function getRequestParametersForFields($data)
	{
		return array('formParam' => $data);
	}

	// PRIVATE METHODS /////////////////////////////////////////////////////////

	/**
	 * Generate a field element from the provided template and attributes.
	 * @param string $template
	 * @param array<string,string> $attributes
	 * @return string
	 */
	private static function field($template, $attributes)
	{
		$templateObject = TemplateLoader::getInstance()->setPackageName('modules_form')->load('Form-Field-' . $template);
    	$templateObject->setAttribute('field', $attributes);
    	$templateObject->setAttribute('moduleName', self::getModuleName());
    	return $templateObject->execute();
	}

	const SELECTION_SINGLE            = 's';
	const SELECTION_MULTIPLE          = 'm';
	const SELECTION_SINGLE_RADIO      = 'r';
	const SELECTION_MULTIPLE_CHECKBOX = 'c';

	/**
	 * Generate a <select/> element.
	 * @param string $name
	 * @param string $id
	 * @param string $value
	 * @param array<string,string> $items
	 * @param string $type self::SELECTION_SINGLE, self::SELECTION_MULTIPLE, self::SELECTION_SIGNLE_RADIO
	 * @param array<string,string> $attributes
	 * @return string
	 */
	private static function listField($name, $id, $value, $items, $type = self::SELECTION_SINGLE, $attributes = array())
	{
		$attributes['name']  = $name;
		$attributes['id']    = $id;
		$attributes['value'] = $value;
		$options = array();
		foreach ($items as $itemValue => $itemLabel)
		{
			$option = array('value' => $itemValue, 'label' => $itemLabel);
			if ($value == $itemValue || (is_array($value) && in_array($itemValue, $value)))
			{
				$option['selected'] = true;
			}
			$options[] = $option;
		}
		$attributes['items'] = $options;
		if ($type == self::SELECTION_MULTIPLE)
		{
			$template = 'SelectMultiple';
		}
		else if ($type == self::SELECTION_MULTIPLE_CHECKBOX)
		{
			$template = 'CheckboxMultiple';
		}
		else if ($type == self::SELECTION_SINGLE_RADIO)
		{
			$template = 'Radio';
		}
		else
		{
			$template = 'Select';
		}
		return self::field($template, $attributes);
	}

	/**
	 * @param form_persistentdocument_text $field
	 * @param string $value
	 * @return string
	 */
	private static function fromTextFieldDocument($field, $value)
	{
		return self::returnTextFieldHtml($field, $value);
	}

	/**
	 * @param form_persistentdocument_mail $field
	 * @param string $value
	 * @return string
	 */
	private static function fromMailFieldDocument($field, $value)
	{
		return self::returnTextFieldHtml($field, $value);
	}

	/**
	 * @param form_persistentdocument_mail $field
	 * @param string $value
	 * @return string
	 */
	private static function returnTextFieldHtml($field, $value)
	{
		// build required attributes
		$attributes = array();
		$attributes['maxlength'] = $field->getMaxlength();
		$attributes['value'] = $value;
		$attributes['title'] = $field->getHelpTextAsHtml();
		if ($field->getMultiline())
		{
			$attributes['cols'] = $field->getCols();
			$attributes['rows'] = $field->getRows();
			return self::multilineTextBox($field->getFieldName(), $field->getId(), $value, $attributes);
		}
		else
		{
			$attributes['size'] = $field->getCols();
			$attributes['autocorrect'] = $field->getDisableAutocorrect() ? 'off' : 'on';
			$attributes['autocapitalize'] = $field->getDisableAutocapitalize() ? 'off' : 'on';
			$attributes['autocomplete'] = $field->getDisableAutocomplete() ? 'off' : 'on';
			return self::textBox($field->getFieldName(), $field->getId(), $value, $attributes);
		}
	}

	/**
	 * @param form_persistentdocument_list $field
	 * @param string $value
	 * @return string
	 */
	private static function fromListFieldDocument($field, $value)
	{
		// build required attributes
		$attributes = array();
		$attributes['value'] = $value;
		$attributes['title'] = $field->getHelpTextAsHtml();
Framework::fatal(__METHOD__ . ' ' . $attributes['title']);
		// build items
		$listObject = $field->getDataSource();
		$items = array();
		if ( ! is_null($listObject) )
		{
			$itemObjects = $listObject->getItems();
			foreach ($itemObjects as $name => $itemObject)
			{
				$name = $itemObject->getValue();
				$items[$name] = $itemObject->getLabel();
			}
		}
		if ($field->getMultiple())
		{
			if ($field->getDisplay() == self::DISPLAY_BUTTONS)
			{
				return self::listField(
					$field->getFieldName(),
					$field->getId(),
					$value,
					$items,
					self::SELECTION_MULTIPLE_CHECKBOX,
					$attributes
					);
			}
			else
			{
				if ($field->getHasBlankOption())
				{
					$items = array_reverse($items, true);
					$items[''] = f_Locale::translate("&modules.form.frontoffice.list.BlankOption;");
					$items = array_reverse($items, true);
				}
				return self::listBox($field->getFieldName(), $field->getId(), $value, $items, $attributes);
			}
		}
		else
		{
			if ($field->getDisplay() == self::DISPLAY_BUTTONS)
			{
				return self::listField(
					$field->getFieldName(),
					$field->getId(),
					$value,
					$items,
					self::SELECTION_SINGLE_RADIO,
					$attributes
					);
			}
			else
			{
				if ($field->getHasBlankOption())
				{
					$items = array_reverse($items, true);
					$items[''] = f_Locale::translate("&modules.form.frontoffice.list.BlankOption;");
					$items = array_reverse($items, true);
				}
				return self::comboBox($field->getFieldName(), $field->getId(), $value, $items, $attributes);
			}
		}
	}

	/**
	 * @param form_persistentdocument_recipientGroupList $field
	 * @param string $value
	 * @return string
	 */
	private static function fromRecipientGroupListFieldDocument($field, $value)
	{
		form_ListRecipientgrouplistService::getInstance()->setParentForm(form_FieldService::getInstance()->getFormOf($field));
		return self::fromListFieldDocument($field, $value);
	}

	/**
	 * @param form_persistentdocument_password $field
	 * @param string $value
	 * @return string
	 */
	private static function fromPasswordFieldDocument($field, $value)
	{
		// build required attributes
		$attributes = array();
		$attributes['title'] = $field->getHelpTextAsHtml();
		return self::passwordBox($field->getFieldName(), $field->getId(), $attributes);
	}

	/**
	 * @param form_persistentdocument_file $field
	 * @return string
	 */
	private static function fromFileFieldDocument($field)
	{
		// build required attributes
		$attributes = array();
		$attributes['title'] = $field->getHelpTextAsHtml();
		return self::uploadFileBox($field->getFieldName(), $field->getId(), $attributes);
	}

	/**
	 * @param form_persistentdocument_hidden $field
	 * @return string
	 */
	private static function fromHiddenFieldDocument($field, $value)
	{
		// build required attributes
		return self::hiddenBox($field->getFieldName(), $field->getId(), $value);
	}

	/**
	 * @param form_persistentdocument_date $field
	 * @return string
	 */
	private static function fromDateFieldDocument($field, $value)
	{
		$attributes = array(
			'startDate' => date_DateFormat::format(date_Calendar::getInstance($field->getUiStartDatePicker()), 'd/m/Y'),
			'endDate'   => date_DateFormat::format(date_Calendar::getInstance($field->getUiEndDatePicker()), 'd/m/Y')
			);
		return self::dateBox($field->getFieldName(), $field->getId(), $value, $attributes);
	}

	/**
	 * @param form_persistentdocument_boolean $field
	 * @param string $value
	 * @return string
	 */
	private static function fromBooleanFieldDocument($field, $value)
	{
		// build required attributes
		$attributes = array();
		$attributes['title'] = $field->getHelpTextAsHtml();
		$html = '';
		switch ($field->getDisplay())
		{
			case 'checkbox':
				$html = self::checkBox(
					$field->getFieldName(),
					$field->getId(),
					$field->getTruelabel(),
					($field->getTruelabel() == $value),
					$attributes
					);
				break;

			case 'radio':
			default:
				$html = self::listField(
					$field->getFieldName(),
					$field->getId(),
					$value,
					array($field->getTruelabel() => $field->getTruelabel(), $field->getFalselabel() => $field->getFalselabel()),
					self::SELECTION_SINGLE_RADIO,
					$attributes
					);
				break;
		}
		return $html;
	}

	/**
	 * @param String $code
	 * @param String $key
	 * @return boolean
	 */
	public static function checkCaptchaForKey($code, $key)
	{
		if (f_util_StringUtils::isNotEmpty($code))
		{
			$generator = form_CaptchaGenerator::getInstance();
			$generator->setKey($key);
			if ($code === $generator->getCurrentCode())
			{
				$generator->clearCode();
				return true;
			}
		}
		return false;
	}

	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	public static function hasCondition($elementId)
	{
		$element = DocumentHelper::getDocumentInstance($elementId);		
		return $element->getActivationQuestion() !== null;
	}	
		
	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	public static function getActivationLabel($elementId)
	{
		$element = DocumentHelper::getDocumentInstance($elementId);
		
		$question = $element->getActivationQuestion();
		if($question instanceof form_persistentdocument_list)
		{
			$list = $question->getDatasource();
		}
		elseif ($question instanceof form_persistentdocument_boolean)
		{
			$list = $question;
		}
		
		$value = self::getActivationValue($elementId);
		
		return self::getLabelByListAndValue($list, $value);
	}
	
	/**
	 * @param Integer $elementId
	 * @return Boolean
	 */
	public static function getActivationValue($elementId)
	{
		$element = DocumentHelper::getDocumentInstance($elementId);
		
		return $element->getActivationValue();
	}
	
	/**
	 * @param form_persistentdocument_field $list
	 * @param String $value
	 */
	private function getLabelByListAndValue($list, $value)
	{
		if ($list instanceof form_persistentdocument_boolean)
		{
			return $value;
		}
		else
		{
			foreach($list->getItems() as $item)
			{
				if($item->getValue() == $value)
				{
					return $item->getLabel();
				}
			}
		}
		
		return null;
	}
		
	/**
	 * @param website_Page $page
	 * @param form_persistentdocument_baseform $form
	 */
	public static function addScriptsAndStyles($page)
	{
		$page->addScript('modules.form.lib.js.form');
	}
	
	// DEPRECATED
	
	/**
	 * @deprecated
	 */
	public static function checkCaptcha($code)
	{
		return self::checkCaptchaForKey($code, 'default');
	}
}