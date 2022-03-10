<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;

/**
 * PlgPaymentByorderHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentByorderHelper
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
	public function buildByorderHelperUrl($secure = true)
	{
		return '';
	}

	/**
	 * Storelog.
	 *
	 * @param   string  $name     name
	 *
	 * @param   string  $logdata  logdata
	 *
	 * @since   2.2
	 *
	 * @return   string  name
	 */
	public function Storelog($name, $logdata)
	{
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$path    = dirname(__FILE__);
		$my      = Factory::getUser();

		Log::addLogger(
			array(
			'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.php',
			'text_entry_format' => $options
			),
			Log::INFO,
			$logdata['JT_CLIENT']
			);

		$logEntry       = new LogEntry('Transaction added', Log::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		Log::add($logEntry);

		//        $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
		//   $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}
}
