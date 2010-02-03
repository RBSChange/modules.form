<?php
class form_CaptchaGenerator extends BaseService
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
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @var Integer
	 */
	protected $fontSize = 12;

	/**
	 * @var Integer
	 */
	protected $fontDepth = 3;

	/**
	 * @var Integer
	 */
	protected $width = 120;

	/**
	 * @var Integer
	 */
	protected $height = 40;

	/**
	 * @var Integer
	 */
	protected $codeMaxLength = 5;

	/**
	 * @var Integer
	 */
	protected $codeMinLength = 3;

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setFontSize($value)
	{
		$this->fontSize = $value;
		return $this;
	}

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setFontDepth($value)
	{
		$this->fontDepth = $value;
		return $this;
	}

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setWidth($value)
	{
		$this->width = $value;
		return $this;
	}

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setHeight($value)
	{
		$this->height = $value;
		return $this;
	}

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setCodeMaxLength($value)
	{
		$this->codeMaxLength = $value;
		return $this;
	}

	/**
	 * @param Integer $value
	 * @return form_CaptchaGenerator
	 */
	public function setCodeMinLength($value)
	{
		$this->codeMinLength = $value;
		return $this;
	}

	/**
	 * @param String $code
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
			$t  = new gotcha_TextEffect($code, $this->fontSize, $this->fontDepth);
			$t->addFont(f_util_FileUtils::buildAbsolutePath(FRAMEWORK_HOME, 'libs', 'gotcha', 'SFTransRoboticsExtended.ttf'));
			$t->addFont(f_util_FileUtils::buildAbsolutePath(FRAMEWORK_HOME, 'libs', 'gotcha', 'arialbd.ttf'));
			$t->addFont(f_util_FileUtils::buildAbsolutePath(FRAMEWORK_HOME, 'libs', 'gotcha', 'comic.ttf'));
			$t->addFont(f_util_FileUtils::buildAbsolutePath(FRAMEWORK_HOME, 'libs', 'gotcha', 'britanic.ttf'));
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
	 * @return String
	 */
	public function generateCode()
	{
		$text = '';
		$nb = mt_rand($this->codeMinLength, $this->codeMaxLength);
		for ($i=0 ; $i<$nb ; $i++)
		{
			$text .= chr(mt_rand(ord('0'), ord('9')));
		}
		$this->registerCode($text);
		return $text;
	}

	/**
	 * @param String $code
	 */
	protected final function registerCode($code)
	{
		Controller::getInstance()->getContext()->getUser()->setAttribute(CAPTCHA_SESSION_KEY, $code);
	}

	/**
	 * Renders the generated image to the browser.
	 * @param String $code
	 */
	public final function render($code)
	{
		$this->generate($code)->render();
	}
}