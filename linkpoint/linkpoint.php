<?php
/**
 * @version    SVN: <svn_id>
 * @package    CPG
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
$lang = JFactory::getLanguage();
$lang->load('plg_payment_linkpoint', JPATH_ADMINISTRATOR);
jimport('joomla.plugin.plugin');
require_once dirname(__FILE__) . '/linkpoint/helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_linkpoint', JPATH_ADMINISTRATOR);

/**
 * plgPaymentLinkpoint
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentLinkpoint extends JPlugin
{
	private $cache = null;

	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->store_id 		= $this->params->get('store_id');
		$this->port		 		= $this->params->get('port', '1129');

		// Define Payment Status codes in Link[oint  And Respective Alias in Framework
		// APPROVED DECLINED, or FRAUD.

		$this->responseStatus = array(
			'APPROVED' => 'C',
			'DECLINED' => 'D',
			'FRAUD' => 'F',
			'ERROR'  => 'E'
		);
	}

	/**
	 * Build List of Payment Gateway in the respective Components
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

			$obj 		= new stdClass;
			$obj->name 	= $this->params->get('plugin_name');
			$obj->id	= $this->_name;

			return $obj;
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
		$core_file	= dirname(__FILE__) . '/' . $this->_name . '/tmpl/form.php';
		$override	= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' . $this->_type
		. '/' . $this->_name . '/' . $layout . '.php';

		return (JFile::exists($override)) ? $override : $core_file;
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
	public function buildLayout($vars, $layout = 'form' )
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
	 * Builds the layout to be shown, along with hidden fields. Constructs the Payment
	 * form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like linkpoint
	 *
	 * @param   object  $vars  Data from component
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function onTP_GetHTML($vars)
	{
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
	public function onTP_Processpayment($data,$vars=array())
	{
		$isValid = true;
		$error = array();
		$error['code']	= '';
		$error['desc']	= '';
		include dirname(__FILE__) . '/linkpoint/lib/lphp.php';
		$pemfilepath = (dirname(__FILE__) . '/linkpoint/staging_cert.pem');
		$plgPaymentLinkpointHelper = new plgPaymentLinkpointHelper;
		$host = $plgPaymentLinkpointHelper->buildLinkpointUrl();
		$orderid = $data['oid'];

		$mylphp = new lphp;
		$order["host"]       	= $host;
		$order["port"]       	= $this->port;
		$order["keyfile"] 		= $pemfilepath;
		$order["configfile"] 	= $this->store_id;

		$order["ordertype"] = "SALE";
		$testmode = $this->params->get('testmode', '1');

		if ($testmode == 1)
		{
			$order["result"] = "GOOD";

			// For test transactions, set to GOOD, DECLINE, or DUPLICATE
		}
		else
		{
				$order["result"] = "LIVE";
		}

		$order["transactionorigin"] = "ECI";

		// For credit card retail txns, set to RETAIL, for Mail order/telephone order, set to MOTO, for e-commerce, leave out or set to ECI
		$order["oid"] = $data['oid'];

		// Order ID number must be unique. If not set, gateway will assign one.

		// Transaction Details
		$order["chargetotal"] = $data['chargetotal'];

		// Card Info

		$order["cardnumber"]   = $data['creditcard_number'];
		$order["cardexpmonth"] = str_pad($data['expire_month'], 2, "0", STR_PAD_LEFT);
		$order["cardexpyear"]  = substr($data['expire_year'], 2);
		$order["cvmvalue"]     = $data['creditcard_code'];
		$order["debug"] = "true";

		// For development only - not intended for production use

		$raw_data = $mylphp->curl_process($order);

		// Use curl methods

		// 3.compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['oid'];

		if ($isValid )
		{
			if (!empty($vars) && $res_orderid != $vars->order_id )
			{
				$isValid = false;
				$error['desc'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is " . $vars->order_id . ", and response "
				. $res_orderid;
			}
		}
		// Amount check
		if ($isValid )
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $data["chargetotal"];
				$epsilon = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					$raw_data['r_approved'] = 'ERROR';

					// Change response status to ERROR FOR AMOUNT ONLY
					$isValid = false;
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount .
					' response order amount = ' . $retrunamount;
				}
			}
		}
		// Translet response
		$status = $this->translateResponse($raw_data['r_approved']);

		// Error Handling
		$error = array();
		$error['code']	.= $raw_data['r_code'];
		$error['desc']	.= $raw_data['r_message '];

		$result = array('transaction_id' => md5($data['oid']),
					'order_id' => $data['oid'],
					'status' => $status,
					'total_paid_amt' => $data["chargetotal"],
					'raw_data' => $raw_data,
					'error' => $error,
					'return' => $data['return'],
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
			$log = plgPaymentLinkpointHelper::Storelog($this->_name, $data);
		}
	}
}
