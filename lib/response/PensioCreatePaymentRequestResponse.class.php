<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/response/PensioAbstractResponse.class.php');

class PensioCreatePaymentRequestResponse extends PensioAbstractResponse
{
	private $redirectURL, $result;
	
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
		
		if($this->getErrorCode() === '0')
		{
			$this->result = (string)$xml->Body->Result;
			$this->redirectURL = (string)$xml->Body->Url;
		}
	}
	
	public function getRedirectURL()
	{
		return $this->redirectURL;
	}
	
	public function wasSuccessful()
	{
		return $this->getErrorCode() === '0' && $this->result == 'Success';
	}
}