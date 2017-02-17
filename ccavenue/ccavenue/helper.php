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
 * Helper for ccavenue
 *
 * @package     CPG
 * @subpackage  component
 * @since       1.0
 */
class PlgPaymentCcavenueHelper
{
	/**
	 * Get ccavenue url
	 *
	 * @param   integer  $secure  true/false
	 *
	 * @return  url
	 *
	 * @since   1.0
	 */
	public function buildCcavenueUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'ccavenue');
		$params = json_decode($plugin->params);
		$url = $params->sandbox ? 'test.ccavenue.com' : 'secure.ccavenue.com';

		if ($secure)
		{
			$url = 'https://' . $url . '/transaction/transaction.do?command=initiateTransaction&encRequest=';
		}

		return $url;
	}

	/**
	 * Store log for ccavenue posted data
	 *
	 * @param   string  $name     name of plugin
	 * @param   string  $logdata  data passed in post
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */

	public function Storelog($name,$logdata)
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
		/*
		$logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		$logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
		*/
	}
}
