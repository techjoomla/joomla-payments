<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die(';)');
jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * PlgPaymentBycheckHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentPaypalHelper
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
		$plugin = JPluginHelper::getPlugin('payment', 'paypal');
		$params = json_decode($plugin->params);
		$url    = $params->sandbox ? 'www.sandbox.paypal.com/cgi-bin/webscr' : 'www.paypal.com/cgi-bin/webscr';
		$url    = 'https://' . $url;

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
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$my      = JFactory::getUser();

		if (empty($logdata['JT_CLIENT']))
		{
			$logdata['JT_CLIENT'] = "cpg_";
		}

		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.log',
				'text_entry_format' => $options
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry       = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);
		JLog::add($logEntry);
	}

	/**
	 * ValidateIPN - Validate the payment detail. (We are thankful to Akeeba Subscriptions Team,
	 * while modifing the plugin according to paypal security update. https://github.com/paypal/TLS-update#php
	 * Security update links: https://devblog.paypal.com/upcoming-security-changes-notice/
	 * https://developer.paypal.com/docs/classic/ipn/ht_ipn/
	 *
	 * @param   string  $data           data
	 * @param   string  $componentName  Component Name
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function validateIPN($data, $componentName)
	{
		$url              = self::buildPaypalUrl();
		$newData = array(
			'cmd'	=> '_notify-validate'
		);
		$newData = array_merge($newData, $data);

		$options = array(
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_VERBOSE        => false,
			CURLOPT_HEADER         => false,
			CURLINFO_HEADER_OUT    => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CAINFO         => dirname(__FILE__) . '/cacert.pem',
			CURLOPT_HTTPHEADER     => array('Connection: Close'),
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $newData,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,

		);

		/*
		 TLS 1.2 is only supported in OpenSSL 1.0.1c and later AND cURL 7.34.0 and later running on PHP 5.5.19+ or
		 PHP 5.6.3+. If these conditions are met we can use PayPal's minimum requirement of TLS 1.2 which is mandatory
		 since June 2016.
		*/
		$curlVersionInfo   = curl_version();
		$curlVersion       = $curlVersionInfo['version'];
		$openSSLVersionRaw = $curlVersionInfo['ssl_version'];

		// OpenSSL version typically reported as "OpenSSL/1.0.1e", I need to convert it to 1.0.1.5
		$parts             = explode('/', $openSSLVersionRaw, 2);
		$openSSLVersionRaw = (count($parts) > 1) ? $parts[1] : $openSSLVersionRaw;
		$openSSLVersion    = substr($openSSLVersionRaw, 0, -1) . '.' . (ord(substr($openSSLVersionRaw, -1)) - 96);

		// PHP version required for TLS 1.2 is 5.5.19+ or 5.6.3+
		$minPHPVersion = version_compare(PHP_VERSION, '5.6.0', 'ge') ? '5.6.3' : '5.5.19';

		$curlVerStatus = version_compare($curlVersion, '7.34.0', 'ge');

		if (!$curlVerStatus ||  ! version_compare($openSSLVersion, '1.0.1.3', 'ge') || 	! version_compare(PHP_VERSION, $minPHPVersion, 'ge'))
		{
			$phpVersion = PHP_VERSION;
			$data['ipncheck_envoirnmen_warning'] = "WARNING! PayPal demands that connections be made with TLS 1.2.
				This requires PHP $minPHPVersion+
				(you have $phpVersion), libcurl 7.34.0+ (you have $curlVersion) and OpenSSL 1.0.1c+ (you have
				$openSSLVersionRaw) on your server's PHP. Please upgrade these requirements to meet the stated
				minimum or the PayPal integration will cease working.";
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		$response = curl_exec($ch);
		$errNo = curl_errno($ch);
		$error = curl_error($ch);
		$lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		$status = false;

		if (($errNo > 0) && !empty($error))
		{
			$data['ipncheck_failure_got_error'] = "Could not open SSL connection to $hostname:443, cURL error $errNo: $error";

			$status = false;
		}

		if ($lastHttpCode >= 400)
		{
			$data['ipncheck_failure'] = "Invalid HTTP status $lastHttpCode verifying PayPal's IPN";

			$status = false;
		}

		if (stristr($response, "VERIFIED"))
		{
			$status = true;
		}
		elseif (stristr($response, "INVALID"))
		{
			$data['akeebasubs_ipncheck_failure'] = 'PayPal claims the IPN data is INVALID – Possible fraud!';

			$status = false;
		}

		$logData = array();
		$logData["JT_CLIENT"] = $componentNamel;
		$logData["raw_data"] = $data;
		self::Storelog("paypal", $logData);

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
		fclose($fp);
	}
}
