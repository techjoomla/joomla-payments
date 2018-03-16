<?php
/**
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * APU file field
 *
 * @package     CPG
 * @subpackage  site
 * @since       1.0
 */
class JFormFieldAupfile extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $type = 'Aupfile';

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
		return '<a href="' . JURI::root() . 'plugins/payment/altauserpoints/altauserpoints/jticketing_aup.zip"> '
		. JText::_('AUP_CLK') . '</a><span> ' . JText::_('AUP_INST') . ' </span><a href="'
		. JURI::base() . 'index.php?option=com_altauserpoints&task=plugins" target="_blank">' . JText::_('HERE')
		. '</a></br><a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'
		. JText::_('CLK_DOC') . '</a>';
	}
}
