<?php
/**
 * @package payment plugin
 * @copyright Copyright (C) 2009 -2022 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die(';)');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * PlgPaymentAdaptivePaypalHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentAdaptivePaypalHelper
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
		$plugin = PluginHelper::getPlugin('payment', 'adaptive_paypal');
		$params = json_decode($plugin->params);
		$url    = $params->sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		return $url = 'https://' . $url . '/cgi-bin/webscr';
	}

	/**
	 * Store log
	 *
	 * @param   string  $name     name.
	 * @param   array   $logdata  data.
	 *
	 * @since   1.0
	 * @return  list.
	 */
	public function Storelog($name, $logdata)
	{
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$my      = Factory::getUser();

		Log::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
				'text_entry_format' => $options
			), Log::INFO, $logdata['JT_CLIENT']
		);

		$logEntry       = new LogEntry('Transaction added', Log::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		Log::add($logEntry);
	}

	/**
	 * StorelogBeforePayment
	 *
	 * @param   String  $name       name.
	 * @param   Array   $logdata    data.
	 * @param   String  $client     client.
	 * @param   String  $item_name  item_name.
	 *
	 * @since   1.0
	 * @return  list.
	 */
	public function StorelogBeforePayment($name, $logdata, $client = '', $item_name = '')
	{
		// Store item name
		$logdata['item_name'] = $item_name;

		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$path = JPATH_SITE . '/plugins/payment/' . $name . '/' . $name . '/';

		$my = Factory::getUser();

		Log::addLogger(
			array(
				'text_file' => 'logBeforePayment_' . $client . '.php',
				'text_entry_format' => $options,
				'text_file_path' => $path
			), Log::INFO, $logdata
		);

		$logEntry       = new LogEntry('Transaction added', Log::INFO, $logdata);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata);

		// Write log
		Log::add($logEntry);
	}

	/**
	 * ValidateIPN
	 *
	 * @param   string  $data  data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function validateIPN($data)
	{
		// Parse the paypal URL
		$url              = plgPaymentAdaptivePaypalHelper::buildPaypalUrl();
		$this->paypal_url = $url;
		$url_parsed       = parse_url($url);

		/* generate the post string from the _POST vars aswell as load the
		_POST vars into an arry so we can play with them from the calling
		script.
		append ipn command
		open the connection to paypal*/
		$fp = fsockopen($url_parsed['host'], "80", $err_num, $err_str, 30);
		/*$fp = fsockopen ($this->paypal_url, 80, $errno, $errstr, 30);*/

		if (!$fp)
		{
			/*could not open the connection.  If loggin is on, the error message
			will be in the log.*/
			$this->last_error = "fsockopen error no. $errnum: $errstr";
			plgPaymentAdaptivePaypalHelper::log_ipn_results(false);

			return false;
		}
		else
		{
			$post_string = '';

			foreach ($data as $field => $value)
			{
				$this->ipn_data["$field"] = $value;
				$post_string .= $field . '=' . urlencode(stripslashes($value)) . '&';
			}

			$post_string .= "cmd=_notify-validate";

			// Post the data back to paypal
			fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
			fputs($fp, "Host: $url_parsed[host]\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($post_string) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $post_string . "\r\n\r\n");

			// Loop through the response from the server and append to variable
			while (!feof($fp))
			{
				$this->ipn_response .= fgets($fp, 1024);
			}

			// Close connection
			fclose($fp);
		}

		if (eregi("verified", $post_string))
		{
			// Valid IPN transaction.
			plgPaymentAdaptivePaypalHelper::log_ipn_results(true);

			return true;
		}
		else
		{
			// Invalid IPN transaction.  Check the log for details.
			$this->last_error = 'IPN Validation Failed.';
			plgPaymentAdaptivePaypalHelper::log_ipn_results(false);

			return false;
		}
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
