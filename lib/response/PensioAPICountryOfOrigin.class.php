<?php

class PensioAPICountryOfOrigin
{
	const NotSet = 'NotSet';
	const CardNumber = 'CardNumber';
	const BankAccount = 'BankAccount';
	const BillingAddress = 'BillingAddress';
	const RegisteredAddress = 'RegisteredAddress';
	const ShippingAddress = 'ShippingAddress';
	const PayPal = 'PayPal';

	private $country;
	private $source;

	public function __construct(SimpleXmlElement $xml)
	{
		$this->country = (string)$xml->Country;
		$this->source = (string)$xml->Source;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getSource()
	{
		return $this->source;
	}
}