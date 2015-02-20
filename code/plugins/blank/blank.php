<?php
/**
 * @package     Joomla.Payment.Plugin
 * @subpackage  com_ABC-Payment
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
$lang->load('plg_payment_blank', JPATH_ADMINISTRATOR);

if (JVERSION >= '1.6.0')
{
	require_once JPATH_SITE . '/plugins/payment/blank/blank/helper.php';
}
else
{
	require_once JPATH_SITE . '/plugins/payment/blank/helper.php';
}
$lang =  JFactory::getLanguage();
$lang->load('plg_payment_blank', JPATH_ADMINISTRATOR);

/**
 * blank Payment class.
 *
 * @package  Joomla.Payment.Plugin
 * 
 * @since    1.0
 */
class plgpaymentblank extends JPlugin
{
	/**
	 * @var		string	Store Payment name.
	 * @var		string	Log store.
	 * @since	1.0
	 */
	var $_payment_gateway = 'payment_blank';

	var $_log = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $subject  Payment Status codes And Respective Alias in Framework.
	 * @param   array  $config   Payment config.
	 * 
	 * @see      JPlugin
	 * 
	 * @since    1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();


		//Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus= array(
			'Completed'  => 'C',
			'Pending'  => 'P',
			'Failed'=>'E','Denied'=>'D',
			'Refunded'=>'RF','Canceled_Reversal'=>'CRV',
			'Reversed'=>'RV'
		);
	}

	/**
	 * This function falls under STEP 1 of the Common Payment Gateway flow,
	 * It is Used to Build List of Payment Gateway in the respective Components.
	 * 
	 * @param   array  $config  list of payment plugin names from component settings/config.
	 * 
	 * @return  Object  with 'name' set as in the param plugin_name and 'id' set as the plugin's filename
	 * 
	 * @since   1.0
	 */
	public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
				/*check if payment plugin is in config*/
				return;
		}

		$obj 		= new stdClass;
		$obj->name = $this->params->get('plugin_name');
		$obj->id	= $this->_name;

		return $obj;
	}

	/**
	 * This function falls under STEP 1 of the Common Payment Gateway flow,
	 * It Constructs the Payment form in case of On Site Payment gateways like Auth.net
	 * OR constructs the Submit button in case of offsite
	 * 
	 * @param   object  $vars  list of all data required by payment plugin constructed by the component.
	 * 
	 * @return  string  HTML  Paymet form Html.
	 * 
	 * @since   1.0 
	 */
	public function onTP_GetHTML($vars)
	{
		$vars->action_url = 'http://secure.blank.com/transaction/transaction.do?command=initiateTransaction';//$plgPaymentPaypalHelper->buildPaypalUrl();
		// Add on any payment plugin specific data to $vars
		$html = $this->buildLayout($vars);

		// Pass $vars to buildLayout to get the payment form/html.

		return $html;
	}

	/**
	 * Reconstruct Payment form - to avoid change in amount or etc from Hidden form
	 * 
	 * @param   array  $data  Post data of item form.
	 * @param   array  $vars  list of all data required by payment plugin constructed by the component.
	 * 
	 * @return  void.
	 * 
	 * @since   1.0 
	 */
	public function onTP_ProcessSubmit($data, $vars)
	{
		// Take this receiver email address from plugin if component not provided it
		// Reconstruct Payment FORM using $vars

		$submitVaues['order_id'] =$vars->order_id;
		$submitVaues['user_id'] =$vars->user_id;
		$submitVaues['return'] =$vars->return;
		$submitVaues['amount'] =$vars->amount;
		$submitVaues['plugin_payment_method'] ='onsite';
		$submitVaues['cardfname'] =$data['cardfname'];
		$submitVaues['cardlname'] =$data['cardlname'];
		$submitVaues['cardaddress1'] =$data['cardaddress1'];
		$submitVaues['cardaddress2'] =$data['cardaddress2'];
		$submitVaues['cardcity'] =$data['cardcity'];
		$submitVaues['cardstate'] =$data['cardstate'];
		$submitVaues['cardzip'] =$data['cardzip'];
		$submitVaues['cardcountry'] =$data['cardcountry'];
		$submitVaues['email'] =$data['email'];
		$submitVaues['cardnum'] =$data['cardnum'];
		$submitVaues['cardexp'] =$data['cardexp'];
		$submitVaues['cardcvv'] =$data['cardcvv'];

		// Build action URL
		$plgPaymentPaypalHelper = new plgPaymentPaypalHelper;
		$postaction = $plgPaymentPaypalHelper->buildPaypalUrl();
		/* for offsite plugin */
		$postvalues = http_build_query($submitVaues);
		header('Location: ' . $postaction . '?' . $postvalues);
	}

	/**
	 * This function calls under STEP 3 of the Common Payment Gateway flow
	 * If Process on the post data from the payment and pass a fixed format data to component for further process.
	 * 
	 * @param   array  $data  ost data from gateway to notify url.
	 * 
	 * @return  associative array  Gateway specific fixed format data required by the component to process payment.
	 * 
	 * @since   1.0 
	 */
	public function onTP_Processpayment($data)
	{
		/**
		 * NOTE : for onsite payment the code for sending data to payment
		 * gateway via cURL or any other method will come here
		 * */

		//~ $verify = PlgPaymentblankHelper::validateIPN($data);
//~ 
		//~ /*verification of IPN*/
		//~ if (!$verify)
		//~ {
			//~ return false;
		//~ }

		// Translate the status response depending upon you payment gateway.
		$payment_status = $this->translateResponse($data['payment_status']);

		$result = array(
						'order_id' => $data['custom'],
						'transaction_id' => $data['txn_id'],
						'buyer_email' => $data['payer_email'],
						'status' => $payment_status,
						'subscribe_id' => $data['subscr_id'],
						'txn_type' => $data['txn_type'],
						'total_paid_amt' => $data['mc_gross'],
						'raw_data' => $data,
						'error' => $error,
						);

		return $result;
	}

	/**
	 * This function calls under STEP 3 of the Common Payment Gateway flow
	 * It Logs the payment process data.
	 * 
	 * @param   array  $data  ost data from gateway to notify url.
	 * 
	 * @return  void.
	 * 
	 * @since   1.0 
	 */
	public function onTP_Storelog($data)
	{
		$log = PlgPaymentBlankHelper::Storelog($this->_name, $data);
	}

	/**
	 * Internal use functions  @TODO move to common helper
	 * translate the status response depending upon you payment gateway
	 * 
	 * @param   string  $payment_status  Short form of payment status e.g. P:pending.
	 * 
	 * @return  string  Return Status name.
	 * 
	 * @since   1.0 
	 */
	public function translateResponse($payment_status)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == $payment_status)
			{
				return $value;
			}
		}
	}

	/**
	 * Internal use functions  @TODO move to common helper
	 * Builds the layout to be shown, along with hidden fields.
	 * 
	 * @param   array   $vars    Payment related varible array.
	 * @param   string  $layout  Payment layout name.
	 * 
	 * @return  string  html of form.
	 * 
	 * @since   1.0 
	 */
	public function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables

		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Internal use functions  @TODO move to common helper
	 * Builds the layout path and check override the layout of payment.
	 * 
	 * @param   string  $layout  Payment layout name.
	 * 
	 * @return  string  path of layout.
	 * 
	 * @since   1.0 
	 */
	public function buildLayoutPath($layout)
	{
		$app = JFactory::getApplication();
		$core_file = dirname(__FILE__) . DS . $this->_name . DS . 'tmpl' . DS . 'default.php';
		$override = JPATH_BASE . DS . 'templates' . DS . $app->getTemplate() . DS . 'html' . DS;
		$override .= 'plugins' . DS . $this->_type . DS . $this->_name . DS . $layout . ' .php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}

	}
}
