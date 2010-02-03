<?php
class form_FormException extends Exception
{
}

class form_FieldAlreadyExistsException extends form_FormException
{
}

class form_FieldLockedException extends form_FormException
{
}

class form_FormValidationException extends form_FormException
{
	private $errorCollection;

	public function __construct($message, $errorCollection)
	{
		parent::__construct($message, 0);
		$this->errorCollection = $errorCollection;
	}

	public function getErrorCollection()
	{
		return $this->errorCollection;
	}
}

class form_ReplyToFieldAlreadyExistsException extends form_FormException
{
}