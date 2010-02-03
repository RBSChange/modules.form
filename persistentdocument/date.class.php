<?php
/**
 * form_persistentdocument_date
 * @package modules.form
 */
class form_persistentdocument_date extends form_persistentdocument_datebase
{
	/**
	 * @return String
	 */
	public function getStartDatePicker()
	{
		if ( !$this->getStartDate() )
		{
			return form_DateService::DEFAULT_START_DATE;
		}
		return $this->getStartDate();
	}

	/**
	 * @return String
	 */
	public function getEndDatePicker()
	{
		if (!$this->getEndDate() )
		{
			return form_DateService::DEFAULT_END_DATE;
		}
		return $this->getEndDate();
	}

	/**
	 * @return String
	 */
	public function getUiStartDatePicker()
	{
		if ( !$this->getUiStartDate() )
		{
			return form_DateService::DEFAULT_START_DATE;
		}
		return $this->getUiStartDate();
	}

	/**
	 * @return String
	 */
	public function getUiEndDatePicker()
	{
		if (!$this->getUiEndDate() )
		{
			return form_DateService::DEFAULT_END_DATE;
		}
		return $this->getUiEndDate();
	}
	
	/**
	 * @return String
	 */
	public function getValidators()
	{
		$format = f_Locale::translate("&modules.form.document.date.validator.format;");		
		if ('format' == $format)
		{
			return form_DateService::DEFAULT_VALIDATORS;
		}
		
		$validators = array('date:'.$format);
		if ($this->getStartDate())
		{
			$validators[] =  substr($this->getUIStartDate(),0, 10);
		}
		else
		{
			$validators[] = '';
		}

		if ($this->getEndDate())
		{
			$validators[] =   substr($this->getUIEndDate(),0, 10);
		}
		else
		{
			$validators[] = '';
		}
		
		return implode('|', $validators);
	}
}