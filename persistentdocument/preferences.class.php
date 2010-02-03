<?php
/**
 * form_persistentdocument_preferences
 * @package modules.form
 */
class form_persistentdocument_preferences extends form_persistentdocument_preferencesbase
{
	
	/**
	 * @see f_persistentdocument_PersistentDocumentImpl::getLabel()
	 *
	 * @return String
	 */
	public function getLabel()
	{
		return f_Locale::translateUI(parent::getLabel());
	}	
	
	/**
	 * Returns an associative array of CAPTCHA parameters.
	 * In the database, CAPTCHA parameters are stored in the following form:
	 * param1=value1;param2=value2;param3=value3
	 *
	 * @return Array<String=>String>
	 */
	public function getCaptchaParameters()
	{
		$parameters = array();
		$serialized = $this->getCaptchaParametersSerialized();
		if (strlen($serialized) > 0)
		{
			$parts = explode(";", $serialized);
			foreach ($parts as $part)
			{
				list($name, $value) = explode("=", $part);
				$parameters[$name] = $value;
			}
		}
		return $parameters;
	}
	
	/**
	 * @param Integer $value
	 */
	public function setIw($value)
	{
		$this->setCaptachaParameter('iw', $value);
	}
	
	/**
	 * @return Integer
	 */
	public function getIw()
	{
		return $this->getCaptachaParameter('iw');
	}
	
	/**
	 * @param Integer $value
	 */
	public function setIh($value)
	{
		$this->setCaptachaParameter('ih', $value);
	}
	
	/**
	 * @return Integer
	 */
	public function getIh()
	{
		return $this->getCaptachaParameter('ih');
	}
	
	/**
	 * @param Integer $value
	 */
	public function setFs($value)
	{
		$this->setCaptachaParameter('fs', $value);
	}
	
	/**
	 * @return Integer
	 */
	public function getFs()
	{
		return $this->getCaptachaParameter('fs');
	}
	
	/**
	 * @param Integer $value
	 */
	public function setFd($value)
	{
		$this->setCaptachaParameter('fd', $value);
	}
	
	/**
	 * @return Integer
	 */
	public function getFd()
	{
		return $this->getCaptachaParameter('fd');
	}
	
	/**
	 * @param Integer $value
	 */
	public function setMl($value)
	{
		$this->setCaptachaParameter('ml', $value);
	}
	
	/**
	 * @return Integer
	 */
	public function getMl()
	{
		return $this->getCaptachaParameter('ml');
	}
	
	// Private parameters.

	/**
	 * Format: param1=value1;param2=value2;param3=value3
	 * @param Array<String=>String> $parameters
	 */
	private function setCaptchaParameters($parameters)
	{
		$paramStrings = array();
		foreach ($parameters as $key => $value)
		{
			$paramStrings[] = $key . '=' . $value;
		}
		$this->setCaptchaParametersSerialized(implode(";", $paramStrings));
	}
	
	/**
	 * @param String $name
	 * @return Integer
	 */
	private function getCaptachaParameter($name)
	{
		$parameters = $this->getCaptchaParameters();
		if (isset($parameters[$name]))
		{
			return $parameters[$name];
		}
		return null;
	}
	
	/**
	 * @param String $name
	 * @param Integer $value
	 */
	private function setCaptachaParameter($name, $value)
	{
		$parameters = $this->getCaptchaParameters();
		$parameters[$name] = $value;
		$this->setCaptchaParameters($parameters);
	}
}