<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */

	// Check to ensure this file is within the rest of the framework
	defined('JPATH_BASE') or die();
	if(JVERSION>=1.6)
	{
			class JFormFieldAupfilesocialads extends JFormField
			{
				/**
				 * The form field type.
				 *
				 * @var		string
				 * @since	1.6
				 */
				public $type = 'Aupfilesocialads';

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
				if($this->id=='jform_params_aupfilesocialads')
					{
					echo '<div style="float:left"><a href="'.JURI::root().'plugins/payment/alphauserpoints/alphauserpoints/socialads_aup.zip"> '.JText::_('AUP_CLK').'</a><span> '.JText::_('AUP_INST').' </span><a href="'.JURI::base().'index.php?option=com_alphauserpoints&task=plugins" target="_blank">'.JText::_('HERE').'</a>
						<a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'.JText::_('CLK_DOC').'</a></div>';
				}
				} //function
				
			}
	}
	else
	{
		class JElementAupfilesocialads extends JElement
		{
			var $_name = 'Aupfilesocialads';
			function fetchElement($name, $value, &$node, $control_name)
			{
				if($name=='aupfilesocialads')
				{
				return '<div style="float:left"><a href="'.JURI::root().'plugins/payment/alphauserpoints/socialads_aup.zip"> '.JText::_('AUP_CLK').'</a><span> '.JText::_('AUP_INST').' </span><a href="'.JURI::base().'index.php?option=com_alphauserpoints&task=plugins" target="_blank">'.JText::_('HERE').'</a>
				</br><a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'.JText::_('CLK_DOC').'</a></div>';
			}
			}
		}
	}
?>
