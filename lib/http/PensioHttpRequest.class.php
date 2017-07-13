<?php

class PensioHttpRequest
{
	private $url;
	private $method = 'GET';
	private $parameters = array();
	private $postContent;
	private $user;
	private $pass;
	private $logPaymentId;
	private $logPaymentRequestId;
	private $cookie;
	private $headers = array();
	
	public function getUrl() { return $this->url; } 
	public function getMethod() { return $this->method; } 
	public function getParameters() { return $this->parameters; } 
	public function getPostContent() { return $this->postContent; } 
	public function getUser() { return $this->user; } 
	public function getPass() { return $this->pass; } 
	public function getLogPaymentId() { return $this->logPaymentId; } 
	public function getLogPaymentRequestId() { return $this->logPaymentRequestId; } 
	public function getCookie() { return $this->cookie; }
	public function getHeaders() { return $this->headers; } 
	public function setUrl($x) { $this->url = $x; } 
	public function setMethod($x) { $this->method = $x; } 
	public function setParameters($x) { $this->parameters = $x; } 
	public function setPostContent($x) { $this->postContent = $x; } 
	public function setUser($x) { $this->user = $x; } 
	public function setPass($x) { $this->pass = $x; } 
	public function setLogPaymentId($x) { $this->logPaymentId = $x; } 
	public function setLogPaymentRequestId($x) { $this->logPaymentRequestId = $x; } 
	public function setCookie($x) { $this->cookie = $x; } 
	
	public function addHeader($header)
	{
		$this->headers[] = $header;
	}
}