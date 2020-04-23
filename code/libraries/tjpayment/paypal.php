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

/**
 * Payment class for authorizenet payment plugin
 *
 * @since  1.1
 */
class TjPayPal extends TjOmniPayPayment
{
	protected $omniPayGateWayName = 'PayPal_Express';

	protected $paymentPluginName = 'paypal';

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
		
		$this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

		parent::__construct($data, $config);
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

		
		$gateway->setUsername('amit_u@techjoomla.com');
        $gateway->setPassword('Amittechjoomla@123');
        $gateway->setTestMode(true);
        $gateway->setSignature('tLWUfZU9Np/7qgPqWF1LMIWjY1s=');
		
		
		
		$post['amount'] = floatval($post['amount']);

		$card = array();
		$card['amount'] = $post['amount'];
		$card['firstName'] = $post['cardfname'];
		$card['lastName'] = $post['cardlname'];

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
					echo "<pre>";
print_r($e);die;
			$app->redirect($this->paymentFailureUrl);
		}
		echo "<pre>";
print_r($response);die;
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

	protected function translateResponse($response, $data)
	{
		$responseData = json_decode($response->getTransactionReference());
		$responseCode = $responseData->transId;
		$paymentStatus = $response->getAVSCode();

		if ($paymentStatus == 'Y')
		{
			$paymentStatus = "C";
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

		return $response;
	}
}
