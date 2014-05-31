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
	public $type = 'Cron';

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
		$return	=	'<a href="'.$jgiveLogFilePath.'">jGive log file</a> <br>';

	return $return;
	} //function

}
?>
