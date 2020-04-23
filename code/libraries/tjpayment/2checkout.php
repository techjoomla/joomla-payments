<?php
/**
 * @version     SVN: <svn_id>
 * @package     Techjoomla.Libraries
 * @subpackage  Payment.paypal
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('techjoomla.payment.payment');

/**
 * Payment class for authorizenet payment plugin
 *
 * @since  1.1
 */
class Tj2checkout extends TjOmniPayPayment
{
	protected $omniPayGateWayName = 'TwoCheckout';

	protected $paymentPluginName = '2checkout';

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
