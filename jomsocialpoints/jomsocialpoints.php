<?php
/**
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Require_once JPATH_COMPONENT . DS . 'helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_jomsocialpoints', JPATH_ADMINISTRATOR);
require_once dirname(__FILE__) . "/jomsocialpoints/helper.php";

/**
 * Ensure this file is being included by a parent file
 *
 * @since  1.0.0
 */
class Plgpaymentjomsocialpoints extends JPlugin
{
	protected $payment_gateway = 'payment_jomsocialpoints';

	protected $log = null;

	/**
	 * Function to get
	 *
	 * @param   STRING  &$subject  subject
	 * @param   STRING  $config    config
	 *
	 * @since  1.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();

		// Define Payment Status codes in Authorise  And Respective Alias in Framework 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
		$this->responseStatus = array(
			'Success' => 'C',
			'Failure' => 'X',
		);
	}

	/**
	 * Function to get
	 *
	 * @param   STRING  $layout  layout
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function buildLayoutPath($layout)
	{
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/tmpl/form.php';
		$override_ext = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/';
		$override = $override_ext . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

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
	 * Function to Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   STRING  $vars    vars
	 * @param   STRING  $layout  layout
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
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

	/**
	 * Function to get.
	 *
	 * @param   STRING  $vars  vars
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function onTP_GetHTML($vars)
	{
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();
		$jspath = JPATH_ROOT . '/components/com_community';

		if (JFolder::exists($jspath))
		{
			$query = "SELECT points FROM #__community_users where userid=$vars->user_id";
			$db->setQuery($query);
			$user_points = $db->loadResult();
			$vars->user_points = $user_points;

			if ($user_points == '')
			{
				$vars->user_points = 0;
			}

			$vars->convert_val = $this->params->get('conversion');

			$html = $this->buildLayout($vars);

			return $html;
		}
	}

	/**
	 * Function to get.
	 *
	 * @param   STRING  $config  config
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function onTP_GetInfo($config)
	{
		$jspath = JPATH_ROOT . '/components/com_community';

		if (JFolder::exists($jspath))
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
	}

	/**
	 * Function to adds a row for the first time in the db, calls the layout view.
	 *
	 * @param   STRING  $data  data
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function onTP_Processpayment($data)
	{
		$isValid = true;
		$error	 = array();
		$error['code']	= '';
		$error['desc']	= '';
		$db    = JFactory::getDBO();
		$query = "SELECT points FROM #__community_users where userid=" . $data['user_id'];
		$db->setQuery($query);
		$points_count  = $db->loadResult();
		$convert_val   = $this->params->get('conversion');
		$points_charge = $data['total'] * $convert_val;

		if ($points_charge <= $points_count)
		{
			$count = $points_count - $points_charge;
			$sql   = "UPDATE #__community_users SET points =" . $db->quote($count) . " WHERE userid=" . $data['user_id'];
			$db->setQuery($sql);
			$db->query();
			$payment_status = 'Success';
		}
		else
		{
			$payment_status = 'Failure';
			$isValid = false;
		}

		// Compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];

		if ($isValid )
		{
			if (!empty($vars) && $res_orderid != $vars->order_id )
			{
				$payment_status = 'ERROR';
				$isValid = false;
				$error['desc'] .= "ORDER_MISMATCH" . " Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}

		// Amount check
		if ($isValid )
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $data['total'];
				$epsilon = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					$payment_status = 'ERROR';
					$isValid = false;
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}

		// TRANSLET RESPONSE
		$payment_status = $this->translateResponse($payment_status);
		$data['payment_status'] = $payment_status;
		$result = array('transaction_id' => '',
						'order_id' => $data['order_id'],
						'status' => $payment_status,
						'total_paid_amt' => $data['total'],
						'raw_data' => json_encode($data),
						'error' => '',
						'return' => $data['return'],
						);

		return $result;
	}

	/**
	 * Function to get.
	 *
	 * @param   STRING  $invoice_status  invoice_status
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
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
	 * Method to get onTP_Storelog
	 *
	 * @param   STRING  $data  storelog data
	 *
	 * @return   Test    comment
	 *
	 * @since    1.0.0
	 */
	public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if ($log_write == 1)
		{
			$log = plgPaymentJomsocialpointsHelper::Storelog($this->_name, $data);
		}
	}
}
