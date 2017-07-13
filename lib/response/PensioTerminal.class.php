<?php

class PensioTerminal
{
	private $title;
	private $country;
	private $natures = array();
	private $currencies = array();
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setCountry($country)
	{
		$this->country = $country;
	}
	
	public function addNature($nature)
	{
		$this->natures[] = $nature;
	}
	
	public function addCurrency($currency)
	{
		$this->currencies[] = $currency;
	}
	
	public function hasCurrency($currency)
	{
        return in_array('XXX', $this->currencies) || in_array($currency, $this->currencies);
	}
}