<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
	defined('_JEXEC') or die('Restricted access');

	jimport('joomla.html.html');
	jimport('joomla.plugin.helper');

/**
 * PlgPaymentAuthorizenetHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentAuthorizenetHelper
{
	/**
	 * buildAuthorizenetUrl.
	 *
	 * @param   object  $secure  secure
	 *
	 * @since   2.2
	 *
	 * @return   string url
	 */
	public function buildAuthorizenetUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'authorizenet');
		$params = json_decode($plugin->params);
		$secure_post = $params->secure_post;
		$url = $params->sandbox ? 'test.authorize.net' : 'secure.authorize.net';
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

		JLog::add($logEntry);

	// $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
	// $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}

	/**
	 * isSandboxEnabled.
	 *
	 * @since   2.2
	 *
	 * @return   boolean
	 */
	public function isSandboxEnabled()
	{
		$plugin = JPluginHelper::getPlugin('payment', 'authorizenet');
		$params = json_decode($plugin->params);

		if ($params->sandbox)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
