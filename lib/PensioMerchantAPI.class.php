<?php

if(!defined('PENSIO_API_ROOT'))
{
	define('PENSIO_API_ROOT',__DIR__);
}

require_once(PENSIO_API_ROOT.'/IPensioCommunicationLogger.class.php');
require_once(PENSIO_API_ROOT.'/request/PensioAPITransactionsRequest.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioGetTerminalsResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioGetPaymentResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioLoginResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioCreatePaymentRequestResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioCaptureResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioRefundResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioReleaseResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioReservationResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioCaptureRecurringResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioPreauthRecurringResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIPaymentNatureService.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPICustomerInfo.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPICountryOfOrigin.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIAddress.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIPaymentInfos.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIFunding.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIChargebackEvent.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioAPIChargebackEvents.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioCalculateSurchargeResponse.class.php');
require_once(PENSIO_API_ROOT.'/response/PensioFundingListResponse.class.php');
require_once(PENSIO_API_ROOT.'/http/PensioFOpenBasedHttpUtils.class.php');
require_once(PENSIO_API_ROOT.'/http/PensioCurlBasedHttpUtils.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioMerchantAPIException.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioUnauthorizedAccessException.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioRequestTimeoutException.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioConnectionFailedException.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioInvalidResponseException.class.php');
require_once(PENSIO_API_ROOT.'/exceptions/PensioUnknownMerchantAPIException.class.php');
require_once(PENSIO_API_ROOT.'/ALTAPAY_VERSION.php');

class PensioMerchantAPI
{
	private $baseURL, $username, $password;
	private $connected = false;
	/**
	 * @var IPensioCommunicationLogger
	 */
	private $logger;
	private $httpUtil;

	public function __construct($baseURL, $username, $password, IPensioCommunicationLogger $logger = null, IPensioHttpUtils $httpUtil = null)
	{
		$this->connected = false;
		$this->baseURL = rtrim($baseURL, '/');
		$this->username = $username;
		$this->password = $password;
		$this->logger = $logger;
		
		if(is_null($httpUtil))
		{
			if(function_exists('curl_init'))
			{
				$httpUtil = new PensioCurlBasedHttpUtils();
			}
			else if(ini_get('allow_url_fopen'))
			{
				$httpUtil = new PensioFOpenBasedHttpUtils();
			}
			else
			{
				throw new Exception("Neither allow_url_fopen nor cURL is installed, we cannot communicate with Pensio's Payment Gateway without at least one of them.");
			}
		}
		$this->httpUtil = $httpUtil;
	}

	private function checkConnection()
	{
		if(!$this->connected)
		{
			throw new Exception("Not Connected, invoke login() before using any API calls");
		}
	}
	
	public function isConnected()
	{
		return $this->connected;
	}

	private function maskPan($pan)
	{
		if(strlen($pan) >= 10)
		{
			return  substr($pan, 0, 6).str_repeat('x', strlen($pan) - 10).substr($pan, -4);
		}
		else
		{
			return $pan;
		}
	}

