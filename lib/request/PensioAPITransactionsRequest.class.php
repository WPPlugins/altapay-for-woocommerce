<?php

class PensioAPITransactionsRequest
{
	private $shop;
	private $terminal;
	private $transaction;
	private $transactionId;
	private $shopOrderId;
	private $paymentStatus;
	private $reconciliationIdentifier;
	private $acquirerReconciliationIdentifier;

	public function getShop()
	{
		return $this->shop;
	}

	public function setShop($shop)
	{
		$this->shop = $shop;
	}

	public function getTerminal()
	{
		return $this->terminal;
	}

	public function setTerminal($terminal)
	{
		$this->terminal = $terminal;
	}

	public function getTransaction()
	{
		return $this->transaction;
	}

	public function setTransaction($transaction)
	{
		$this->transaction = $transaction;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
	}

	public function getShopOrderId()
	{
		return $this->shopOrderId;
	}

	public function setShopOrderId($shopOrderId)
	{
		$this->shopOrderId = $shopOrderId;
	}

	public function getPaymentStatus()
	{
		return $this->paymentStatus;
	}

	public function setPaymentStatus($paymentStatus)
	{
		$this->paymentStatus = $paymentStatus;
	}

	public function getReconciliationIdentifier()
	{
		return $this->reconciliationIdentifier;
	}

	public function setReconciliationIdentifier($reconciliationIdentifier)
	{
		$this->reconciliationIdentifier = $reconciliationIdentifier;
	}

	public function getAcquirerReconciliationIdentifier()
	{
		return $this->acquirerReconciliationIdentifier;
	}

	public function setAcquirerReconciliationIdentifier($acquirerReconciliationIdentifier)
	{
		$this->acquirerReconciliationIdentifier = $acquirerReconciliationIdentifier;
	}

	public function asArray()
	{
		$array = array();
		if (!is_null($this->shop))
		{
			$array['shop'] = $this->shop;
		}
		if (!is_null($this->terminal))
		{
			$array['terminal'] = $this->terminal;
		}
		if (!is_null($this->transaction))
		{
			$array['transaction'] = $this->transaction;
		}
		if (!is_null($this->transactionId))
		{
			$array['transaction_id'] = $this->transactionId;
		}
		if (!is_null($this->shopOrderId))
		{
			$array['shop_orderid'] = $this->shopOrderId;
		}
		if (!is_null($this->paymentStatus))
		{
			$array['payment_status'] = $this->paymentStatus;
		}
		if (!is_null($this->reconciliationIdentifier))
		{
			$array['reconciliation_identifier'] = $this->reconciliationIdentifier;
		}
		if (!is_null($this->acquirerReconciliationIdentifier))
		{
			$array['acquirer_reconciliation_identifier'] = $this->acquirerReconciliationIdentifier;
		}

		return $array;
	}
}
