<?php
interface form_FormReceiver
{
	/**
	 * Return the list of emails addresses
	 * @return array Array of string
	 */
	public function getEmailAddresses();
}