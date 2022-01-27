<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * PlgPaymentOgoneHelper
 *
 * @package     CPG
 * @subpackage  site
 * @since       2.2
 */
class PlgPaymentOgoneHelper
{
	/**
	 * buildOgoneUrl.
	 *
	 * @param   string  $secure  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  secure
	 */
	public function buildOgoneUrl($secure = true)
	{
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
		$my      = Factory::getUser();
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

	/**
	 * ValidateIPN
	 *
	 * @param   string  $data  data
	 *
	 * @since   2.2
	 *
	 * @return   string  data
	 */
	public function validateIPN($data)
	{
		$plugin = PluginHelper::getPlugin('payment', 'ogone');
		$params = json_decode($plugin->params);

		require_once JPATH_SITE . '/plugins/payment/ogone/ogone/lib/Response.php';
		$options = array('sha1OutPassPhrase' => $params->secretkey);

		/* Define array of values returned by Ogone
		Parameters are validated and filtered automatically
		so it is safe to specify a superglobal variable
		like $_POST or $_GET if you don't want to
		specify all parameters manually*/
		$params   = $data;

		// Instantiate response
		$response = new Ogone_Response($options, $params);

		/* Check if response by Ogone is valid
		The SHA1Sign is calculated automatically and
		verified with the SHA1Sign provided by Ogone*/

		if (!$response->isValid())
		{
			return false;
		}

		return true;
	}
}
