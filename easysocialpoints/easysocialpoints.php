<?php
/**
 * @package    Payment_Easysocialpoints
 * @author     Techjoomla http://www.techjoomla.com <support@techjoomla.com>
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

/** ensure this file is being included by a parent file */

// No direct access
defined('_JEXEC') or die('Restricted access');

$lang = JFactory::getLanguage();
$lang->load('plg_payment_easysocialpoints', JPATH_ADMINISTRATOR);

if (JVERSION >= '1.6.0')
{
	require_once JPATH_SITE . '/plugins/payment/easysocialpoints/easysocialpoints/helper.php';
}
else
{
	require_once JPATH_SITE . '/plugins/payment/easysocialpoints/helper.php';
}

/**
 * @since  1.6
 */

class Plgpaymenteasysocialpoints extends JPlugin
{
	var $payment_gateway = 'payment_easysocialpoints';

	var $log = null;

	Public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();

		// Define Payment Status codes in Authorise  And Respective Alias in Framework
		// 1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
		$this->responseStatus = array(
			'Success' => 'C',
			'Failure' => 'X',
		);
	}

	/**
	 * Check Override file exists
	 *
	 * @param   string  Return File path.
	 *
	 * @return  mixed  file path.
	 *
	 * @since   1.6
	 */
	Protected function buildLayoutPath($layout)
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$core_file = dirname(__FILE__) . DS . $this->_name . DS . 'tmpl' . DS . 'form.php';
		$override = JPATH_BASE . DS . 'templates' . DS . $app->getTemplate() . DS . 'html' . DS . 'plugins' . DS .
		$this->_type . DS . $this->_name . DS . $layout . '.php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}
	}

	// Builds the layout to be shown, along with hidden fields.
	Protected function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	Public function onTP_GetHTML($vars)
	{
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();
		$jspath = JPATH_ROOT . DS . 'components' . DS . 'com_easysocial';

		if (JFolder::exists($jspath))
		{
			$query = "SELECT SUM(points) FROM #__social_points_history where user_id=" . $vars->user_id. " AND state = 1 ";
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

	Public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj 		= new stdClass;
		$obj->name  = $this->params->get('plugin_name');
		$obj->id	= $this->_name;

		return $obj;
	}

	// Adds a row for the first time in the db, calls the layout view
	Public function onTP_Processpayment($data)
	{
		$isValid = true;
		$error = array();
		$error['code'] = '';
		$error['desc'] = '';
		$db = JFactory::getDBO();
		$query = "SELECT SUM(points) FROM #__social_points_history where user_id=" . $data['user_id']. " AND state = 1 ";
		$db->setQuery($query);
		$points_count = $db->loadResult();
		$convert_val = $this->params->get('conversion');
		$points_charge = $data['total'] * $convert_val;

		if ($points_charge <= $points_count)
		{
			//insert new entry in history table to deduct points
			$espoint=new stdClass();
			$espoint->id = '';
			$espoint->state = 1;
			$espoint->points = "-". $points_charge;
			$espoint->user_id = $data['user_id'];
			$espoint->created = date("Y-m-d H:i:s"); // 2014-08-12 11:14:54
			if (!$db->insertObject( '#__social_points_history', $espoint, 'id' ))
			{
				echo $db->stderr();
				return false;
			}
			$payment_status = 'Success';
		}
		else
		{
			$payment_status = 'Failure';
			$isValid = false;
		}

		// 3.compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];

		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
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
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . 'response order amount = ' . $retrunamount;
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
					'error' => ' ',
					'return' => $data['return'],

					);

		return $result;
	}

	Public function translateResponse($invoice_status)
	{
		foreach ($this->responseStatus as $key => $value)
				{
					if ($key == $invoice_status)
					{
						return $value;
					}
		}
	}

	Public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if($log_write == 1)
		{
			$log = plgPaymenteasysocialpointsHelper::Storelog($this->_name, $data);
		}
	}
}
