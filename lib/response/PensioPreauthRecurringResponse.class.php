<?php
if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}
require_once(PENSIO_API_ROOT.'/response/PensioAbstractPaymentResponse.class.php');

class PensioPreauthRecurringResponse extends PensioAbstractPaymentResponse
{
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
	}
	
	protected function parseBody(SimpleXmlElement $body)
	{
		
	}

	/**
	 * This payment represent the subscription, it is returned as the subscription it 
	 * self might have changed since last time it was used.
	 * 
	 * @return PensioAPIPayment
	 */
	public function getSubscriptionPayment()
	{
		return isset($this->payments[0]) ? $this->payments[0] : null;
	}
	
	/**
	 * This is the payment which was pre-authed.
	 * 
	 * @return PensioAPIPayment
	 */
	public function getPrimaryPayment()
	{
		return isset($this->payments[1]) ? $this->payments[1] : null;
	}
}