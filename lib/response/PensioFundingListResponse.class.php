<?php

class PensioFundingListResponse extends PensioAbstractResponse
{
	private $numberOfPages;
	private $fundings = array();

	public function __construct(SimpleXmlElement $xml)
	{
		parent::__construct($xml);
		if($this->getErrorCode() === '0')
		{
			$attr = $xml->Body->Fundings->attributes();
			$this->numberOfPages = (string)$attr['numberOfPages'];
			foreach($xml->Body->Fundings->Funding as $funding)
			{
				$this->fundings[] = new PensioAPIFunding($funding);
			}
		}		
	}
	
	public function wasSuccessful()
	{
		return $this->getNumberOfPages() > 0;
	}
	
	public function getNumberOfPages()
	{
		return $this->numberOfPages;
	}
	
	public function getFundings()
	{
		return $this->fundings;
	}
}