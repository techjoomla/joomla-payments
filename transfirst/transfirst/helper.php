<?php
/**
 * @version    SVN: <svn_id>
 * @package    CPG
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * plgPaymentTransfirstHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentTransfirstHelper
{
	/**
	 * Build submit URL according to plugin configuration.
	 *
	 * @since   2.2
	 *
	 * @return   string url
	 */
	public function buildTransfirstUrl()
	{
		$plugin = JPluginHelper::getPlugin('payment', 'transfirst');
		$params = json_decode($plugin->params);
		$sandboxUrl = 'https://ws.cert.processnow.com:443/portal/merchantframework/MerchantWebServices-v1?wsdl';
		$url    = $params->sandbox ? $sandboxUrl : 'https://ws.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl';

		return $url;
	}

	/**
	 * Log the payment response.
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
		$my      = JFactory::getUser();

		JLog::addLogger(
			array(
			'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.log',
			'text_entry_format' => $options
			),
			JLog::INFO, $logdata['JT_CLIENT']
		);

		$logEntry       = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		JLog::add($logEntry);

		// $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		// $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}
}
