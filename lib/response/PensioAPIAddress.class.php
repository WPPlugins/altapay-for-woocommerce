<?php

class PensioAPIAddress
{
	/*
		<BillingAddress>
			<Firstname><![CDATA[Kødpålæg >-) <script>alert(42);</script>]]></Firstname>
			<Lastname><![CDATA[Lyn]]></Lastname>
			<Address><![CDATA[Rosenkæret 13]]></Address>
			<City><![CDATA[Søborg]]></City>
			<Region><![CDATA[]]></Region>
			<Country><![CDATA[DK]]></Country>
			<PostalCode><![CDATA[2860]]></PostalCode>
		</BillingAddress>
	*/
	private $firstName;
	private $lastName;
	private $address;
	private $city;
	private $region;
	private $country;
	private $postalCode;
	
	public function __construct(SimpleXmlElement $xml)
	{
		$attrs = $xml->attributes();

		$this->firstName = (string)$xml->Firstname;
		$this->lastName = (string)$xml->Lastname;
		$this->address = (string)$xml->Address;
		$this->city = (string)$xml->City;
		$this->region = (string)$xml->Region;
		$this->country = (string)$xml->Country;
		$this->postalCode = (string)$xml->PostalCode;
	}
	
	public function getFirstName()
	{
		return $this->firstName;
	}

	public function getLastName()
	{
		return $this->lastName;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getPostalCode()
	{
		return $this->postalCode;
	}
}