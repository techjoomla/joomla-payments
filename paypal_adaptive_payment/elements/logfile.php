<?php

defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');

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

		$jgiveLogFilePath = JRoute::_(JUri::root().'plugins/payment/adaptive_paypal/adaptive_paypal/logBeforePayment_com_jgive.log');
		$return	=	'<div style="clear:both"><a href="'.$jgiveLogFilePath.'">jGive log file</a> <br></div>';

	return $return;
	} //function

}
?>
