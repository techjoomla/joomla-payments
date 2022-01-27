<?php
/**
 * @package payment plugin
 * @copyright Copyright (C) 2009 -2022 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('JPATH_BASE') or die();

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class JFormFieldLogfile extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Logfile';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		$hint = $this->hint;

		$logFilePath = Route::_(Uri::root(true) . 'plugins/payment/adaptive_paypal/adaptive_paypal/logBeforePayment_' . $hint . '.log');
		$return	= '<div style="clear:both"><a href="' . $logFilePath.'">' . $hint . 'log file</a> <br></div>';
		return $return;
	}
}
