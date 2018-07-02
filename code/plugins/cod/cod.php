<?php
/**
 * @version    SVN: <svn_id>
 * @package    Cash On Delivery
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Ensure this file is being included by a parent file.
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_payment_cod', JPATH_ADMINISTRATOR);
// Load helper.
if (JVERSION >= '1.6.0')
{
	require_once (JPATH_SITE . '/plugins/payment/cod/cod/helper.php');
}
else
{
	require_once (JPATH_SITE . '/plugins/payment/cod/helper.php');
}


class plgpaymentcod extends JPlugin
{
	var $_payment_gateway = 'payment_cod';
	var $_log = null;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();
		$this->responseStatus = array(
			'Success' => 'C',
			'Failure' => 'X',
			'Pending' => 'P',
			'ERROR' => 'E',
			'COD' => 'COD',
		);
	}


	function buildLayoutPath($layout)
	{
		if (empty($layout))
		{
			 $layout = "default";
		}

		$app = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/tmpl/' . $layout . '.php';
		$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return $core_file;
		}
	}

	// Builds the layout to be shown, along with hidden fields.
	/**
	 * Return basic payment gateway name.
	 *
	 * @param   mixed   $var  object sent by component.
	 *
	 * @since   1.0.0
	 * @return  object payment gatways basic info.
	 */

	function buildLayout($vars, $layout = 'default')
	{
		if (JVERSION >= '1.6.0')
		{
			require_once (JPATH_SITE . '/plugins/payment/cod/cod/helper.php');
		}
		else
		{
			 require_once (JPATH_SITE . '/plugins/payment/cod/helper.php');
		}

		// Load the layout & push variables
		ob_start();
			$layout = $this->buildLayoutPath($layout);
			include ($layout);
			$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	/**
	 * Return basic payment gateway name.
	 *
	 * @param   mixed   $var  object sent by component.
	 *
	 * @since   1.0.0
	 * @return  object payment gatways basic info.
	 */
	function onTP_GetHTML($vars)
	{
		$vars->custom_name = $this->params->get('plugin_name');
		$html = $this->buildLayout($vars);
		return $html;
	}

	/**
	 * Return basic payment gateway name.
	 *
	 * @param   mixed   $config  payment gateway names array.
	 *
	 * @since   1.0.0
	 * @return  object payment gatways basic info.
	 */
	function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj = new stdClass;
		$obj->name = $this->params->get('plugin_name');
		$obj->id = $this->_name;
		return $obj;
	}

	/**
	 * This function process the payment response received from payment gateway site
	 *
	 * @param   mixed   $data  Payment response data.
	 * @param   object  $vars  Formatted Order detail sent from component.
	 *
	 * @since   1.0.0
	 * @return  array Standared formatted payment response.
	 */
	function onTP_Processpayment($data, $vars)
	{
		$isValid = true;
		$error = array();
		$error['code'] = '';
		$error['desc'] = '';
		$trxnstatus = "COD";

		// Compare response order id and send order id in notify URL
		$res_orderid = '';
		$res_orderid = $data['order_id'];

		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$trxnstatus = 'ERROR';
				$isValid = false;
				$error['desc'] = Text::sprintf('PLG_PAYMENT_COD_ORDER_ID_MISMATCH', $vars->order_id, $res_orderid);
			}
		}

		// amount check
		if ($isValid)
		{
			if (!empty($vars))
			{

				// Check whether amount is correct ?
				$order_amount = (float)$vars->amount;
				$retrunamount = (float)$data['total'];
				$epsilon = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus = 'ERROR';
					$isValid = false;
					$error['desc'] = Text::sprintf('PLG_PAYMENT_COD_ORDER_AMOUNT_MISMATCH', $order_amount, $retrunamount);
				}
			}
		}
		// END OF AMOUNT CHECK

		$payment_status = $this->translateResponse($trxnstatus);
		$data['payment_status'] = $payment_status;
		$result = array(
			'transaction_id' => '',
			'order_id' => $data['order_id'],
			'status' => $payment_status,
			'total_paid_amt' => $data['total'],
			'raw_data' => $data,
			'error' => '',
			'return' => $data['return'],
		);
		return $result;
	}

	/**
	 * Transalate order status according to defined plugin status in constructor.
	 *
	 * @param   string  $resOrderStatus  Order status.
	 *
	 * @since   1.0.0
	 * @return  string status.
	 */
	function translateResponse($resOrderStatus)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == $resOrderStatus)
			{
				return $value;
			}
		}
	}

	/**
	 * Method to store the formatted response.
	 *
	 * @param   mixed  $data  formated post data.
	 *
	 * @since   1.0.0
	 * @return  null
	 */
	function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if($log_write == 1)
		{
			$plgPaymentCodHelper = new plgPaymentCodHelper;
			$log = $plgPaymentCodHelper->Storelog($this->_name, $data);
		}
	}
}
