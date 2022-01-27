<?php
/**
 * @version    SVN: <svn_id>
 * @package    CPG
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Helper for Cod
 *
 * @package     CPG
 * @subpackage  component
 * @since       1.0
 */
class PlgPaymentCodHelper
{
	/**
	 * buildAuthorizenetUrl
	 *
	 * @param   integer  $secure  true/false
	 *
	 * @return  url
	 *
	 * @since   1.0
	 */
	public function buildAuthorizenetUrl($secure = true)
	{
		/*
		Sample code for further use.
		$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'test.authorize.net' : 'secure.authorize.net';
		if ($secure_post) $url = 'https://' . $url . '/gateway/transact.dll';
		else $url = 'http://' . $url . '/gateway/transact.dll';
		return $url;
		*/
	}

	/**
	 * Store log for cod posted data
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
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$my = Factory::getUser();

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
}
