<?php
/**
 * @version    SVN: <svn_id>
 * @package    Payu
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
	jimport('joomla.html.parameter');
class plgPaymentPayuHelper
{

	//gets the Payu URL
	function buildPayuUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'payu');
		$params = json_decode($plugin->params);
		$url = $params->sandbox? 'test.payu.in/_payment' : 'secure.payu.in/_payment';
		if ($secure) {
			$url = 'https://' . $url;
		}
		return $url;
	}

	/**
	 * Store log
	 *
	 * @param   string  $data     data.
	 * @param   array   $logdata  data.
	 *
	 * @since   1.0
	 * @return  list.
	 */
	function Storelog($name, $logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$my = JFactory::getUser();

		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name.'.log',
				'text_entry_format' => $options
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		JLog::add($logEntry);

		//	$logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		//  $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}
}
