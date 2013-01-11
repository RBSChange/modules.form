<?php
/**
 * form_persistentdocument_date
 * @package modules.form
 */
class form_persistentdocument_date extends form_persistentdocument_datebase
{
	/**
	 * @return string
	 */
	public function getStartDatePicker()
	{
		if ($this->getRangeType() == 'fixed')
		{
			if ($this->getStartDate())
			{
				return $this->getStartDate();
			}
		}
		elseif ($this->getRangeType() == 'floating')
		{
			return $this->calculateDateFromFloating($this->getFloatingStartDate());
		}
		return form_DateService::DEFAULT_START_DATE;
	}
	
	/**
	 * @return string
	 */
	public function getEndDatePicker()
	{
		if ($this->getRangeType() == 'fixed')
		{
			if ($this->getEndDate())
			{
				return $this->getEndDate();
			}
		}
		elseif ($this->getRangeType() == 'floating')
		{
			return $this->calculateDateFromFloating($this->getFloatingEndDate());
		}
		return form_DateService::DEFAULT_END_DATE;
	}
	
	/**
	 * @return String
	 */
	public function getUiStartDatePicker()
	{
		return date_Converter::convertDateToLocal($this->getStartDatePicker());
	}
	
	/**
	 * @return String
	 */
	public function getUiEndDatePicker()
	{
		return date_Converter::convertDateToLocal($this->getEndDatePicker());
	}
	
	/**
	 * @param string $floatingDate
	 * @return date_Calendar
	 */
	public function calculateDateFromFloating($floatingDate)
	{
		$floatingDate = preg_replace('/([+\-][0-9]+)y/', '\\1year', $floatingDate);
		$floatingDate = preg_replace('/([+\-][0-9]+)m/', '\\1month', $floatingDate);
		$floatingDate = preg_replace('/([+\-][0-9]+)w/', '\\1week', $floatingDate);
		$floatingDate = preg_replace('/([+\-][0-9]+)d/', '\\1day', $floatingDate);
		$date = new DateTime($floatingDate);
		return date_Calendar::getInstance($date->format('Y-m-d H:i:s'));
	}
	
	/**
	 * @return string
	 */
	public function getValidators()
	{
		$format = LocaleService::getInstance()->trans("m.form.document.date.validator.format");
		if ('format' == $format)
		{
			return form_DateService::DEFAULT_VALIDATORS;
		}
		
		$validators = array('date:' . $format);
		if ($this->getStartDate())
		{
			$validators[] = substr($this->getUIStartDate(), 0, 10);
		}
		else
		{
			$validators[] = '';
		}
		
		if ($this->getEndDate())
		{
			$validators[] = substr($this->getUIEndDate(), 0, 10);
		}
		else
		{
			$validators[] = '';
		}
		
		return implode('|', $validators);
	}
	
	/**
	 * @return boolean
	 */
	public function isValid()
	{
		if (!parent::isValid())
		{
			return false;
		}
		
		$startDate = date_Calendar::getInstance($this->getStartDatePicker());
		$endDate = date_Calendar::getInstance($this->getEndDatePicker());
		if ($endDate->isBefore($startDate))
		{
			if ($this->getRangeType() == 'fixed')
			{
				$fieldName = 'startDate';
			}
			elseif ($this->getRangeType() == 'floating')
			{
				$fieldName = 'floatingStartDate';
			}
			$this->validationErrors->rejectValue($fieldName, LocaleService::getInstance()->trans('m.form.bo.general.must-start-before-end', array('ucf')));
			return false;
		}
		return true;
	}
}