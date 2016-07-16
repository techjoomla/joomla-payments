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
 * Joomla Payment Plugin 2Checkout
 *
 * @package     TechJoomla.Plugin
 * @subpackage  Payment.joomla
 * @since       3.0
 */
class PlgPayment2CheckoutHelper
{
	/**
	 * Function buildPaypalUrl
	 *
	 * @param   boolean  $secure  Array holding options (remember, autoregister, group)
	 *
	 * @return  string
	 *
	 * @since   3.0
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
	 * Function Storelog
	 *
	 * @param   string  $name     Name String
	 * @param   Array   $logdata  Array
	 *
	 * @return  string
	 *
	 * @since   3.0
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
	 * Function validateIPN
	 *
	 * @param   Array  $data    Name String
	 * @param   Array  $secret  Array
	 *
	 * @return  string
	 *
	 * @since   3.0
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
	 * Function log_ipn_results
	 *
	 * @param   Boolean  $success  success
	 *
	 * @return  void
	 *
	 * @since   3.0
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
