<?php
/**
 * @package    CPG
 * @author     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * APU socialads file field
 *
 * @package     CPG
 * @subpackage  site
 * @since       1.0
 */
class JFormFieldAupfilesocialads extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $type = 'Aupfilesocialads';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.0
	 */
	protected function getInput()
	{
		if ($this->id == 'jform_params_aupfilesocialads')
		{
			$html = '<div style="float:left"><a href="' . JURI::root() . 'plugins/payment/alphauserpoints/alphauserpoints/socialads_aup.zip"> '
			. Text::_('AUP_CLK') . '</a><span> ' . Text::_('AUP_INST') . ' </span><a href="'
			. JURI::base() . 'index.php?option=com_alphauserpoints&task=plugins" target="_blank">' . Text::_('HERE')
			. '</a>. <a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'
			. Text::_('CLK_DOC') . '</a></div>';

			return $html;
		}
	}
}
