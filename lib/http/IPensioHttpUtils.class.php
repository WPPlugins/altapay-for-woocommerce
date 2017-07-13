<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/http/PensioHttpRequest.class.php');
require_once(PENSIO_API_ROOT.'/http/PensioHttpResponse.class.php');

interface IPensioHttpUtils
{
	/**
	 * @return PensioHttpResponse
	 */
	public function requestURL(PensioHttpRequest $request);
}