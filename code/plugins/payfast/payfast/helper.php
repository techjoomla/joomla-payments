<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

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
		$plugin = PluginHelper::getPlugin('payment', 'payfast');
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
		$my = Factory::getUser();

		Log::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
				'text_entry_format' => $options
			),
			Log::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new LogEntry('Transaction added', Log::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		$person = json_encode($logEntry);
		Log::add($logEntry);
	}
}
