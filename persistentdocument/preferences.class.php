<?php
/**
 * @package modules.form
 */
class form_persistentdocument_preferences extends form_persistentdocument_preferencesbase
{
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
	 * @param integer $value
	 */
	public function setIw($value)
	{
		$this->setCaptachaParameter('iw', $value);
	}
	
	/**
	 * @return integer
	 */
	public function getIw()
	{
		return $this->getCaptachaParameter('iw');
	}
	
	/**
	 * @param integer $value
	 */
	public function setIh($value)
	{
		$this->setCaptachaParameter('ih', $value);
	}
	
	/**
	 * @return integer
	 */
	public function getIh()
	{
		return $this->getCaptachaParameter('ih');
	}
	
	/**
	 * @param integer $value
	 */
	public function setFs($value)
	{
		$this->setCaptachaParameter('fs', $value);
	}
	
	/**
	 * @return integer
	 */
	public function getFs()
	{
		return $this->getCaptachaParameter('fs');
	}
	
	/**
	 * @param integer $value
	 */
	public function setFd($value)
	{
		$this->setCaptachaParameter('fd', $value);
	}
	
	/**
	 * @return integer
	 */
	public function getFd()
	{
		return $this->getCaptachaParameter('fd');
	}
	
	/**
	 * @param integer $value
	 */
	public function setMl($value)
	{
		$this->setCaptachaParameter('ml', $value);
	}
	
	/**
	 * @return integer
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
	 * @param string $name
	 * @return integer
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
	 * @param string $name
	 * @param integer $value
	 */
	private function setCaptachaParameter($name, $value)
	{
		$parameters = $this->getCaptchaParameters();
		$parameters[$name] = $value;
		$this->setCaptchaParameters($parameters);
	}
}