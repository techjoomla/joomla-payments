<?php
/**
 * @copyright  Copyright (c) 2015-2020 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 * @link       http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * PlgPayment2CheckoutHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPayment2CheckoutHelper
{
	// Gets the paypal URL
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
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

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
	public function Storelog($name,$logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$my = JFactory::getUser();

		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.log',
				'text_entry_format' => $options
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		JLog::add($logEntry);

		// $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		// $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}

	/**
	 * validateIPN.
	 *
	 * @param   string  $data    data
	 * @param   string  $secret  data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function validateIPN($data, $secret)
	{
		$incoming_md5 = strtoupper($data['md5_hash']);
		$calculated_md5 = md5(
			$data['sale_id'] .
			$data['vendor_id'] .
			$data['invoice_id'] .
			$secret
		);
		$calculated_md5 = strtoupper($calculated_md5);

		return ($calculated_md5 == $incoming_md5);
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
