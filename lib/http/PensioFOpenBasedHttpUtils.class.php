<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',dirname(__DIR__));
}

require_once(PENSIO_API_ROOT.'/http/IPensioHttpUtils.class.php');

class PensioFOpenBasedHttpUtils implements IPensioHttpUtils
{
	private $streamState;
	
	public function __construct($timeoutSeconds=60, $connectionTimeout=30)
	{
		$this->timeout = $timeoutSeconds;
		$this->connectionTimeout = $connectionTimeout;
	}
	
	/**
	 * @return PensioHttpResponse
	 */
	public function requestURL(PensioHttpRequest $request)
	{
		$this->streamState = 'NOT_CONNECTED';
		
		global $http_response_header;
		$context = $this->createContext($request);
		
		$url = ($request->getMethod() == 'GET') ? $this->appendToUrl($request->getUrl(), $request->getParameters()) : $request->getUrl(); 
		$content = @file_get_contents($url, false, $context);
		$response = new PensioHttpResponse();
		$response->setInfo(array('http_code'=>$this->getHttpCodeFromHeader($http_response_header)));
		if($content !== false)
		{
			$response->setHeader($http_response_header);
			$response->setContent($content);
			$response->setConnectionResult(PensioHttpResponse::CONNECTION_OKAY);
		}
		else
		{
			if($this->streamState == 'NOT_CONNECTED')
			{
				$response->setConnectionResult(PensioHttpResponse::CONNECTION_REFUSED);
			}
			else
			{
				$response->setConnectionResult(PensioHttpResponse::CONNECTION_READ_TIMEOUT);
			}
		}
		
		return $response;
	}
	
	private function createContext(PensioHttpRequest $request)
	{
		$args = array(
			'http' => array(
					'method'  => $request->getMethod(),
					'header'  => sprintf("Authorization: Basic %s\r\n", base64_encode($request->getUser().':'.$request->getPass())).
					"Content-type: application/x-www-form-urlencoded\r\n",
					'timeout' => $this->timeout,
					'ignore_errors' => true,
			),
		);
		if($request->getMethod() == 'POST')
		{
			$args['http']['content'] = http_build_query($request->getParameters());
		}
		$context = stream_context_create($args);
		stream_context_set_params($context, array('notification' => array($this, 'stream_notification_callback')));
		return $context;
	}
	
	public function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
	{
	    switch($notification_code) {
	        case STREAM_NOTIFY_FAILURE:
	        	if(strpos($message, '401 Unauthorized'))
	        	{
	        		$this->streamState = 'AUTH_FAILED';
	        	}
	        	break;
	        case STREAM_NOTIFY_CONNECT:
	            $this->streamState = 'CONNECTED';
	            break;
	        default:
	        	//echo "Notification: ".$notification_code."\n";
	        	break;
	    }
	}
	
	private function getHttpCodeFromHeader($http_response_header)
	{
		if(is_array($http_response_header) && isset($http_response_header[0]))
		{
			if(preg_match('/HTTP\/[0-9\.]+ ([0-9]{3}) .*/', $http_response_header[0], $matches))
			{
				return $matches[1];
			}
		}
		return 0;
	}
	
	/**
	 * This method will append the given parameters to the URL. Using a ? or a & depending on the url
	 *
	 * @param string$url
	 * @param array $parameters
	 * @return string - the URL with the new parameters appended
	 */
	public function appendToUrl($url, array $parameters)
	{
		if(count($parameters) > 0)
		{
			$append = http_build_query($parameters);
			return $url.(strstr($url, "?") ? "&" : "?").$append;
		}
		return $url;
	}
}