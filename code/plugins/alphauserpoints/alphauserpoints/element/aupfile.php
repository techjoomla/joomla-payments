<?php 
 /**
	* @package JomSocial Network Suggest
	* @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link     http://www.techjoomla.com
	*/ 

	// Check to ensure this file is within the rest of the framework
	defined('JPATH_BASE') or die();
	if(JVERSION>=1.6)
	{
			class JFormFieldAupfile extends JFormField
			{
				/**
				 * The form field type.
				 *
				 * @var		string
				 * @since	1.6
				 */
				public $type = 'Aupfile';

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
					return '<a href="'.JURI::root().'plugins/payment/alphauserpoints/alphauserpoints/jticketing_aup.zip"> '.JText::_('AUP_CLK').'</a><span> '.JText::_('AUP_INST').' </span><a href="'.JURI::base().'index.php?option=com_alphauserpoints&task=plugins" target="_blank">'.JText::_('HERE').'</a>
				</br><a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'.JText::_('CLK_DOC').'</a>';
		
				} //function
				
			}
	}
	else
	{
		class JElementAupfile extends JElement
		{
			var $_name = 'Aupfile';
			function fetchElement($name, $value, &$node, $control_name)
			{

				return '<a href="'.JURI::root().'plugins/payment/alphauserpoints/jticketing_aup.zip"> '.JText::_('AUP_CLK').'</a><span> '.JText::_('AUP_INST').' </span><a href="'.JURI::base().'index.php?option=com_alphauserpoints&task=plugins" target="_blank">'.JText::_('HERE').'</a>
				</br><a href="http://techjoomla.com/documentation-for-socialads/configuring-payment-plugins-for-socialads.html" target="_blank">'.JText::_('CLK_DOC').'</a>';
			}
		}
	}
?>
