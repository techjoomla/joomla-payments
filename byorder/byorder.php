<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
/** ensure this file is being included by a parent file */

defined('_JEXEC') or die('Restricted access');

// Require_once JPATH_COMPONENT . DS . 'helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_byorder', JPATH_ADMINISTRATOR);
require_once dirname(__FILE__) . '/byorder/helper.php';

/**
 * Plgpaymentbyorder
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class Plgpaymentbyorder extends JPlugin
{
	protected  $payment_gateway = 'byorder';

	protected  $log = null;

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
		// 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
		$this->responseStatus = array(
			'Success' => 'C',
			'Failure' => 'X',
			'Pending' => 'P',
			'ERROR' => 'E'
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
		if (empty($layout))
		{
			$layout = "default";
		}

		$app       = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout . '.php';
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
		$vars->custom_name  = $this->params->get('plugin_name');
		$vars->custom_email = $this->params->get('plugin_mail');
		$html               = $this->buildLayout($vars);

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
	 * Adds a row for the first time in the db, calls the layout view
	 *
	 * @param   object  $data  Data from component
	 * @param   object  $vars  Component data
	 *
	 * @since   2.2
	 *
	 * @return   object  processeddata
	 */
	public function onTP_Processpayment($data, $vars = array())
	{
		JLoader::import('joomla.utilities.date');
		$isValid       = true;
		$error         = array();
		$error['code'] = '';
		$error['desc'] = '';

		$trxnstatus  = "Pending";

		// 3.compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];

		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$trxnstatus    = 'ERROR';
				$isValid       = false;
				$error['desc'] = "ORDER_MISMATCH" . " Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}

		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $data['total'];
				$epsilon      = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus    = 'ERROR';

					// Change response status to ERROR FOR AMOUNT ONLY
					$isValid       = false;
					$error['desc'] = "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}
		// END OF AMOUNT CHECK

		$payment_status = $this->translateResponse($trxnstatus);

		$data['payment_status'] = $payment_status;
		$result                 = array(
			'transaction_id' => '',
			'order_id' => $data['order_id'],
			'status' => $payment_status,
			'total_paid_amt' => $data['total'],
			'raw_data' => json_encode($data),
			'error' => '',
			'return' => $data['return']
		);

		return $result;
	}

	/**
	 * This function transalate the response got from payment getway
	 *
	 * @param   object  $invoice_status  invoice_status
	 *
	 * @since   2.2
	 *
	 * @return   string  value
	 */
	public function translateResponse($invoice_status)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == $invoice_status)
			{
				return $value;
			}
		}
	}

	/**
	 * Transalate the response
	 *
	 * @param   mixed  $data  data to store
	 *
	 * @since   2.2
	 *
	 * @return 0
	 */
	public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if ($log_write == 1)
		{
			$plgPaymentByorderHelper = new plgPaymentByorderHelper;
			$log                     = $plgPaymentByorderHelper->Storelog($this->_name, $data);
		}
	}
}
