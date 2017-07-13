<?php

class PensioAPIPaymentNatureService
{
	private $name;
	private $supportsRefunds;
	private $supportsRelease;
	private $supportsMultipleCaptures;
	private $supportsMultipleRefunds;
	
	public function __construct(SimpleXmlElement $xml)
	{
		$attrs = $xml->attributes();

		$this->name = strval(@$attrs['name']);
		$this->supportsRefunds = (string)$xml->SupportsRefunds;
		$this->supportsRelease = (string)$xml->SupportsRelease;
		$this->supportsMultipleCaptures = (string)$xml->SupportsMultipleCaptures;
		$this->supportsMultipleRefunds = (string)$xml->SupportsMultipleRefunds;
	}
	
	public function getName()
	{
		return $this->name;
	}

	public function getSupportsRefunds()
	{
		return $this->supportsRefunds;
	}

	public function getSupportsRelease()
	{
		return $this->supportsRelease;
	}

	public function getSupportsMultipleCaptures()
	{
		return $this->supportsMultipleCaptures;
	}

	public function getSupportsMultipleRefunds()
	{
		return $this->supportsMultipleRefunds;
	}
}