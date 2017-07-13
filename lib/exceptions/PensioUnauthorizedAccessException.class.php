<?php

class PensioUnauthorizedAccessException extends PensioMerchantAPIException
{
	public function __construct($url, $username)
	{
		parent::__construct("Unauthorized access to ".$url." for user ".$username, 9283745);
	}
}