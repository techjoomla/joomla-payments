<?php
/**
 * @version     SVN: <svn_id>
 * @package     Techjoomla.Libraries
 * @subpackage  Payment.authorizenet
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('tjpayment.payment.payment');
use Omnipay\Common\CreditCard;

/**
 * Payment class for authorizenet payment plugin
 *
 * @since  1.1
 */
class TjAuthorizenet extends TjOmniPayPayment
{
	protected $omniPayGateWayName = 'AuthorizeNet_AIM';

	protected $paymentPluginName = 'authorizenet';

	protected $paymentPluginParams;

	/**
	 * Instantiate the payment object
	 *
	 * @param   array  $config  payment plugin config
	 */
	public function __construct($config = array())
	{
		$data = array();
		$data['omniPayGateWayName'] = $this->omniPayGateWayName;
		$data['paymentPluginName'] = $this->paymentPluginName;

		parent::__construct($data, $config);
	}

	protected function translateResponse($response, $data)
	{
		$responseData = json_decode($response->getTransactionReference());
		$responseCode = $responseData->transId;
		$paymentStatus = $response->getAVSCode();

		/* Response Code - indicates the overall status of the transaction with
		possible values of Approved, Declined, Errored or Held for Review:

		1: Approved
		2: Declined
		3: Error
		4: Action Required (typically used for AFDS transactions that are held for review) */

		switch($response->getResultCode())
		{
			case 1:
			$paymentStatus = "C";
			break;
			
			case 2:
				$paymentStatus = "F";
			break;
			
			case 3:
						$paymentStatus = "F";
			break;
			
			case 4:
						$paymentStatus = "P";
			break;
			}

		$response = array(
		'transaction_id' => $responseCode,
		'order_id' => $data['order_id'],
		'status' => $paymentStatus,
		'total_paid_amt' => $data['amount'],
		'raw_data' => '',
		'error' => '',
		'return' => $data['return']
		);

		return array($response);
	}

	/**
	 * Method to process the payment using OmniPay library
	 *
	 * @param   ARRAY   $post  post data
	 * @param   OBJECT  $vars  payment vars
	 *
	 * @return void
	 */
	public function processPayment($post, $vars)
	{
		$app = JFactory::getApplication();
		$gateway = $this->getGateway();
		$post['amount'] = floatval($post['amount']);

		$card = array();
		$card['amount'] = $post['amount'];
		$card['firstName'] = $post['cardfname'];
		$card['lastName'] = $post['cardlname'];

		$exp = explode("-", $post['cardexp']);

		$card['startMonth'] = "02";
		$card['startYear'] = "2010";
		$card['address1'] = $post['cardaddress1'];
		$card['address2'] = $post['cardaddress2'];
		$card['city'] = $post['cardcity'];
		$card['postcode'] = $post['cardzip'];
		$card['state'] = "maharashtra";
		$card['country'] = "india";
		$card['phone'] = $vars->userInfo['phone'];
		$card['email'] = $vars->userInfo['user_email'];
		$card['number'] = $post['cardnum'];
		$card['expiryMonth'] = $exp[0];
		$card['expiryYear'] = $exp[1];
		$card['cvv'] = $post['cardcvv'];

		$cardData = new CreditCard($card);

		// Register payment success and payment failure URL
		$this->paymentSuccessUrl = $vars->return;
		$this->paymentFailureUrl = $vars->cancel_return;

		try
		{
			$request = $gateway->purchase(
			array(
			'amount' => $post['amount'], 'currency' => $vars->currency_code,
			'card' => $cardData, 'returnUrl' => $vars->return, 'cancelUrl' => $vars->cancel_return,
			'notifyUrl' => $vars->notify_url, 'description' => 'description')
			);

			$request->setTransactionId($vars->order_id);
			$request->setTransactionReference($vars->order_id);

			$response = $request->send();
		}
		catch (\Exception $e)
		{
			$app->redirect($this->paymentFailureUrl);
		}
		//~ echo "<pre>";
//~ print_r($response);die;
		if ($response->isSuccessful())
		{
			// Payment success - for onsite payment gateways
			$response = $this->translateResponse($response, $post);

			return $response;
		}
		elseif ($response->isRedirect())
		{
			
			// For off-site payment gateways
			if ($response->getRedirectMethod() == 'GET')
			{
				$app->redirect($response->getRedirectUrl());
			}
			else
			{
				$redirectUrl = $response->getRedirectUrl();
				$data        = $response->getRedirectData();
				$this->renderRedirectForm($redirectUrl, $data);
				jexit();
			}
		}
		else
		{
			// Payment failure
			$app->redirect($this->paymentFailureUrl);
		}
	}

	/**
	 * Convert payment amount to USD currency in case the currency is not supported by the payment gateway
	 *
	 * @param $amount
	 * @param $currency
	 *
	 * @return float
	 */
	public static function convertAmountToUSD($amount, $currency)
	{
		static $rate = null;

		if ($rate === null)
		{
			$http     = JHttpFactory::getHttp();
			$url      = 'http://download.finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=USD' . $currency . '=X';
			$response = $http->get($url);
			if ($response->code == 200)
			{
				$currencyData = explode(',', $response->body);
				$rate         = floatval($currencyData[1]);
			}
		}

		if ($rate > 0)
		{
			$amount = $amount / $rate;
		}

		return round($amount, 2);
	}
}
