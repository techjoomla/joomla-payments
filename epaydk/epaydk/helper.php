<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.plugin.helper');
jimport('joomla.html.parameter');

/**
 * PlgPaymentEpaydkHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentEpaydkHelper
{
	/**
	 * buildEpaydkUrl.
	 *
	 * @param   object  $secure  secure
	 *
	 * @since   2.2
	 *
	 * @return   string url
	 */
	public function buildEpaydkUrl($secure = true)
	{
		$plugin  = JPluginHelper::getPlugin('payment', 'epaydk');
		$params  = json_decode($plugin->params);
		$sandbox = $params->sandbox;

		if (!empty($sandbox))
		{
			// SANDBOX MODE == ON
			$url = 'sandbox.epaydk.co.za/eng/process';
		}
		else
		{
			$url = 'www.epaydk.co.za/eng/process';
		}

		if ($secure)
		{
			$url = 'https://' . $url;
		}

		return $url;
	}

	/**
	 * Storelog.
	 *
	 * @param   object  $name     name
	 *
	 * @param   string  $logdata  logdata
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function Storelog($name, $logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$my = JFactory::getUser();

		JLog::addLogger(
			array(
			'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
			'text_entry_format' => $options
		), JLog::INFO, $logdata['JT_CLIENT']
		);

		$logEntry       = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		JLog::add($logEntry);
	}
}
