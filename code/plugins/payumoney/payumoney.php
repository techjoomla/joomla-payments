<?php
/**
 * @package     Joomla_Payments
 * @subpackage  PayuMoney
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\String\StringHelper;

require_once JPATH_SITE . '/plugins/payment/payumoney/payumoney/helper.php';

$lang = Factory::getLanguage();
$lang->load('plg_payment_payumoney', JPATH_ADMINISTRATOR);

/**
 * PlgPaymentPayuMoney
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentPayuMoney extends CMSPlugin
{
	/**
	 * Supported payumoney payment statuses
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	private $responseStatus = array(
			'success' => 'C',
			'pending' => 'P',
			'failure' => 'E'
	);

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
		$app       = Factory::getApplication();
		$coreFile = dirname(__FILE__) . '/' . $this->_name . '/tmpl/default.php';

		$override  = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' .
				$this->_type . '/' . $this->_name . '/' . $layout . '.php';

				if (File::exists($override))
				{
					return $override;
				}
				else
				{
					return $coreFile;
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
		$resOrderid = '';

		if ($isValid)
		{
			$resOrderid = $data['udf1'];

			if (!empty($vars) && $resOrderid != $vars->order_id)
			{
				$isValid       = false;
				$error['desc'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $resOrderid;
			}
		}

		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$orderAmount = (float) $vars->amount;
				$retrunamount = (float) $data['amount'];
				$epsilon      = 0.01;

				if (($orderAmount - $retrunamount) > $epsilon)
				{
					// Change response status to ERROR FOR AMOUNT ONLY
					$data['status'] = 'failure';
					$isValid        = false;
					$error['desc']  = "ORDER_AMOUNT_MISTMATCH - order amount= " . $orderAmount . ' response order amount = ' . $retrunamount;
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
	 * @param   object  $paymentStatus  payment_status
	 *
	 * @since   2.2
	 *
	 * @return   string  value
	 */
	public function translateResponse($paymentStatus)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == StringHelper::strtolower($paymentStatus))
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
		$logWrite = $this->params->get('log_write', '0');

		if ($logWrite == 1)
		{
			$plgPaymentPayuMoneyHelper = new plgPaymentPayuMoneyHelper;
			$plgPaymentPayuMoneyHelper->Storelog($this->_name, $data);
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

	/**
	 * Process Webhooks data and return the array
	 *
	 * @return  array  formatted webhooks data
	 */
	public function onTP_ProcessInputData()
	{
		$data = Factory::getApplication()->input->json->getArray();
		$data['order_id'] = $data['udf1'];

		return $data;
	}
}
