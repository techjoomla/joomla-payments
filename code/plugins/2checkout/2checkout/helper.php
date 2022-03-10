<?php
/**
 * @package     Joomla_Payments
 * @subpackage  plg_payments_2checkout
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\LogEntry;

/**
 * plgPayment2CheckoutHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPayment2CheckoutHelper
{
	/**
	 * buildPaypalUrl.
	 *
	 * @param   string  $secure  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildPaypalUrl($secure = true)
	{
		$secure_post = $this->params->get('secure_post');
		$url         = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		if ($secure_post)
		{
			$url = 'https://' . $url . '/cgi-bin/webscr';
		}
		else
		{
			$url = 'http://' . $url . '/cgi-bin/webscr';
		}

		return $url;
	}

	/**
	 * Store log
	 *
	 * @param   string  $name     name.
	 *
	 * @param   array   $logdata  data.
	 *
	 * @since   1.0
	 * @return  list.
	 */
	public function Storelog($name, $logdata)
	{
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$my = Factory::getUser();

		Log::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' .
				$name . '.php', 'text_entry_format' => $options
			), Log::INFO, $logdata['JT_CLIENT']
		);

		$logEntry       = new LogEntry('Transaction added', Log::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		Log::add($logEntry);
	}

	/**
	 * ValidateIPN
	 *
	 * @param   OBJECT  $data    data
	 * @param   STRING  $secret  Component Name
	 *
	 * @since   2.2
	 *
	 * @return   Boolean
	 */
	public function validateIPN($data, $secret)
	{
		$input = Factory::getApplication()->input;
		$incoming_md5   = strtoupper($data['md5_hash']);
		$calculated_md5 = md5($data['sale_id'] . $data['vendor_id'] . $data['invoice_id'] . $secret);
		$calculated_md5 = strtoupper($calculated_md5);

		if ($calculated_md5 == $incoming_md5)
		{
			$status = true;
		}
		else
		{
			$data['ins_check_failure'] = Text::_("PLG_PAYMENT_2CHECKOUT_ERR_INVALID_INS");

			$status = false;
		}

		$logData = array();
		$logData["JT_CLIENT"] = $input->get("option", '', "STRING");
		$logData["raw_data"] = $data;
		$this->Storelog("2checkout", $logData);

		return $status;
	}

	/**
	 * log_ipn_results.
	 *
	 * @param   string  $success  success
	 *
	 * @since   2.2
	 *
	 * @return   string  success
	 */
	public function log_ipn_results($success)
	{
		if (!$this->ipn_log)
		{
			return;
		}

		// Timestamp
		$text = '[' . date('m/d/Y g:i A') . '] - ';

		// Success or failure being logged?
		if ($success)
		{
			$text .= "SUCCESS!\n";
		}
		else
		{
			$text .= 'FAIL: ' . $this->last_error . "\n";
		}

		// Log the POST variables
		$text .= "IPN POST Vars from Paypal:\n";

		foreach ($this->ipn_data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		// Log the response from the paypal server
		$text .= "\nIPN Response from Paypal Server:\n " . $this->ipn_response;

		// Write to log
		$fp = fopen($this->ipn_log_file, 'a');
		fwrite($fp, $text . "\n\n");

		// Close file
		fclose($fp);
	}
}
