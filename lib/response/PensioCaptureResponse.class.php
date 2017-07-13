<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/response/PensioAbstractPaymentResponse.class.php');

class PensioCaptureResponse extends PensioAbstractPaymentResponse
{
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
	}
	
	protected function parseBody(SimpleXmlElement $body)
	{
		
	}
	
}