<?php

class PensioAPIChargebackEvent
{
	private $date;
	private $type;
	private $reasonCode;
	private $reason;
	private $amount;
	private $currency;
	private $additionalInfo = array();

	public function __construct(SimpleXmlElement $xml)
	{
		$this->date =  new DateTime((string)$xml->Date);
		$this->type = (string)$xml->Type;
		$this->reasonCode = (string)$xml->ReasonCode;
		$this->reason = (string)$xml->Reason;
		$this->amount = (string)$xml->Amount;
		$this->currency = (string)$xml->Currency;

		$additionalInfoXml = @simplexml_load_string((string)$xml->AdditionalInfo);
		foreach($additionalInfoXml->info_element as $infoElement)
		{
			$this->additionalInfo[(string)$infoElement->key] = (string)$infoElement->value;
		}
	}

	public function getDate()
	{
		return $this->date;
	}

	public function setDate($date)
	{
		return $this->date = $date;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		return $this->type = $type;
	}

	public function getReasonCode()
	{
		return $this->reasonCode;
	}

	public function setReasonCode($reasonCode)
	{
		return $this->reasonCode = $reasonCode;
	}

	public function getReason()
	{
		return $this->reason;
	}

	public function setReason($reason)
	{
		return $this->reason = $reason;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setAmount($amount)
	{
		return $this->amount = $amount;
	}

	public function getCurrency()
	{
		return $this->currency;
	}

	public function setCurrency($currency)
	{
		return $this->currency = $currency;
	}

	public function getAdditionalInfo()
	{
		return $this->additionalInfo;
	}

	public function setAdditionalInfo(array $additionalInfo)
	{
		return $this->additionalInfo = $additionalInfo;
	}
}

