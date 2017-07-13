<?php

class PensioAPICustomerInfo
{
	/*
					<UserAgent>Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:13.0)
						Gecko/20100101 Firefox/13.0.1</UserAgent>
					<IpAddress>81.7.175.18</IpAddress>
					<Email></Email>
					<Username></Username>
					<CustomerPhone></CustomerPhone>
					<OrganisationNumber></OrganisationNumber>
					<CountryOfOrigin>
						<Country></Country><Source>NotSet</Source>
					</CountryOfOrigin>
	*/
	private $userAgent;
	private $ipAddress;
	private $email;
	private $username;
	private $phone;
	private $organisationNumber;
	
	/**
	 * @var PensioAPIAddress
	 */
	private $billingAddress,$shippingAddress,$registeredAddress;

	private $countryOfOrigin;

	public function __construct(SimpleXmlElement $xml)
	{
		$this->userAgent = (string)$xml->UserAgent;
		$this->ipAddress = (string)$xml->IpAddress;
		$this->email = (string)$xml->Email;
		$this->username = (string)$xml->Username;
		$this->phone = (string)$xml->CustomerPhone;
		$this->organisationNumber = (string)$xml->OrganisationNumber;

		if(isset($xml->CountryOfOrigin))
		{
			$this->countryOfOrigin = new PensioAPICountryOfOrigin($xml->CountryOfOrigin);
		}
		if(isset($xml->BillingAddress))
		{
			$this->billingAddress = new PensioAPIAddress($xml->BillingAddress);
		}
		if(isset($xml->ShippingAddress))
		{
			$this->shippingAddress = new PensioAPIAddress($xml->ShippingAddress);
		}
		if(isset($xml->RegisteredAddress))
		{
			$this->registeredAddress = new PensioAPIAddress($xml->RegisteredAddress);
		}
	}
	
	/**
	 * @return PensioAPIAddress
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @return PensioAPIAddress
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}
	
	/**
	 * @return PensioAPIAddress
	 */
	public function getRegisteredAddress()
	{
		return $this->registeredAddress;
	}

	/**
	 * @return PensioAPICountryOfOrigin
	 */
	public function getCountryOfOrigin()
	{
		return $this->countryOfOrigin;
	}
	
	public function getUserAgent()
	{
		return $this->userAgent;
	}
	
	public function getIpAddress()
	{
		return $this->ipAddress;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function getPhone()
	{
		return $this->phone;
	}
	
	public function getOrganisationNumber()
	{
		return $this->organisationNumber;
	}
}