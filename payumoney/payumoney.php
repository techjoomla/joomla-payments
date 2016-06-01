<?php
/**
 * @copyright  Copyright (c) 2009 - 2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

require_once JPATH_SITE . '/plugins/payment/payumoney/payumoney/helper.php';

$lang = JFactory::getLanguage();
$lang->load('plg_payment_payumoney', JPATH_ADMINISTRATOR);

/**
 * PlgPaymentPayuMoney
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentPayuMoney extends JPlugin
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

		// Define Payment Status codes in payu  And Respective Alias in Framework
		$this->responseStatus = array(
			'success' => 'C',
			'pending' => 'P',
			'failure' => 'E'
		);
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
		$app       = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/tmpl/default.php';

		$override  = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' .
		$this->_type . '/' . $this->_name . '/' . $layout . '.php';

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
	 * @param   object  $vars    Data from component
	 * @param   string  $layout  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
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
		$plgPaymentPayuMoneyHelper = new plgPaymentPayuMoneyHelper;
		$vars->action_url          = $plgPaymentPayuMoneyHelper->buildPayuMoneyUrl();
		$vars->key                 = $this->params->get('key');
		$vars->salt                = $this->params->get('salt');

		// Fomating on data
		$this->preFormatingData($vars);

		$html = $this->buildLayout($vars);

		return $html;
	}

	/**
	 * Adds a row for the first time in the db, calls the layout view
	 *
	 * @param   object  $data  Data from component
	 * @param   Array   $vars  Data from component
	 *
	 * @since   2.2
	 *
	 * @return   object  processeddata
	 */
	public function onTP_Processpayment($data, $vars = array())
	{
		$isValid       = true;
		$error         = array();
		$error['code'] = '';
		$error['desc'] = '';

		// Compare response order id and send order id in notify URL
		$res_orderid = '';

		if ($isValid)
		{
			$res_orderid = $data['udf1'];

			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$isValid       = false;
				$error['desc'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}

		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $data['amount'];
				$epsilon      = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					// Change response status to ERROR FOR AMOUNT ONLY
					$data['status'] = 'failure';
					$isValid        = false;
					$error['desc']  = "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}

		$data['status'] = $this->translateResponse($data['status']);

		// Error Handling
		$error         = array();

		// @TODO change these $data indexes afterwards
		$error['code'] = $data['unmappedstatus'];
		$error['desc'] = (isset($data['field9']) ? $data['field9'] : '');

		$result = array(
			'order_id' => $data['udf1'],
			'transaction_id' => $data['mihpayid'],
			'buyer_email' => $data['email'],
			'status' => $data['status'],
			'txn_type' => $data['mode'],
			'total_paid_amt' => $data['amount'],
			'raw_data' => $data,
			'error' => $error
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
			$plgPaymentPayuMoneyHelper = new plgPaymentPayuMoneyHelper;
			$log                       = $plgPaymentPayuMoneyHelper->Storelog($this->_name, $data);
		}
	}

	/**
	 * The formated data
	 *
	 * @param   Array  $vars  Vars array
	 *
	 * @return  Object  $vars formatted object
	 */
	public function preFormatingData($vars)
	{
		foreach ($vars as $key => $value)
		{
			if (!is_array($value))
			{
				$vars->$key = trim($value);

				if ($key == 'amount')
				{
					$vars->$key = ceil($value);
				}
			}
		}
	}
}
