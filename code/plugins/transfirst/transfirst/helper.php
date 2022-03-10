<?php
/**
 * @package transfirst
 * @copyright Copyright (C) 2009 -2021 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;

/**
 * @version    SVN: <svn_id>
 * @package    CPG
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

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
		$plugin = PluginHelper::getPlugin('payment', 'transfirst');
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
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$my      = Factory::getUser();

		Log::addLogger(
			array(
			'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
			'text_entry_format' => $options
			),
			Log::INFO, $logdata['JT_CLIENT']
		);

		$logEntry       = new LogEntry('Transaction added', Log::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		Log::add($logEntry);

		// $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		// $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}
}
