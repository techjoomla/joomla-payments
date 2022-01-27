<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * PlgPaymentpaymillHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentpaymillHelper
{
	/**
	 * buildAuthorizenetUrl.
	 *
	 * @param   string  $secure  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildAuthorizenetUrl($secure = true)
	{
		$plugin = PluginHelper::getPlugin('payment', 'paymill');
		$params = json_decode($plugin->params);
		$secure_post = $params->secure_post;
		$url = $params->sandbox ? 'test.paymill.net' : 'secure.paymill.net';
		/*	$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'test.authorize.net' : 'secure.authorize.net';*/
		if ($secure_post)
		{
			$url = 'https://' . $url . '/gateway/transact.dll';
		}
		else
		{
			$url = 'http://' . $url . '/gateway/transact.dll';
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
	public function Storelog($name, $logdata)
	{
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
