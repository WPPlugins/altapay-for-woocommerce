<?php

interface IPensioCommunicationLogger
{
	/**
	 * Will get a string representation of the request being sent to Pensio.
	 * @param string $message
	 * @return string - A log-id used to match the request and response
	 */
	public function logRequest($message);
	
	/**
	 * Will get a string representation of the response from Pensio for the request identified by the logId
	 * 
	 * @param string $logId
	 * @param string $message
	 */
	public function logResponse($logId, $message);
}