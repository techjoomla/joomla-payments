<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.plugin.plugin');
require_once dirname(__FILE__) . '/authorizenet/helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_authorizenet', JPATH_ADMINISTRATOR);

/**
 * PlgpaymentAuthorizenet
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgpaymentAuthorizenet extends JPlugin
{
	private $payment_gateway = 'payment_authorizenet';

	private $log = null;

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

		// Define Payment Status codes in Authorise  And Respective Alias in Framework
		// 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review, ERROR=if amount mismatch
		$this->responseStatus = array(
			'1' => 'C',
			'2' => 'D',
			'3' => 'E',
			'4' => 'UR',
			'ERROR' => 'E'
		);
		$this->login_id = $this->params->get('login_id', '1');
		$this->tran_key = $this->params->get('tran_key', '1');
	}

	/**
	 * buildLayoutPath
	 *
	 * @param   string  $layout  layout
	 *
	 * @since   2.2
	 *
	 * @return   string  layout
	 */
	public function buildLayoutPath($layout = "default")
	{
		if (empty($layout))
		{
			$layout = "default";
		$app       = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout . '.php';
		$override  = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/'
		. $this->_type . '/' . $this->_name . '/' . $layout . '.php';
		}

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return $core_file;
		}
	}

	/**
	 * buildLayout
	 *
	 * @param   string  $vars    vars
	 *
	 * @param   string  $layout  layout
	 *
	 * @since   2.2
	 *
	 * @return   string  vars
	 */
	public function buildLayout($vars, $layout = 'default')
	{
		if (!empty($vars->bootstrapVersion))
		{
			// BootstrapVersion will contain bs3 for bootstrap3 version
			$newLayout = $layout . "_" . $vars->bootstrapVersion;
			$core_file = dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $newLayout . '.php';

			if (JFile::exists($core_file))
			{
				$layout = $newLayout;
			}
		}

		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * getParamResult
	 *
	 * @param   string  $name     name
	 *
	 * @param   string  $default  default
	 *
	 * @since   2.2
	 *
	 * @return   string  name
	 */
	public function getParamResult($name, $default = '')
	{
		$sandbox_param = "sandbox_$name";
		$sb_value      = $this->params->get($sandbox_param);

		if ($this->params->get('sandbox') && !empty($sb_value))
		{
			$param = $this->params->get($sandbox_param, $default);
		}
		else
		{
			$param = $this->params->get($name, $default);
		}

		return $param;
	}

	/**
	 * onTP_GetInfo
	 *
	 * @param   string  $config  config
	 *
	 * @since   2.2
	 *
	 * @return   string  config
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

	/**
	 * onTP_GetHTML
	 *
	 * @param   string  $vars  array
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function onTP_GetHTML($vars)
	{
		if (!empty($vars->payment_type) and $vars->payment_type != '')
		{
			$payment_type = $vars->payment_type;
		}
		else
		{
			$payment_type = '';
		}

		$html = $this->buildLayout($vars, $payment_type);

		return $html;
	}

	/**
	 * onTP_ProcessSubmit
	 *
	 * @param   object  $data  Data
	 * @param   string  $vars  array
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function onTP_ProcessSubmit($data, $vars)
	{
		$submitVaues['order_id']              = $vars->order_id;
		$submitVaues['user_id']               = $vars->user_id;
		$submitVaues['return']                = $vars->return;
		$submitVaues['amount']                = $vars->amount;
		$submitVaues['plugin_payment_method'] = 'onsite';
		$submitVaues['cardfname']             = $data['cardfname'];
		$submitVaues['cardlname']             = $data['cardlname'];
		$submitVaues['cardaddress1']          = $data['cardaddress1'];
		$submitVaues['cardaddress2']          = $data['cardaddress2'];
		$submitVaues['cardcity']              = $data['cardcity'];
		$submitVaues['cardstate']             = $data['cardstate'];
		$submitVaues['cardzip']               = $data['cardzip'];
		$submitVaues['cardcountry']           = $data['cardcountry'];
		$submitVaues['email']                 = $data['email'];
		$submitVaues['cardnum']               = $data['cardnum'];
		$submitVaues['cardexp']               = $data['cardexp'];
		$submitVaues['cardcvv']               = $data['cardcvv'];

		/* for onsite plugin set the post data into session and redirect to the notify URL */
		$session = JFactory::getSession();
		$session->set('payment_submitpost', $submitVaues);
		JFactory::getApplication()->redirect($vars->url);
	}

	/**
	 * onTP_Processpayment
	 *
	 * @param   object  $data  Data
	 * @param   string  $vars  array
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function onTP_Processpayment($data, $vars = array())
	{
		$isValid       = true;
		$error         = array();
		$error['code'] = '';
		$error['desc'] = '';

		if (!empty($data['payment_type']) && $data['payment_type'] == "recurring")
		{
			$response = plgpaymentAuthorizenet::onTP_Processpayment_recurring($data);

			return $response;
		}

		$authnet_values = array(
			"login" => $this->login_id,
			"tran_key" => $this->tran_key,
			"version" => "3.1",
			"delim_char" => "|",
			"delim_data" => "TRUE",
			"type" => "AUTH_CAPTURE",
			"method" => "CC",
			"relay_response" => "FALSE",
			"card_num" => $data['cardnum'],
			"card_code" => $data['cardcvv'],
			"exp_date" => $data['cardexp'],
			"description" => "",
			"amount" => $data['amount'],
			"first_name" => $data['cardfname'],
			"last_name" => $data['cardlname'],
			"address" => $data['cardaddress1'],
			"city" => $data['cardcity'],
			"state" => $data['cardstate'],
			"zip" => $data['cardzip'],
			"country" => $data['cardcountry'],
			"cust_id" => $data['user_id'],
			"email" => $data['email'],
			"invoice_num" => $vars->order_id,
			"description" => $vars->item_name
		);

		require_once 'authorizenet/lib/AuthorizeNet.php';
		$sale = new AuthorizeNetAIM($this->login_id, $this->tran_key);

		// Check sandbox or live
		$plgPaymentAuthorizenetHelper = new plgPaymentAuthorizenetHelper;
		$sandbox                      = $plgPaymentAuthorizenetHelper->isSandboxEnabled();
		$sale->setSandbox($sandbox);

		$sale->setFields($authnet_values);

		$allresp = $sale->authorizeAndCapture();

		if ($allresp->approved)
		{
			// Echo "Sale successful!";
		}
		else
		{
			$error['desc'] = $allresp->response_reason_text;
		}

		// Print_r($allresp);die;

		// Compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];

		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$isValid = false;
				$error['desc'] .= " ORDER_MISMATCH" . "Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}
		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $allresp->amount;
				$epsilon      = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					// Change response status to ERROR FOR AMOUNT ONLY
					$app = JFactory::getApplication()->input;
					$app->enqueuemessage($allresp->error_message, 'error');

					$isValid = false;
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}

		// TRANSLET PAYMENT RESPONSE
		$payment_status = $this->translateResponse($allresp->response_code);

		$transaction_id = $allresp->transaction_id;

		$result = array(
			'transaction_id' => $transaction_id,
			'order_id' => $data['order_id'],
			'status' => $payment_status,
			'total_paid_amt' => $allresp->amount,
			'raw_data' => $allresp,
			'error' => $error,
			'return' => $data['return']
		);

		return $result;
	}

	/**
	 * onTP_Processpayment_recurring.
	 *
	 * @param   object  $data  data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */

	public function onTP_Processpayment_recurring($data)
	{
		$order_id = $data['order_id'];

		require_once dirname(__FILE__) . '/authorizenet/lib/AuthorizeNet.php';

		$auth_net_login_id = $this->params->get('login_id', '1');
		$auth_net_tran_key = $this->params->get('tran_key', '1');

		$plgPaymentAuthorizenetHelper = new plgPaymentAuthorizenetHelper;
		$auth_net_url                 = $plgPaymentAuthorizenetHelper->buildAuthorizenetUrl();
		$auth_net_url;

		// Display additional information to track down problems
		$DEBUGGING     = 1;

		// Set the testing flag so that transactions are not live

		$TESTING       = 1;

		// Number of transactions to post if soft errors occur
		$ERROR_RETRIES = 2;

		$exp_date               = explode('-', $data['expirationDate']);
		$data['expirationDate'] = $exp_date[1] . "-" . $exp_date[0];

		define("AUTHORIZENET_API_LOGIN_ID", $auth_net_login_id);
		define("AUTHORIZENET_TRANSACTION_KEY", $auth_net_tran_key);

		$subscription = new AuthorizeNet_Subscription;

		$subscription->name               = $data['sub_name'];
		$subscription->intervalLength     = $data['intervalLength'];
		$subscription->intervalUnit       = $data['intervalUnit'];
		$subscription->startDate          = $data['startDate'];
		$subscription->totalOccurrences   = $data['totalOccurrences'];
		$subscription->amount             = $data['amount'];
		$subscription->orderInvoiceNumber = $data['order_id'];
		$subscription->creditCardCardNumber     = $data['cardNumber'];
		$subscription->creditCardExpirationDate = $data['expirationDate'];
		$subscription->creditCardCardCode       = $data['cardcode'];
		$subscription->billToFirstName          = $data['firstName'];
		$subscription->billToLastName           = $data['lastName'];
		$subscription->billToAddress            = $data['cardaddress1'];
		$subscription->billToCity               = $data['cardcity'];
		$subscription->billToState              = $data['cardstate'];
		$subscription->billToZip                = $data['cardzip'];

		// Create the subscription.
		$request = new AuthorizeNetARB;
		$refId   = $subscription->orderInvoiceNumber;
		$request->setRefId($refId);
		$testmode = $this->params->get('sandbox', '1');

		if ($testmode == 0)
		{
			// Turn OFF sandbox
			$request->setSandbox(false);
		}
		else
		{
			// Turn ON sandbox
			$request->setSandbox(true);
		}

		$response = $request->createSubscription($subscription);
		$subscription_id = $response->getSubscriptionId();
		$error = "";

		if ($response->xml->messages->resultCode != 'Ok')
		{
			$payment_status = "P";

			$error = JText::_('AUTH_SUB_FAIL') . $response->xml->messages->message->text;
		}
		else
		{
			$payment_status = "C";
			$success        = JText::_('AUTH_SUB_SUCCESS') . $subscription_id;
		}

		$result = array(
			'transaction_id' => $refId,
			'subscription_id' => $subscription_id,
			'order_id' => $data['order_id'],
			'status' => $payment_status,
			'total_paid_amt' => $data['amount'],
			'raw_data' => $response,
			'payment_type' => 'recurring',
			'error' => $error,
			'success' => $success,
			'return' => $data['return']
		);

		return $result;
	}

	/**
	 * confirm_recurring_payment_Update.
	 *
	 * @param   object  $json  json
	 *
	 * @since   2.2
	 *
	 * @return   string  json
	 */
	public function confirm_recurring_payment_Update($json)
	{
		$db             = JFactory::getDBO();
		$data           = json_decode($json, true);
		$payment_status = plgpaymentAuthorizenet::translateResponse($data['x_response_code']);

		$result = array(
			'transaction_id' => $data['x_trans_id'],
			'subscription_id' => $data['x_subscription_id'],
			'order_id' => $data['x_trans_id'],
			'status' => $payment_status,
			'total_paid_amt' => $data['x_amount'],
			'raw_data' => $json,
			'pg_plugin' => 'authorizenet',
			'payment_type' => 'recurring',
			'payment_number' => $data['x_subscription_paynum'],
			'success' => 1,
			'return' => ''
		);

		return $result;
	}

	/**
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $data  Data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function cancelsubscription($data)
	{
		$subid   = $data['0'];
		$id      = $data['1'];
		$gateway = $data['2'];
		$ad_id   = $data['3'];
		$db      = JFactory::getDBO();

		if ($subid)
		{
			require_once dirname(__FILE__) . '/authorizenet/lib/AuthorizeNet.php';
			$auth_net_login_id = $this->params->get('login_id', '1');
			$auth_net_tran_key = $this->params->get('tran_key', '1');

			$plgPaymentAuthorizenetHelper = new plgPaymentAuthorizenetHelper;
			$auth_net_url                 = $plgPaymentAuthorizenetHelper->buildAuthorizenetUrl();
			define("AUTHORIZENET_API_LOGIN_ID", $auth_net_login_id);
			define("AUTHORIZENET_TRANSACTION_KEY", $auth_net_tran_key);

			$refId        = $id;

			// Cancel the subscription.
			$cancellation = new AuthorizeNetARB;
			$cancellation->setRefId($refId);
			$response = $cancellation->cancelSubscription($subid);

			if ($response->xml->messages->resultCode != 'Ok')
			{
				$payment_status = "P";
				$error          = JText::_('AUTH_SUB_CANCEL_FAIL') . $response->xml->messages->message->text;
			}
			else
			{
				$payment_status = "C";
				$success        = JText::_('AUTH_SUB_CANCEL_SUCCESS');
				$paymentdata    = new stdClass;
			}

			$result = array(

				'transaction_id' => $refId,
				'subscription_id' => $subid,
				'order_id' => $data['order_id'],
				'status' => $payment_status,
				'total_paid_amt' => $data['amount'],
				'raw_data' => $response,
				'payment_type' => 'recurring',
				'error' => $error,
				'success' => $success,
				'return' => $data['return']
			);

			return $result;
		}
	}

	/**
	 * translateResponse
	 *
	 * @param   object  $payment_status  payment_status
	 *
	 * @since   2.2
	 *
	 * @return   string  payment_status
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
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $data  Data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if ($log_write == 1)
		{
			$plgPaymentAuthorizenetHelper = new plgPaymentAuthorizenetHelper;
			$log                          = $plgPaymentAuthorizenetHelper->Storelog($this->_name, $data);
		}
	}
}