	private function callAPIMethod($method, array $args = array())
	{
		$absoluteUrl = $this->baseURL."/merchant/API/".$method;

		if(!is_null($this->logger))
		{
			$loggedArgs = $args;
			if(isset($loggedArgs['cardnum']))
			{
				$loggedArgs['cardnum'] = $this->maskPan($loggedArgs['cardnum']);
			}
			if(isset($loggedArgs['cvc']))
			{
				$loggedArgs['cvc'] = str_repeat('x', strlen($loggedArgs['cvc']));
			}
			$logId = $this->logger->logRequest($absoluteUrl.'?'.http_build_query($loggedArgs));
		}

		$request = new PensioHttpRequest();
		$request->setUrl($absoluteUrl);
		$request->setParameters($args);
		$request->setUser($this->username);
		$request->setPass($this->password);
		$request->setMethod('POST');
		$request->addHeader('x-altapay-client-version: '.ALTAPAY_VERSION);

		$response = $this->httpUtil->requestURL($request);
		
		if(!is_null($this->logger))
		{
			$this->logger->logResponse($logId, print_r($response, true));
		}

		if($response->getConnectionResult() == PensioHttpResponse::CONNECTION_OKAY)
		{
			if($response->getHttpCode() == 200)
			{
				if(stripos($response->getContentType(), "text/xml") !== false)
				{
					try
					{
						return new SimpleXMLElement($response->getContent());
					}
					catch(Exception $e)
					{
						if($e->getMessage() == 'String could not be parsed as XML')
						{
							throw new PensioInvalidResponseException("Unparsable XML Content in response");
						}
						throw new PensioUnknownMerchantAPIException($e);
					}
				}
				elseif (stripos($response->getContentType(), "text/csv") !== false)
				{
					return $response->getContent();
				}
				else
				{
					throw new PensioInvalidResponseException("Non XML ContentType (was: ".$response->getContentType().")");
				}
			}
			else if($response->getHttpCode() == 401)
			{
				throw new PensioUnauthorizedAccessException($absoluteUrl, $this->username);
			}
			else
			{
				throw new PensioInvalidResponseException("Non HTTP 200 Response: ".$response->getHttpCode());
			}
		}
		else if($response->getConnectionResult() == PensioHttpResponse::CONNECTION_REFUSED)
		{
			throw new PensioConnectionFailedException($absoluteUrl, 'Connection refused');
		}
		else if($response->getConnectionResult() == PensioHttpResponse::CONNECTION_TIMEOUT)
		{
			throw new PensioConnectionFailedException($absoluteUrl, 'Connection timed out');
		}
		else if($response->getConnectionResult() == PensioHttpResponse::CONNECTION_READ_TIMEOUT)
		{
			throw new PensioRequestTimeoutException($absoluteUrl);
		}
		else
		{
			throw new PensioUnknownMerchantAPIException();
		}
	}

	/**
	 * @return PensioFundingListResponse
	 * @throws PensioMerchantAPIException
	 */
	public function getFundingList($page=0)
	{
		$this->checkConnection();

		return new PensioFundingListResponse($this->callAPIMethod('fundingList', array('page'=>$page)));
	}
	
	/**
	 * @return string|boolean
	 * @throws PensioMerchantAPIException
	 */
	public function downloadFundingCSV(PensioAPIFunding $funding)
	{
		$this->checkConnection();

		$request = new PensioHttpRequest();
		$request->setUrl($funding->getDownloadLink());
		$request->setUser($this->username);
		$request->setPass($this->password);
		$request->setMethod('GET');
		
		$response = $this->httpUtil->requestURL($request);
		
		if($response->getHttpCode() == 200)
		{
			return $response->getContent();
		}
		
		return false;
	}

	/**
	 * @return string|boolean
	 * @throws PensioMerchantAPIException
	 */
	public function downloadFundingCSVByLink($downloadLink)
	{
		$this->checkConnection();

		$request = new PensioHttpRequest();

		$request->setUrl($downloadLink);
		$request->setUser($this->username);
		$request->setPass($this->password);
		$request->setMethod('GET');

		$response = $this->httpUtil->requestURL($request);

		if($response->getHttpCode() == 200)
		{
			return $response->getContent();
		}

		return false;
	}
	
