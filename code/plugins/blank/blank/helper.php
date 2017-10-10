<?php
/**
 * @package     Joomla.Payment.Plugin
 * @subpackage  Payment
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * Blank Payment Helper Class.
 *
 * @package  Joomla.Payment.Plugin
 * 
 * @since    1.0
 */
class PlgPaymentBlankHelper
{
	/**
	 * Gets the action URL
	 * 
	 * @param   boolean  $secure  For secure payment process.
	 * 
	 * @return  string  
	 * 
	 * @since   1.0
	 */
	static function buildBlankUrl($secure = true)
	{
		// 1. check for https or http to use
		$secure_post = '';//$this->params->get('secure_post');

		// 2. check whether sandbox mode is ON /OFF
		//$url = $this->params->get('sandbox') ? 'www.sandbox.blank.com' : 'www.blank.com';
		$url = $secure_post ? 'www.sandbox.blank.com' : 'www.blank.com';

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
	 * Function for Logging payment data
	 * 
	 * @param   string  $name     Name for refernce the log.
	 * @param   RAW     $logdata  payment data for log.
	 * 
	 * @return  string  
	 * 
	 * @since   1.0
	 */
	public function Storelog($name, $logdata)
	{
		jimport('joomla.error.log');

		$options = array('format' => "{DATE}\t{TIME}\t{USER}\t{DESC}");

		if (JVERSION >= '1.6.0')
		{
			$path = JPATH_SITE . '/plugins/payment/' . $name . '/' . $name . '/';
		}
		else
		{
			$path = JPATH_SITE . '/plugins/payment/' . $name . '/';
		}

		$my = JFactory::getUser();
		$logs = JLog::getInstance($logdata['JT_CLIENT'] . '_' . $name . ' . log', $options, $path);
		$logs->addEntry(array('user' => $my->name . '(' . $my->id . ')','desc' => json_encode($logdata['raw_data'])));
	}

	/**
	 * Validates the incoming data.
	 * 
	 * @param   RAW  $data  payment data.
	 * 
	 * @return  string  
	 * 
	 * @since   1.0
	 */
	static function validateIPN($data)
	{
		// Parse the blank URL

		$url = PlgPaymentBlankHelper::buildBlankUrl();
		//$this->blank_url = $url;
		$url_parsed = parse_url($url);

		/**
		generate the post string from the _POST vars aswell as load the
		POST vars into an arry so we can play with them from the calling
		script.
		Append ipn command
		Open the connection to blank
		*/
		$fp = fsockopen($url_parsed[host], "80", $err_num, $err_str, 30);

		if (!$fp)
		{
			// Could not open the connection.  If loggin is on, the error message
			// will be in the log.
			$this->last_error = "fsockopen error no. $errnum: $errstr";
			plgPaymentBlankHelper::log_ipn_results(false);

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

			// Post the data back to blank
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
			PlgPaymentBlankHelper::log_ipn_results(true);

			return true;
		}
		else
		{
			// Invalid IPN transaction.  Check the log for details.
			$this->last_error = 'IPN Validation Failed.';
			PlgPaymentBlankHelper::log_ipn_results(false);

			return false;
		}
	}

	/**
	 * Log the IPN data.
	 * 
	 * @param   string  $success  result of payment.
	 * 
	 * @return  string  
	 * 
	 * @since   1.0
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
		$text .= "IPN POST Vars from payment server:\n";

		foreach ($this->ipn_data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		// Log the response from the payment server
		$text .= "\nIPN Response from payment Server:\n " . $this->ipn_response;

		// Write to log
		$fp = fopen($this->ipn_log_file, 'a');

		fwrite($fp, $text . "\n\n");

		fclose($fp);

		// Close file
	}
}
