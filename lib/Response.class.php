<?php
interface form_Response
{
	/**
	 * @return string
	 */
	public function getBoEditorModule();
	
	/**
	 * @param string $key
	 * @return string | null
	 */
	public function getResponseFieldValue($key);
}