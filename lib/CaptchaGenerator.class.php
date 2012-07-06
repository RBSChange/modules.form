<?php
class form_CaptchaGenerator extends change_BaseService
{
	/**
	 * @var form_CaptchaGenerator
	 */
	private static $instance;
	
	/**
	 * @return form_CaptchaGenerator
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @var integer
	 */
	protected $fontSize = 12;
	
	/**
	 * @var integer
	 */
	protected $fontDepth = 3;
	
	/**
	 * @var integer
	 */
	protected $width = 120;
	
	/**
	 * @var integer
	 */
	protected $height = 40;
	
	/**
	 * @var integer
	 */
	protected $codeMaxLength = 5;
	
	/**
	 * @var integer
	 */
	protected $codeMinLength = 3;
	
	/**
	 * @var string
	 */
	protected $key = 'default';
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setFontSize($value)
	{
		$this->fontSize = $value;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setFontDepth($value)
	{
		$this->fontDepth = $value;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setWidth($value)
	{
		$this->width = $value;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setHeight($value)
	{
		$this->height = $value;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setCodeMaxLength($value)
	{
		$this->codeMaxLength = $value;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setCodeMinLength($value)
	{
		$this->codeMinLength = $value;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return form_CaptchaGenerator
	 */
	public function setKey($value)
	{
		$this->key = $value;
		return $this;
	}
	
	/**
	 * @param string $code
	 * @return gotcha_GotchaPng
	 */
	protected function generate($code)
	{
		$img = new gotcha_GotchaPng($this->width, $this->height);
		
		if ($img->create())
		{
			//fill the background color.
			$img->apply(new gotcha_GradientEffect());
			//Apply the Grid.
			$img->apply(new gotcha_GridEffect(2));
			$img->apply(new gotcha_LineEffect());
			//Add the text.
			$t = new gotcha_TextEffect($code, $this->fontSize, $this->fontDepth);
			$t->addFont(f_util_FileUtils::buildFrameworkPath('libs', 'gotcha', 'SFTransRoboticsExtended.ttf'));
			$t->addFont(f_util_FileUtils::buildFrameworkPath('libs', 'gotcha', 'arialbd.ttf'));
			$t->addFont(f_util_FileUtils::buildFrameworkPath('libs', 'gotcha', 'comic.ttf'));
			$t->addFont(f_util_FileUtils::buildFrameworkPath('libs', 'gotcha', 'britanic.ttf'));
			// repeat the process for as much fonts as you want. Actually, the more the better.
			// A font type will be randomly selected for each character in the text code.
			$img->apply($t);
			//Add more dots
			$img->apply(new gotcha_DotEffect());
			return $img;
		}
		
		throw new Exception("Could not generate CAPTCHA image.");
	}
	
	/**
	 * @return string
	 */
	public function generateCode()
	{
		$text = '';
		$characters = '123479ACDEFGHIJKLNPQRTXYZ';
		$nb = mt_rand($this->codeMinLength, $this->codeMaxLength);
		for ($i = 0; $i < $nb; $i++)
		{
			$text .= $characters[mt_rand(0, strlen($characters)-1)];
		}
		$this->registerCode($text);
		return $text;
	}
	
	/**
	 * @param string $code
	 */
	protected final function registerCode($code)
	{
		$codes = $this->getCurrentCodes();
		$codes[$this->key] = $code;
		change_Controller::getInstance()->getStorage()->writeForUser('form_CHANGE_CAPTCHA', $codes);
	}
	
	/**
	 * Renders the generated image to the browser.
	 * @param string $code
	 */
	public final function render($code = null)
	{
		if ($code === null)
		{
			$code = $this->getCurrentCode();
		}
		$this->generate($code)->render();
	}
	
	/**
	 * @return string
	 */
	public function getCurrentCode()
	{
		$codes = $this->getCurrentCodes();
		return (isset($codes[$this->key])) ? $codes[$this->key] : null;
	}
	
	/**
	 * @return string
	 */
	protected function getCurrentCodes()
	{
		$codes = change_Controller::getInstance()->getStorage()->readForUser('form_CHANGE_CAPTCHA');
		return (is_array($codes)) ? $codes : array();
	}
	
	/**
	 * @return string
	 */
	public function clearCode()
	{
		$codes = $this->getCurrentCodes();
		unset($codes[$this->key]);
		change_Controller::getInstance()->getStorage()->writeForUser('form_CHANGE_CAPTCHA', $codes);
	}
}