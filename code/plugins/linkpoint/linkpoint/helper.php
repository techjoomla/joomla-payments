<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die(';)');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;

/**
 * PlgPaymentLinkpointHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentLinkpointHelper
{
	/**
	 * buildLinkpointUrl.
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildLinkpointUrl()
	{
		$plugin      = PluginHelper::getPlugin('payment', 'payu');
		$params      = json_decode($plugin->params);
		$secure_post = $params->secure_post;
		$url         = $params->sandbox ? 'staging.linkpt.net' : 'secure.linkpt.net';
		/*$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'staging.linkpt.net' : 'secure.linkpt.net';*/
		if ($secure_post)
		{
			$url = $url;
		}
		else
		{
			$url = $url;
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
		$text .= "IPN POST Vars from Linkpoint:\n";

		foreach ($this->ipn_data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		// Log the response from the Linkpoint server
		$text .= "\nIPN Response from Linkpoint Server:\n " . $this->ipn_response;

		// Write to log
		$fp = fopen($this->ipn_log_file, 'a');
		fwrite($fp, $text . "\n\n");

		// Close file
		fclose($fp);
	}
}
