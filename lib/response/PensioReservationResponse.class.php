<?php
if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/response/PensioAbstractPaymentResponse.class.php');

class PensioReservationResponse extends PensioAbstractPaymentResponse
{
	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
	}
	
	protected function parseBody(SimpleXmlElement $body)
	{
		
	}

	public function wasSuccessful()
	{
		if(parent::wasSuccessful())
		{
			// There must be at least one Payment
			if(!is_null($this->getPrimaryPayment()))
			{
				// If the current state is supposed to be more than 'created'
				return $this->getPrimaryPayment()->getCurrentStatus() != 'created';
			}
		}
		return false;
	}
}