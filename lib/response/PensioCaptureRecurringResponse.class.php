<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/response/PensioPreauthRecurringResponse.class.php');

class PensioCaptureRecurringResponse extends PensioPreauthRecurringResponse
{
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
	}
	
	/**
	 * @return boolean
	 */
	public function wasSubscriptionReleased()
	{
		return $this->getSubscriptionPayment()->isReleased();
	}
}