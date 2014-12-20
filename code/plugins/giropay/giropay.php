<?php
/**
 * @package    Joomla-Payments
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       10.12.14
 *
 * @copyright  Copyright (C) 2008 - 2014 Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

// Include Helper
require_once dirname(__FILE__) . '/giropay/helper.php';

// Include GiroPay API
require_once dirname(__FILE__) . '/GiroCheckout_SDK/GiroCheckout_SDK.php';

// Load language
$lang = JFactory::getLanguage();
$lang->load('plg_payment_giropay', JPATH_ADMINISTRATOR);

// Import JFile (should normally be already there, but on some installations..
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class plgPaymentGiropay
 *
 * @since  1.0.0
 */
class PlgPaymentGiropay extends JPlugin
{
	/**
	 * Builds and includes the layout file (normally giropay/giropay/default.php)
	 *
	 * @param   array   $vars    - The vars
	 * @param   string  $layout  - Which layout to use
	 *
	 * @return  string  - The generated HTML
	 */
	public function buildLayout($vars, $layout = 'default')
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath();

		include $layout;

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Builds the layout path (e.g. if an override exists this file will be used)
	 *
	 * @return  string  - The file path
	 */
	public function buildLayoutPath()
	{
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/tmpl/default.php';

		// Let's check if we have an template override
		$override = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/'
			. $this->_type . '/' . $this->_name . '/' . 'default.php';

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
	 * Return the Payment plugin informations
	 *
	 * @param   array  $config  - The used plugins
	 *
	 * @return  stdClass  - Informations about this plugin (or null)
	 */
	public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return null;
		}

		// Create a new object
		$obj       = new stdClass;
		$obj->name = $this->params->get('plugin_name');
		$obj->id   = $this->_name;

		return $obj;
	}

	/**
	 * Constructs the payment form
	 *
	 * @param   object  $vars  - The payment informations
	 *
	 * @return  string  - The html
	 */
	public function onTP_GetHTML($vars)
	{
		$html = $this->buildLayout($vars);

		return $html;
	}

	/**
	 * Process submit (not used in giropay)
	 *
	 * @param   array  $data  - The data
	 * @param   array  $vars  - The vars
	 */
	function onTP_ProcessSubmit($data, $vars)
	{
		// Not implemented
	}

	/**
	 * Process payment callback (from giropay)
	 *
	 * @param   object  $data  - The data to process
	 *
	 * @return  array
	 */
	function onTP_Processpayment($data)
	{
		// Get the girpay project password
		$projectPassword = $this->params->get("project_password", "");

		// Confirm that it is a valid callback :-) The SDK does that for us
		$notify = new GiroCheckout_SDK_Notify('giropayTransaction');
		$notify->setSecret($projectPassword);
		$notify->parseNotification($_GET);

		// Payment was successfull
		if ($notify->paymentSuccessful())
		{
			// Build a return array - most important things is the status to C (completed)
			$result = array(
				'order_id'       => $data['custom'],
				'transaction_id' => $notify->getResponseParam('gcBackendTxId'),
				'subscriber_id'  => $data['subscr_id'],
				'buyer_email'    => '',
				'status'         => 'C',
				'txn_type'       => $data['txn_type'],
				'total_paid_amt' => $notify->getResponseParam('gcAmount'),
				'raw_data'       => $notify->getResponseParams(),
				'error'          => '',
			);

			// Notify giropay that everything is okay (we normally would need a 200 / OK header)
			$notify->sendOkStatus();

			return $result;
		}
		else
		{
			// Payment was not successful set status to E (error)
			$result = array(
				'order_id'       => $data['custom'],
				'transaction_id' => $notify->getResponseParam('gcBackendTxId'),
				'subscriber_id'  => $data['subscr_id'],
				'buyer_email'    => '',
				'status'         => 'E',
				'txn_type'       => $data['txn_type'],
				'total_paid_amt' => $notify->getResponseParam('gcAmount'),
				'raw_data'       => $notify->getResponseParams(),
				'error'          => '',
			);

			// Notify giropay that we received the status
			$notify->sendOkStatus();

			return $result;
		}
	}
}