	private function reservationInternal(
			  $apiMethod
			, $terminal
			, $shop_orderid
			, $amount
			, $currency
			, $cc_num
			, $cc_expiry_year
			, $cc_expiry_month
			, $credit_card_token
			, $cvc
			, $type
			, $payment_source
			, array $customerInfo
			, array $transaction_info)
	{
		$this->checkConnection();
	
		$args = array(
				'terminal'=>$terminal,
				'shop_orderid'=>$shop_orderid,
				'amount'=>$amount,
				'currency'=>$currency,
				'cvc'=>$cvc,
				'type'=>$type,
				'payment_source'=>$payment_source
		);
		if(!is_null($credit_card_token))
		{
			$args['credit_card_token'] = $credit_card_token;
		}
		else
		{
			$args['cardnum'] = $cc_num;
			$args['emonth'] = $cc_expiry_month;
			$args['eyear'] = $cc_expiry_year;
		}

        if(!is_null($customerInfo) && is_array($customerInfo))
        {
            $this->addCustomerInfo($customerInfo, $args);
        }

        // Not needed when everyone has been upgraded to 20150428
        // ====================================================================
		foreach(array('billing_city', 'billing_region', 'billing_postal', 'billing_country', 'email', 'customer_phone', 'bank_name', 'bank_phone', 'billing_firstname', 'billing_lastname', 'billing_address') as $custField)
		{
			if(isset($customerInfo[$custField]))
			{
				$args[$custField] = $customerInfo[$custField];
			}
		}
        // ====================================================================
		if(count($transaction_info) > 0)
		{
			$args['transaction_info'] = $transaction_info;
		}
	
		return new PensioReservationResponse(
				$this->callAPIMethod(
						$apiMethod,
						$args
				)
		);
	}
	

	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function reservationOfFixedAmount(
		  $terminal
		, $shop_orderid
		, $amount
		, $currency
		, $cc_num
		, $cc_expiry_year
		, $cc_expiry_month
		, $cvc
		, $payment_source
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
				'reservationOfFixedAmountMOTO'
				, $terminal
				, $shop_orderid
				, $amount
				, $currency
				, $cc_num
				, $cc_expiry_year
				, $cc_expiry_month
				, null // $credit_card_token
				, $cvc
				, 'payment'
				, $payment_source
				, $customerInfo
				, $transactionInfo);
	}

	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function reservationOfFixedAmountMOTOWithToken(
		$terminal
		, $shop_orderid
		, $amount
		, $currency
		, $credit_card_token
		, $cvc = null
		, $payment_source = 'moto'
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
				'reservationOfFixedAmountMOTO'
				, $terminal
				, $shop_orderid
				, $amount
				, $currency
				, null
				, null
				, null
				, $credit_card_token
				, $cvc
				, 'payment'
				, $payment_source
				, $customerInfo
				, $transactionInfo);
	}

	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function setupSubscription(
		$terminal
		, $shop_orderid
		, $amount
		, $currency
		, $cc_num
		, $cc_expiry_year
		, $cc_expiry_month
		, $cvc
		, $payment_source
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
				'setupSubscription'
				, $terminal
				, $shop_orderid
				, $amount
				, $currency
				, $cc_num
				, $cc_expiry_year
				, $cc_expiry_month
				, null // $credit_card_token
				, $cvc
				, 'subscription'
				, $payment_source
				, $customerInfo
				, $transactionInfo);		
	}

	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function setupSubscriptionWithToken(
		$terminal
		, $shop_orderid
		, $amount
		, $currency
		, $credit_card_token
		, $cvc = null
		, $payment_source = 'moto'
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
			'setupSubscription'
			, $terminal
			, $shop_orderid
			, $amount
			, $currency
			, null
			, null
			, null
			, $credit_card_token
			, $cvc
			, 'subscription'
			, $payment_source
			, $customerInfo
			, $transactionInfo);
	}
	
	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function verifyCard(
		$terminal
		, $shop_orderid
		, $currency
		, $cc_num
		, $cc_expiry_year
		, $cc_expiry_month
		, $cvc
		, $payment_source
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
				'reservationOfFixedAmountMOTO'
				, $terminal
				, $shop_orderid
				, 1.00
				, $currency
				, $cc_num
				, $cc_expiry_year
				, $cc_expiry_month
				, null // $credit_card_token
				, $cvc
				, 'verifyCard'
				, $payment_source
				, $customerInfo
				, $transactionInfo);		
	}

	/**
	 * @return PensioReservationResponse
	 * @throws PensioMerchantAPIException
	 */
	public function verifyCardWithToken(
		$terminal
		, $shop_orderid
		, $currency
		, $credit_card_token
		, $cvc = null
		, $payment_source = 'moto'
		, array $customerInfo = array()
		, array $transactionInfo = array())
	{
		return $this->reservationInternal(
			'reservationOfFixedAmountMOTO'
			, $terminal
			, $shop_orderid
			, 1.00
			, $currency
			, null
			, null
			, null
			, $credit_card_token
			, $cvc
			, 'verifyCard'
			, $payment_source
			, $customerInfo
			, $transactionInfo);
	}
	
	
	/**
	 * @return PensioCaptureResponse
	 * @throws PensioMerchantAPIException
	 */
	public function captureReservation($paymentId, $amount=null, array $orderLines=array(), $salesTax=null, $reconciliationIdentifier=null, $invoiceNumber=null)
	{
		$this->checkConnection();

		return new PensioCaptureResponse(
			$this->callAPIMethod(
				'captureReservation',
				array(
					'transaction_id'=>$paymentId,
					'amount'=>$amount,
					'orderLines'=>$orderLines,
					'sales_tax'=>$salesTax,
					'reconciliation_identifier'=>$reconciliationIdentifier,
					'invoice_number'=>$invoiceNumber
				)
			)
		);
	}

	/**
	 * @return PensioRefundResponse
	 * @throws PensioMerchantAPIException
	 */
	public function refundCapturedReservation($paymentId, $amount=null, $orderLines=null, $reconciliationIdentifier=null, $allowOverRefund=null, $invoiceNumber=null)
	{
		$this->checkConnection();

		return new PensioRefundResponse(
			$this->callAPIMethod(
				'refundCapturedReservation',
				array(
					'transaction_id'=>$paymentId, 
					'amount'=>$amount,
					'orderLines'=>$orderLines,
					'reconciliation_identifier'=>$reconciliationIdentifier,
					'allow_over_refund'=>$allowOverRefund,
					'invoice_number'=>$invoiceNumber
				)
			)
		);
	}

	/**
	 * @return PensioReleaseResponse
	 * @throws PensioMerchantAPIException
	 */
	public function releaseReservation($paymentId, $amount=null)
	{
		$this->checkConnection();

		return new PensioReleaseResponse(
			$this->callAPIMethod(
				'releaseReservation',
				array(
					'transaction_id'=>$paymentId
				)
			)
		);
	}

	/**
	 * @return PensioGetPaymentResponse
	 * @throws PensioMerchantAPIException
	 */
	public function getPayment($paymentId)
	{
		$this->checkConnection();

		return new PensioGetPaymentResponse($this->callAPIMethod(
			'payments',
			array(
				'transaction'=>$paymentId
			)
		));
	}
	
	/**
	 * @return PensioGetTerminalsResponse
	 * @throws PensioMerchantAPIException
	 */
	public function getTerminals()
	{
		$this->checkConnection();

		return new PensioGetTerminalsResponse($this->callAPIMethod('getTerminals'));
	}

	/**
	 * @return PensioLoginResponse
	 * @throws PensioMerchantAPIException
	 */
	public function login()
	{
		$this->connected = false;
		
		$response = new PensioLoginResponse($this->callAPIMethod('login'));
		
		if($response->getErrorCode() === '0')
		{
			$this->connected = true;
		}
		
		return $response;
	}
	
	/**
	 * @return PensioCreatePaymentRequestResponse
	 * @throws PensioMerchantAPIException
	 */
	public function createPaymentRequest($terminal,
			$orderid,
			$amount,
			$currencyCode,
			$paymentType,
			$customerInfo = null,
			$cookie = null,
			$language = null,
			array $config = array(),
			array $transaction_info = array(),
			array $orderLines = array(),
			$accountOffer = false,
			$ccToken = null
		)
	{
		$args = array(
			'terminal'=>$terminal,
			'shop_orderid'=>$orderid,
			'amount'=>$amount,
			'currency'=>$currencyCode,
			'type'=>$paymentType
		);
		
		if(!is_null($customerInfo) && is_array($customerInfo))
		{
            $this->addCustomerInfo($customerInfo, $args);
		}
		
		if(!is_null($cookie))
		{
			$args['cookie'] = $cookie;
		}  
		if(!is_null($language))
		{
			$args['language'] = $language;
		}
		if(count($transaction_info) > 0)
		{
			$args['transaction_info'] = $transaction_info;
		}
		if(count($orderLines) > 0)
		{
			$args['orderLines'] = $orderLines;
		}
		if(in_array($accountOffer, array("required", "disabled")))
		{
			$args['account_offer'] = $accountOffer;
		}
        if(!is_null($ccToken))
        {
            $args['ccToken'] = $ccToken;
        }

		$args['config'] = $config;
		
		return new PensioCreatePaymentRequestResponse($this->callAPIMethod('createPaymentRequest', $args));
	}
	
	/**
	 * @return PensioCaptureRecurringResponse
	 * @deprecated - use chargeSubscription instead.
	 * @throws PensioMerchantAPIException
	 */
	public function captureRecurring($subscriptionId, $amount=null)
	{
		return $this->chargeSubscription($subscriptionId, $amount);
	}	
		
	/**
	 * @return PensioCaptureRecurringResponse
	 * @throws PensioMerchantAPIException
	 */
	public function chargeSubscription($subscriptionId, $amount=null)
	{
		$this->checkConnection();

		return new PensioCaptureRecurringResponse(
			$this->callAPIMethod(
				'chargeSubscription',
				array(
					'transaction_id'=>$subscriptionId, 
					'amount'=>$amount,
				)
			)
		);
	}
	
	/**
	 * @return PensioPreauthRecurringResponse
	 * @deprecated - use reserveSubscriptionCharge instead
	 * @throws PensioMerchantAPIException
	 */
	public function preauthRecurring($subscriptionId, $amount=null)
	{
		return $this->reserveSubscriptionCharge($subscriptionId, $amount);
	}
	
	
	/**
	 * @return PensioPreauthRecurringResponse
	 * @throws PensioMerchantAPIException
	 */
	public function reserveSubscriptionCharge($subscriptionId, $amount=null)
	{
		$this->checkConnection();

		return new PensioPreauthRecurringResponse(
			$this->callAPIMethod(
				'reserveSubscriptionCharge',
				array(
					'transaction_id'=>$subscriptionId, 
					'amount'=>$amount,
				)
			)
		);
	}

	/**
	 * @return PensioCalculateSurchargeResponse
	 * @throws PensioMerchantAPIException
	 */
	public function calculateSurcharge($terminal, $cardToken, $amount, $currency)
	{
		$this->checkConnection();
	
		return new PensioCalculateSurchargeResponse(
				$this->callAPIMethod(
						'calculateSurcharge',
						array(
								'terminal'=>$terminal,
								'credit_card_token'=>$cardToken,
								'amount'=>$amount,
								'currency'=>$currency,
						)
				)
		);
	}
	
	/**
	 * @return PensioCalculateSurchargeResponse
	 * @throws PensioMerchantAPIException
	 */
	public function calculateSurchargeForSubscription($subscriptionId, $amount)
	{
		$this->checkConnection();
	
		return new PensioCalculateSurchargeResponse(
				$this->callAPIMethod(
						'calculateSurcharge',
						array(
								'payment_id'=>$subscriptionId,
								'amount'=>$amount,
						)
				)
		);
	}

	/**
	 * @return string|boolean
	 * @throws PensioMerchantAPIException
	 */
	public function getCustomReport($args)
	{
		$this->checkConnection();
		$response = $this->callAPIMethod('getCustomReport', $args);
		return $response;
	}

	/**
	 * @return string|boolean
	 * @throws PensioMerchantAPIException
	 */
	public function getTransactions(PensioAPITransactionsRequest $transactionsRequest)
	{
		$this->checkConnection();
		return $this->callAPIMethod('transactions', $transactionsRequest->asArray());
	}

    /**
     * @param $customerInfo
     * @param $args
     * @throws PensioMerchantAPIException
     */
    private function addCustomerInfo($customerInfo, &$args)
    {
        $errors = array();

        foreach ($customerInfo as $customerInfoKey => $customerInfoValue) {
            if (is_array($customerInfo[$customerInfoKey])) {
                $errors[] = "customer_info[$customerInfoKey] is not expected to be an array";
            }
        }
        if (count($errors) > 0) {
            throw new PensioMerchantAPIException("Failed to create customer_info variable: \n" . print_r($errors, true));
        }
        $args['customer_info'] = $customerInfo;
    }
}
