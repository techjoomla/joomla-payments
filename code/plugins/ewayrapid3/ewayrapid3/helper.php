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
 * PlgPaymentEwayrapid3Helper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentEwayrapid3Helper
{
	/**
	 * buildAuthoribuildEwayrapid3UrlzenetUrl.
	 *
	 * @param   object  $secure  secure
	 *
	 * @since   2.2
	 *
	 * @return   string url
	 */
	public function buildEwayrapid3Url($secure = true)
	{
		/*
		$plugin = PluginHelper::getPlugin('payment', 'ewayrapid3');
		$params=json_decode($plugin->params);
		$sandbox=$params->sandbox;
		if(!empty($sandbox)) {
			$url =  '';
		} else {
			$url =  '';
		}
		if ($secure) {
			$url = 'https://' . $url;
		}
		return $url;*/
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

		Log::add($logEntry);
	}
}
