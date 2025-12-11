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
 * AUP q2c rule file download link
 *
 * @since  1.0
 */
class JFormFieldAupFileQuick2cart extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.0
	 */
	public $type = 'aupfilequick2cart';

	/**
	 * Method to get the field input markup.
	 *
	 * @since	1.0
	 *
	 * @return  string
	 */
	protected function getInput()
	{
		if ($this->id == 'jform_params_aupfilequick2cart')
		{
			$html = '<div style="float:left"><a href="' . JURI::root() . 'plugins/payment/alphauserpoints/alphauserpoints/quick2cart_aup.zip"> ' .
				Text::_('AUP_CLK') . '</a><span> ' . Text::_('AUP_INST') . ' </span><a href="' .
				JURI::base() . 'index.php?option=com_alphauserpoints&task=plugins" target="_blank">' . Text::_('HERE') .
				'</a>. <a href="https://techjoomla.com/documentation-for-quick2cart/configuring-common-payment-gateway.html" target="_blank">' .
				Text::_('CLK_DOC') . '</a></div>';

			return $html;
		}
	}
}
