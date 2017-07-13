<?php

class PensioHttpResponse
{
	const CONNECTION_REFUSED = 'CONNECTION_REFUSED';
	const CONNECTION_TIMEOUT = 'CONNECTION_TIMEOUT';
	const CONNECTION_READ_TIMEOUT = 'CONNECTION_READ_TIMEOUT';
	const CONNECTION_OKAY = 'CONNECTION_OKAY';
	
	private $requestHeader = '';
	private $header = '';
	private $content = '';
	private $info;
	private $errorMessage;
	private $errorNumber;
	private $connectionResult;
	
	public function getHeader()
	{
		return $this->header;
	}
	
	public function getContentType()
	{
		if(preg_match('/^Content-Type: (.+)$/m',$this->header,$matches))
		{
			return trim($matches[1]);
		}
	}
	
	public function getLocationHeader()
	{
		if(preg_match('/^Location: (.+)$/m',$this->header,$matches))
		{
			return trim($matches[1]);
		}
	}
	
	public function getContent()
	{
		return $this->content;
	}
	
	public function setHeader($header)
	{
		if(is_array($header))
		{
			$header = implode("\r\n", $header);
		}		
		$this->header = $header;
	}
	
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getRequestHeader()
	{
		return $this->requestHeader;
	}
	
	public function setRequestHeader($requestHeader)
	{
		$this->requestHeader = $requestHeader;
	}
	
	public function getInfo()
	{
		return $this->info;
	}
	
	public function setInfo($info)
	{
		$this->info = $info;
	}
	
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}
	
	public function setErrorMessage($errorMessage)
	{
		$this->errorMessage = $errorMessage;
	}
	
	public function getErrorNumber()
	{
		return $this->errorNumber;
	}
	
	public function setErrorNumber($errorNumber)
	{
		$this->errorNumber = $errorNumber;
	}
	
	public function getConnectionResult()
	{
		return $this->connectionResult;
	}
	
	public function setConnectionResult($connectionResult)
	{
		$this->connectionResult = $connectionResult;
	}
	
	public function getHttpCode()
	{
		return $this->info['http_code'];
	}
	
	public function curlReadHeader(&$curl, $header)
	{
		$this->header .= $header;
		
		return strlen($header);
	}

	public function curlReadContent(&$curl, $content)
	{
		$this->content .= $content;
		
		return strlen($content);
	}
}