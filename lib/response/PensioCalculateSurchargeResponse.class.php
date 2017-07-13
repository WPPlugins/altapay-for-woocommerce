<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/response/PensioAbstractResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioTerminal.class.php');

class PensioCalculateSurchargeResponse extends PensioAbstractResponse
{
	private $result;
	private $surchargeAmount = array();
	
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
		
		if($this->getErrorCode() === '0')
		{
			$this->result = (string)$xml->Body->Result;
			$this->surchargeAmount = (string)$xml->Body->SurchageAmount;
		}
	}
	
	public function getSurchargeAmount()
	{
		return $this->surchargeAmount;
	}
	
	public function wasSuccessful()
	{
		return $this->result === 'Success';
	}
}