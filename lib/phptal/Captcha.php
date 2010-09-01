<?php
/**
 * @package modules.form.
 * @author INTbonjF
 * 2007-08-02
 */
class PHPTAL_Php_Attribute_CHANGE_captcha extends PHPTAL_Php_Attribute
{
	public function start()
	{
		$parameters = array();
		$key = "'default'";
		$expressions = $this->tag->generator->splitExpression($this->expression);
		foreach ($expressions as $exp)
		{
			list ($attribute, $value) = $this->parseSetExpression($exp);
			$attribute = strtolower($attribute);
			
			switch ($attribute)
			{
				case 'width' :
					$parameters['iw'] = $this->evaluate($value);
					break;
				case 'height' :
					$parameters['ih'] = $this->evaluate($value);
					break;
				case 'fontsize' :
					$parameters['fs'] = $this->evaluate($value);
					break;
				case 'fontdepth' :
					$parameters['fd'] = $this->evaluate($value);
					break;
				case 'maxlength' :
					$parameters['ml'] = $this->evaluate($value);
					break;
				case 'key' :
					$key = $this->evaluate($value);
					break;
			}
		}
		
		switch (strtolower($this->tag->name))
		{
			case 'img' :
				$this->tag->generator->doEcho('PHPTAL_Php_Attribute_CHANGE_captcha::renderImage(' . var_export($this->tag->attributes, true) . ', ' . var_export($parameters, true) . ', strval(' . $key . '))');
				break;
			case 'input' :
				$this->tag->generator->doEcho('PHPTAL_Php_Attribute_CHANGE_captcha::renderInput(' . var_export($this->tag->attributes, true) . ', ' . var_export($parameters, true) . ', strval(' . $key . '))');
				break;
		}
	}
	
	private static function generateCaptchaCode($parameters, $key)
	{
		$generator = form_CaptchaGenerator::getInstance();
		$generator->setKey($key);
		foreach ($parameters as $name => $value)
		{
			switch ($name)
			{
				case 'ml' :
					$generator->setCodeMaxLength(intval($value));
					break;
				case 'iw' :
					$generator->setWidth(intval($value));
					break;
				case 'ih' :
					$generator->setHeight(intval($value));
					break;
				case 'fs' :
					$generator->setFontSize(intval($value));
					break;
				case 'fd' :
					$generator->setFontDepth(intval($value));
					break;
			}
		}
		$generator->generateCode();
	}
	
	public function renderImage($attributes, $parameters, $key)
	{
		// Get CAPTCHA parameters from the module form's preferences.
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
		self::generateCaptchaCode($parameters, $key);
		$parameters['key'] = $key;
		$url = htmlentities(LinkHelper::getUrl('form', 'Captcha', $parameters), ENT_COMPAT, "utf-8");
		$code = '<span class="captcha"><input type="image" src="' . $url . '"';
		if (!isset($attributes['title']))
		{
			$attributes['title'] = f_Locale::translate("&modules.form.bo.general.Captcha-click-to-have-another-one;");
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
	
	public function renderInput($attributes, $parameters, $key)
	{
		$code = '<input type="text" class="textfield" name="formParam[' . CAPTCHA_SESSION_KEY . ']"';
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
			$attributes['title'] = f_Locale::translate("&modules.form.bo.general.Captcha-help;");
		}
		foreach ($attributes as $name => $value)
		{
			$code .= ' ' . $name . '="' . str_replace('"', '\"', $value) . '"';
		}
		return $code . '/>';
	}
	
	public function end()
	{
	}
}