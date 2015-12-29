<?php
/**
 * @version    SVN: <svn_id>
 * @package    CPG
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die(';)');
jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * Helper for amazon
 *
 * @package     CPG
 * @subpackage  component
 * @since       1.0
 */
class PlgPaymentAmazonHelper
{
	/**
	 * Get Amazon url
	 *
	 * @param   integer  $secure  true/false
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function buildAmazonUrl($secure = true)
	{
	}

	/**
	 * Store log for Amazon posted data to IPN url
	 *
	 * @param   string  $name     name of plugin
	 * @param   string  $logdata  data passed in post
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function Storelog($name, $logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$text_file = $logdata['JT_CLIENT'] . '_' . $name . '.log';
		$my = JFactory::getUser();
		JLog::addLogger(
							array('text_file' => $text_file ,
								'text_entry_format' => $options
							), JLog::INFO, $logdata['JT_CLIENT']
						);
		$logEntry       = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);
	}

	/**
	 * Validate IPN data passed from Amazon
	 *
	 * @param   string  $data         url
	 * @param   string  $urlEndPoint  data passed in post
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function validateIPN($data, $urlEndPoint)
	{
		require_once dirname(__FILE__) . '/lib/IPNAndReturnURLValidation/src/SignatureUtilsForOutbound.php';
		$params["transactionId"]     = $data['transactionId'];
		$params["transactionDate"]   = $data['transactionDate'];
		$params["status"]            = $data['status'];
		$params["signatureMethod"]   = $data['signatureMethod'];
		$params["signatureVersion"]  = $data['signatureVersion'];
		$params["buyerEmail"]        = $data['buyerEmail'];
		$params["recipientEmail"]    = $data['recipientEmail'];
		$params["operation"]         = $data['operation'];
		$params["transactionAmount"] = $data['transactionAmount'];
		$params["referenceId"]       = $data['referenceId'];
		$params["buyerName"]         = $data['buyerName'];
		$params["recipientName"]     = $data['recipientName'];
		$params["paymentMethod"]     = $data['paymentMethod'];
		$params["paymentReason"]     = $data['paymentReason'];
		$params["certificateUrl"]    = $data['certificateUrl'];
		$params["signature"]         = $data['signature'];
		$utils = new SignatureUtilsForOutbound;

		// IPN is sent as a http POST request and hence we specify POST as the http method.
		// Signature verification does not require your secret key
		try
		{
			$xml = $utils->validateRequest($params, $urlEndPoint, "POST");
		}
		catch (Exception $e)
		{
			$data['error'] = $error = $e;

			return false;
		}

		return $result = (string) $xml->VerifySignatureResult->VerificationStatus;
	}
}
