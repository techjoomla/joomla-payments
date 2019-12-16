<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.plugin.plugin');
require_once JPATH_SITE . '/plugins/payment/paypalpro/paypalpro/helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_paypalpro', JPATH_ADMINISTRATOR);

/**
 * Plgpaymentpaypalpro
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class Plgpaymentpaypalpro extends JPlugin
{
	protected $_payment_gateway = 'payment_paypalpro';

	protected $_log = null;

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
		/*1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review*/
		$this->responseStatus = array(
			'Success' => 'C',
			'SuccessWithWarning' => 'C',
			'FailureWithWarning' => 'X',
			'Failure' => 'X',
			'ERROR' => 'E'
		);
		$this->login_id       = $this->params->get('login_id', '1');
		$this->tran_key       = $this->params->get('tran_key', '1');
	}

	/**
	 * buildLayoutPath
	 *
	 * @param   string  $layout  layout
	 *
	 * @since   2.2
	 *
	 * @return   string  file path
	 */
	public function buildLayoutPath($layout)
	{
		$app       = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/tmpl/form.php';
		$override  = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

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
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   array   $vars    array
	 * @param   string  $layout  layout
	 *
	 * @since   2.2
	 *
	 * @return   html
	 */
	public function buildLayout($vars, $layout = 'default')
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Get Param Value
	 *
	 * @param   string  $name     Name
	 * @param   string  $default  Default Value
	 *
	 * @since   2.2
	 *
	 * @return  array
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
		$html = $this->buildLayout($vars);

		return $html;
	}

	/**
	 * onTP_Processpayment
	 *
	 * @param   object  $data  Data
	 * @param   object  $vars  array
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function onTP_Processpayment($data, $vars)
	{
		$isValid                   = true;
		$error                     = array();
		$error['code']             = '';
		$error['desc']             = '';
		$plgPaymentpaypalproHelper = new plgPaymentpaypalproHelper;
		$action_url                = $plgPaymentpaypalproHelper->buildpaypalproUrl();

		if ($data['recurring_frequency'] == 'QUARTERLY')
		{
			$BILLINGFREQUENCY = 3;
			$BILLINGPERIOD    = 'MONTH';
		}
		else
		{
			$BILLINGFREQUENCY = $data['recurring_count'];
			$BILLINGPERIOD    = ucfirst(strtolower($data['recurring_frequency']));
		}

		$exp_month       = str_pad($data['expire_month'], 2, "0", STR_PAD_LEFT);
		$data['cardexp'] = $exp_month . $data['expire_year'];

		if ($data['is_recurring'])
		{
			$pro_values = array(
				"METHOD" => 'CreateRecurringPaymentsProfile',
				"AMT" => $data['chargetotal'],
				"CURRENCYCODE" => $data['currency_code'],
				"CREDITCARDTYPE" => $data['credit_card_type'],
				"ACCT" => $data['cardnum'],
				"CVV2" => $data['cardcsc'],
				"EXPDATE" => $data['cardexp'],
				"COUNTRYCODE" => $data['cardcountry'],
				"PROFILESTARTDATE" => gmdate("Y-m-d\TH:i:s\Z"),
				"BILLINGFREQUENCY" => $BILLINGFREQUENCY,
				"BILLINGPERIOD" => $BILLINGPERIOD,
				"DESC" => "recurring billing",
				"VERSION" => "65.0",
				"VERSION" => "104.0",
				"USER" => $this->params->get('pro_api_username'),
				"PWD" => $this->params->get('pro_api_password'),
				"SIGNATURE" => $this->params->get('pro_api_signature')
			);
		}
		else
		{
			$pro_values = array(
				"METHOD" => 'DoDirectPayment',
				"VERSION" => "65.0",
				"USER" => $this->params->get('pro_api_username'),
				"PWD" => $this->params->get('pro_api_password'),
				"SIGNATURE" => $this->params->get('pro_api_signature'),
				"PAYMENTACTION" => "Sale",
				"IPADDRESS" => $_SERVER['REMOTE_ADDR'],
				"AMT" => $data['chargetotal'],
				"CURRENCYCODE" => $data['currency_code'],
				"CREDITCARDTYPE" => $data['credit_card_type'],
				"ACCT" => $data['cardnum'],
				"EXPDATE" => $data['cardexp'],
				"CVV2" => $data['cardcsc'],
				"FIRSTNAME" => $data['cardfname'],
				"LASTNAME" => $data['cardlname'],
				"STREET" => $data['cardaddress1'],
				"CITY" => $data['cardcity'],
				"STATE" => $data['cardstate'],
				"ZIP" => $data['cardzip'],
				"COUNTRYCODE" => $data['cardcountry'],
				"INVNUM" => $data['order_id']
			);
		}

		$fields = "";
		foreach ($pro_values as $key => $value)
			$fields .= "$key=" . urlencode($value) . "&";

		// Call to curl
		$ch = curl_init($action_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, "& "));

		// Execute post and get results
		$resp = curl_exec($ch);

		// Call to curl
		curl_close($ch);

		$allresp = explode('&', $resp);

		foreach ($allresp as $r)
		{
			$res                = explode('=', $r);
			$final_res[$res[0]] = urldecode($res[1]);
		}

		JFactory::getApplication()->enqueueMessage('ACK Response : ' . $final_res['ACK']);

		if ($final_res['ACK'] == 'Success')
		{
			JFactory::getApplication()->enqueueMessage('Recurring payments profile created Successfully.');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage('Unable to create Recurring Payments Profile');
		}

		$error['code'] .= $final_res['L_ERRORCODE0'];
		$error['desc'] .= $final_res['L_LONGMESSAGE0'];

		// Compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];


		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$trxnstatus = 'ERROR';
				$isValid    = false;
				$error['desc'] .= "ORDER_MISMATCH " . " Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}

		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $final_res['AMT'];
				$epsilon      = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					// Change response status to ERROR FOR AMOUNT ONLY
					$trxnstatus = 'ERROR';
					$isValid    = false;
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}

		// Translate response
		if (!empty($trxnstatus))
		{
			$payment_status = $this->translateResponse($trxnstatus);
		}
		else
		{
			$payment_status = $this->translateResponse($final_res['ACK']);
		}

		$transaction_id = $final_res['TRANSACTIONID'];

		$result = array(
			'transaction_id' => $transaction_id,
			'order_id' => $data['order_id'],
			'status' => $payment_status,
			'total_paid_amt' => $final_res['AMT'],
			'raw_data' => $resp,
			'error' => $error,
			'return' => $data['return']
		);

		return $result;
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
			$log = plgPaymentPaypalproHelper::Storelog($this->_name, $data);
		}
	}
}
