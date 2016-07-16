<?php
/**
 * @version    SVN: <svn_id>
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

if (JVERSION >= '1.6.0')
{
	require_once JPATH_SITE . '/plugins/payment/adaptive_paypal/adaptive_paypal/helper.php';
}
else
{
	require_once JPATH_SITE . '/plugins/payment/adaptive_paypal/helper.php';
}

$lang = JFactory::getLanguage();
$lang->load('plg_payment_adaptive_paypal', JPATH_ADMINISTRATOR);

/**
 * PlgPaymentAdaptive_Paypal
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class  PlgPaymentAdaptive_Paypal extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();

		// Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus = array(
		'COMPLETED'  => 'C',
		'INCOMPLETE'  => 'P',
		'PROCESSING' => 'P',
		'PENDING' => 'P',
		'CREATED' => 'P',
		'ERROR' => 'E',
		'DENIED' => 'D',
		'FAILED' => 'E',
		'PARTIALLY_REFUNDED' => 'RF',
		'REVERSALERROR' => 'CRV',
		'REFUNDED' => 'RF',
		'REVERSED' => 'RV'
		);

		$this->headers = array(
			"X-PAYPAL-SECURITY-USERID:" . $this->params->get('apiuser'),
			"X-PAYPAL-SECURITY-PASSWORD:" . $this->params->get('apipass'),
			"X-PAYPAL-SECURITY-SIGNATURE:" . $this->params->get('apisign'),
			"X-PAYPAL-REQUEST-DATA-FORMAT:JSON",
			"X-PAYPAL-RESPONSE-DATA-FORMAT:JSON",
			"X-PAYPAL-APPLICATION-ID:" . $this->params->get('apiid')
		);

		$this->envelope = array(
			"errorLanguage" => "en_US",
			"detailLevel" => "ReturnAll"
		);

		$plugin = JPluginHelper::getPlugin('payment', 'adaptive_paypal');
		$params = json_decode($plugin->params);
		$svcs_url = 'https://svcs.sandbox.paypal.com/AdaptivePayments/';
		$this->apiurl = $params->sandbox ? $svcs_url : 'https://svcs.paypal.com/AdaptivePayments/';
		$sbox_url = 'https://www.sandbox.paypal.com/websrc?cmd=_ap-payment&paykey=';
		$this->paypalurl = $params->sandbox ? $sbox_url : 'https://www.paypal.com/websrc?cmd=_ap-payment&paykey=';
	}

	/**
	 * Build Layout path
	 *
	 * @param   string  $layout  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function buildLayoutPath($layout)
	{
		$app = JFactory::getApplication();

		if ($layout == 'recurring')
		{
			$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/tmpl/recurring.php';
		}
		else
		{
			$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/tmpl/default.php';
			$override = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' .
			$this->_type . '/' . $this->_name . '/' . 'recurring.php';
		}

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}
	}

	/**
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $vars    Data from component
	 * @param   string  $layout  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	// Used to Build List of Payment Gateway in the respective Components
	/**
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $config  Plugin config
	 *
	 * @since   2.2
	 *
	 * @return   mixed  return plugin config object
	 */
	public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj       = new stdClass;
		$obj->name = $this->params->get('plugin_name');
		$obj->id   = $this->_name;

		return $obj;
	}

	// Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Paypal

	/**
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $vars  Data from component
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function onTP_GetHTML($vars)
	{
		$plgPaymentAdaptivePaypalHelper = new plgPaymentAdaptivePaypalHelper;
		$vars->action_url = $plgPaymentAdaptivePaypalHelper->buildPaypalUrl();

		// Take this receiver email address from plugin if component not provided it
		if (empty($vars->business))
		{
			$vars->business = $this->params->get('business');
		}

		// If component does not provide cmd
		if (empty($vars->cmd))
		{
			$vars->cmd = '_xclick';
		}

		$html = $this->buildLayout($vars);

		return $html;
	}

	/**
	 * Adds a row for the first time in the db, calls the layout view
	 *
	 * @param   object  $data  Data from component
	 * @param   object  $vars  Component data
	 *
	 * @since   2.2
	 *
	 * @return   object  processeddata
	 */
	public function onTP_ProcessSubmit($data,$vars)
	{
		$adaptiveReceiverList = $vars->adaptiveReceiverList;

		// Take this receiver email address from plugin if component not provided it
		$plgPaymentAdaptivePaypalHelper = new plgPaymentAdaptivePaypalHelper;
		$receiver = array();
		$data = $this->getFormattedReceiver($vars->adaptiveReceiverList);
		$receiver = $data['receiver'];
		$receiverOptions = $data['receiverOptions'];

		// Create the pay request
		$createPacket = array(
			"actionType" => "PAY",
			"currencyCode" => $vars->currency_code,
			"receiverList" => array(
				"receiver" => $receiverOptions
			),
			"returnUrl" => $vars->return,
			"cancelUrl" => $vars->cancel_return,
			"ipnNotificationUrl" => $vars->notify_url,
			"trackingId" => $vars->order_id,
			"requestEnvelope" => $this->envelope,
			"feesPayer" => "PRIMARYRECEIVER"
		);

		// Send packet
		$response = $this->_paypalSend($createPacket, "Pay");

		// Store packet log
		// @params packet response, component name, Item name
		$this->_StorelogBeforePayment($response, $vars->client, $vars->item_name);

		$paykey = $response['payKey'];

		// Set payment detials
		$detailsPacket = array(
			"requestEnvelope" => $this->envelope,
			"payKey" => $response['payKey'],
			"receiverOptions" => $receiver
		);

		$response = $this->_paypalSend($detailsPacket, "SetPaymentOptions");
		$detls = $this->getPaymentOptions($paykey);

		$file = 'AdaptiveLog.txt';

		// The new person to add to the file
		$person = json_encode($_REQUEST);

		// Header to paypal
		header("Location:" . $this->paypalurl . $paykey);
	}

	/**
	 * Make plugin specific receiver format
	 *
	 * @param   array  $receiverList  List
	 *
	 * @since   2.2
	 *
	 * @return   array  data
	 */
	public function getFormattedReceiver($receiverList)
	{
		$receiver = array();
		$receiverOptions = array();

		foreach ($receiverList as $rec)
		{
			$temp['amount'] = round($rec['amount'], 2);
			$temp['email'] = $rec['receiver'];
			$temp['primary'] = $rec['primary'];
			$receiverOptions[] = $temp;
			$emails['email'] = $temp['email'];
			$r = array();
			$r['receiver'] = $emails;
			$receiver[] = $r;
		}

		$data['receiverOptions'] = $receiverOptions;
		$data['receiver'] = $receiver;

		return $data;
	}

	/**
	 * Adds a row for the first time in the db, calls the layout view
	 *
	 * @param   object  $data  Data from component
	 *
	 * @since   2.2
	 *
	 * @return   object  processeddata
	 */
	public function onTP_Processpayment($data)
	{
		/* $verify = plgPaymentAdaptivePaypalHelper::validateIPN($data);
		if (!$verify) { return false; }
		*/

		$payment_status = $this->translateResponse($data['status']);
		$paymentDetails = $this->getTransactionDetails($data);

		$primaryReceiver = 0;

		// Get primary receiver
		if (!empty($paymentDetails['paymentInfoList']['paymentInfo']))
		{
			// For each receiver.
			foreach ($paymentDetails['paymentInfoList']['paymentInfo'] as $recIndex => $rec)
			{
				if ($rec['receiver']['primary'] == 'true')
				{
					$primaryReceiver = $recIndex;
					break;
				}
			}
		}

		$result = array(
		'order_id' => $data['tracking_id'],
		'transaction_id' => $data['pay_key'],
		'action_type' => $data['action_type'],
		'status' => $payment_status,
		'txn_type' => $data['transaction_type'],
		'total_paid_amt' => $paymentDetails['paymentInfoList']['paymentInfo'][$primaryReceiver]['receiver']['amount'],
		'raw_data' => $paymentDetails,
		'error' => $paymentDetails,
		);

		return $result;
	}

	/**
	 * This function transalate the response got from payment getway
	 *
	 * @param   object  $payment_status  payment_status
	 *
	 * @since   2.2
	 *
	 * @return   string  value
	 */
	public function translateResponse($payment_status)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == $payment_status)
			{
				return $value;
			}
		}
	}

	/**
	 * Store log
	 *
	 * @param   array  $data  data.
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if ($log_write == 1)
		{
			$log = plgPaymentAdaptivePaypalHelper::Storelog($this->_name, $data);
		}
	}

	/**
	 * Store log
	 *
	 * @param   array   $data       data.
	 * @param   string  $client     client.
	 * @param   string  $item_name  name.
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function _StorelogBeforePayment($data, $client, $item_name)
	{
		$log = plgPaymentAdaptivePaypalHelper::StorelogBeforePayment($this->_name, $data, $client, $item_name);
	}

	/**
	 * Function _paypalSend
	 *
	 * @param   array   $data  data.
	 * @param   string  $call  call.
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function _paypalSend($data,$call)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiurl . $call);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

		return json_decode(curl_exec($ch), true);
	}

	// Wrapper for getting payment details
	/**
	 * Function getPaymentOptions
	 *
	 * @param   string  $paykey  key.
	 *
	 * @since   2.2
	 * @return  object.
	 */
	public function getPaymentOptions($paykey)
	{
		$packet = array(
			"requestEnvelope" => $this->envelope,
			"payKey" => $paykey
			);

		return $this->_paypalSend($packet, "GetPaymentOptions");
	}

	// Get the complete transaction details
	/**
	 * Function getTransactionDetails
	 *
	 * @param   array  $data  key.
	 *
	 * @since   2.2
	 * @return  object.
	 */
	public function getTransactionDetails($data)
	{
		$detailsPacket = array(
			"payKey" => $data['pay_key'],
			"requestEnvelope" => $this->envelope
		);

		$res = $this->_paypalSend($detailsPacket, 'PaymentDetails');

		return $res;
	}
}
