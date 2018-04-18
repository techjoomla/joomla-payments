<?php
/**
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * APU socialads file field
 *
 * @package     CPG
 * @subpackage  site
 * @since       1.0
 */
class JFormFieldAupfilesocialads extends JFormField
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
			echo '<div style="float:left"><a href="' . JURI::root() . 'plugins/payment/altauserpoints/altauserpoints/socialads_aup.zip"> '
			. JText::_('AUP_CLK') . '</a><span> ' . JText::_('AUP_INST') . ' </span><a href="'
			. JURI::base() . 'index.php?option=com_altauserpoints&task=plugins" target="_blank">' . JText::_('HERE')
			. '</a><a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'
			. JText::_('CLK_DOC') . '</a></div>';
		}
	}
}
