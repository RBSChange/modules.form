<?php
/**
 * @package modules.form.
 * @author INTbonjF
 * 2007-08-02
 */
class PHPTAL_Php_Attribute_CHANGE_Captcha extends ChangeTalAttribute
{
	/**
	 * @see ChangeTalAttribute::getDefaultValues()
	 */
	protected function getDefaultValues()
	{
		return array('key' => 'default');
	}

	/**
	 * @see ChangeTalAttribute::getEvaluatedParameters()
	 */
	protected function getEvaluatedParameters()
	{
		return array('width', 'height', 'fontsize', 'fontdepth', 'maxlength', 'key');
	}
	
	public static function renderCaptcha($parameters, $ctx)
	{
		if ($parameters['tagname'] === 'img')
		{
			return self::renderImage($parameters, $ctx);
		}
		elseif ($parameters['tagname'] === 'input')
		{
			return self::renderInput($parameters, $ctx);
		}
	}
	
	private static function generateCaptchaCode($parameters)
	{
		$generator = form_CaptchaGenerator::getInstance();
		$generator->setKey($parameters['key']);
		foreach ($parameters as $name => $value)
		{
			switch ($name)
			{
				case 'maxlength' :
					$generator->setCodeMaxLength(intval($value));
					break;
				case 'width' :
					$generator->setWidth(intval($value));
					break;
				case 'height' :
					$generator->setHeight(intval($value));
					break;
				case 'fontsize' :
					$generator->setFontSize(intval($value));
					break;
				case 'fontdepth' :
					$generator->setFontDepth(intval($value));
					break;
			}
		}
		$generator->generateCode();
	}
	
	/**
	 * @param array $parameters
	 * @param PHPTAL_Context $ctx
	 */
	private static function renderImage($allParameters, $ctx)
	{
		$parameterNames = self::getEvaluatedParameters();
		$parameters = array();
		$attributes = array();
		foreach ($allParameters as $name => $value) 
		{
			if (in_array($name, $parameterNames))
			{
				$parameters[$name] = $value;
			}
			else
			{
				$attributes[$name] = $value;
			}
		}
		
		$pref = ModuleService::getInstance()->getPreferencesDocument('form');
		if ($pref !== null)
		{
			foreach ($pref->getCaptchaParameters() as $name => $value)
			{
				// Parameters defined in the template overload the preferences' parameters.
				if (!isset($parameters[$name]))
				{
					$parameters[$name] = $value;
				}
			}
		}
		self::generateCaptchaCode($parameters);
		$url = LinkHelper::getActionLink('form', 'Captcha')
			->setQueryParameters($parameters)
			->setArgSeparator(f_web_HttpLink::ESCAPE_SEPARATOR)->getUrl();
			
		$code = '<span class="captcha"><input type="image" src="' . $url . '"';
		if (!isset($attributes['title']))
		{
			$attributes['title'] = LocaleService::getInstance()->transFO("m.form.bo.general.captcha-click-to-have-another-one", array('ucf', 'attr'));
		}
		if (!isset($attributes['onclick']))
		{
			$attributes['onclick'] = "CAPTCHA.reload(this, '" . $url . "'); return false;";
		}
		foreach ($attributes as $name => $value)
		{
			$code .= ' ' . $name . '="' . str_replace('"', '\"', $value) . '"';
		}
		return $code . ' /></span>';
	}

	/**
	 * @param array $parameters
	 * @param PHPTAL_Context $ctx
	 */
	private static function renderInput($allParameters, $ctx)
	{
		$code = '<input type="text" class="textfield" name="formParam[' . CAPTCHA_SESSION_KEY . ']"';
		$parameterNames = array('width', 'height', 'fontsize', 'fontdepth', 'key');
		$attributes = array();
		foreach ($allParameters as $name => $value) 
		{
			if (!in_array($name, $parameterNames))
			{
				$attributes[$name] = $value;
			}
		}
		
		if (isset($attributes['name']))
		{
			unset($attributes['name']);
		}
		if (!isset($attributes['maxlength']))
		{
			$attributes['maxlength'] = '7';
		}
		if (!isset($attributes['size']))
		{
			$attributes['size'] = '7';
		}
		if (!isset($attributes['title']))
		{
			$attributes['title'] = LocaleService::getInstance()->transFO("m.form.bo.general.captcha-help", array('ucf', 'attr'));
		}
		foreach ($attributes as $name => $value)
		{
			$code .= ' ' . $name . '="' . str_replace('"', '\"', $value) . '"';
		}
		return $code . '/>';
	}
}