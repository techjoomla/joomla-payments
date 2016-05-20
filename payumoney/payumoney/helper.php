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
 * PlgPaymentBycheckHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentPayuMoneyHelper
{
	/**
	 * buildPayuMoneyUrl.
	 *
	 * @param   string  $secure  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildPayuMoneyUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'payumoney');
		$params = json_decode($plugin->params);
		$url = $params->sandbox? 'test.payu.in/_payment' : 'secure.payu.in/_payment';

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
	}
}
