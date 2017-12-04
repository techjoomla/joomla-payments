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
 * PlgPaymentPayfastHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentPayfastHelper
{
	/**
	 * buildPayfastUrl.
	 *
	 * @param   string  $secure  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildPayfastUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'payfast');
		$params = json_decode($plugin->params);
		$sandbox = $params->sandbox;

		if (!empty($sandbox))
		{
			// SANDBOX MODE == ON
			$url = 'sandbox.payfast.co.za/eng/process';
		}
		else
		{
			$url = 'www.payfast.co.za/eng/process';
		}

		if ($secure)
		{
			$url = 'https://' . $url;
		}

		return $url;
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
	public function Storelog($name,$logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$my = JFactory::getUser();

		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
				'text_entry_format' => $options
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		$person = json_encode($logEntry);
		JLog::add($logEntry);
	}
}
