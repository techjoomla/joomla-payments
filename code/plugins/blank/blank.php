<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
//require_once JPATH_COMPONENT . DS . 'helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_blank', JPATH_ADMINISTRATOR);
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/blank/blank/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/blank/helper.php');
class plgpaymentblank extends JPlugin 
{
	var $_payment_gateway = 'payment_blank';
	var $_log = null;

	function __construct(& $subject, $config)
	{
			parent::__construct($subject, $config);
		/*
		 * @var $this->responseStatus	array	Payment Status codes And Respective Alias in Framework
		 * */
		$this->responseStatus= array(
		 'Completed'  => 'C','Pending'  => 'P',
		 'Failed'=>'E','Denied'=>'D',
		 'Refunded'=>'RF',
		 'Canceled_Reversal'=>'CRV',
		 'Reversed'=>'RV','ERROR'  => 'E');
		
	}

	
	/* This function falls under STEP 1 of the Common Payment Gateway flow
	 * It is Used to Build List of Payment Gateway in the respective Components
	 *
	 * @param $config	array	list of payment plugin names from component settings/config
	 * @return object	Object	with 'name' set as in the param plugin_name and 'id' set as the plugin's filename
	 * */
	function onTP_GetInfo($config)
	{
		if(!in_array($this->_name,$config))	/*check if payment plugin is in config*/
			return;
		$obj 		= new stdClass;
		$obj->name 	=$this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}

	/* This function falls under STEP 2 of the Common Payment Gateway flow
	 * It Constructs the Payment form in case of On Site Payment gateways like Auth.net
	 * OR constructs the Submit button in case of offsite
	 *
	 * @param $vars	object	list of all data required by payment plugin constructed by the component
	 * @return string	HTML	to display
	 * */
	function onTP_GetHTML($vars)
	{
		/* add on any payment plugin specific data to $vars*/
		$vars->action_url = plgPaymentBlankHelper::buildBlankUrl();
		$html = $this->buildLayout($vars);	/*pass $vars to buildLayout to get the payment form/html */

		return $html;
	}

/* This function falls under STEP 3 of the Common Payment Gateway flow
 * If Process on the post data from the payment and pass a fixed format data to component for further process
 *
 * @param $data	array	Post data from gateway to notify url
 * @return associative	array	gateway specific fixed format data required by the component to process payment
 * */
	function onTP_Processpayment($data,$vars)
	{
		/*NOTE : for onsite payment the code for sending data to payment gateway via cURL or any other method will come here*/
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		$verify = plgPaymentBlankHelper::validateIPN($data);	/*verification of IPN*/
		if (!$verify) { return false; }
		
		//3.compare response order id and send order id in notify URL 
		$res_orderid='';
		/* // SAMPLE CODE
		 $res_orderid = $result->InvoiceReference; // THIS SHOULD BE RESPONSE ORDERID
		if($isValid ) {
		if(!empty($vars) && $res_orderid != $vars->order_id )
			{
				$trxnstatus = 'ERROR';
				$isValid = false;
				$error['desc'] = "ORDER_MISMATCH " . " Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$res_orderid;
			}
		}*/
		
	// SAMPLE CODE TO CHECK RESPONSE AMOUNT AND ORIGINAL AMOUNT
		/*if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount; 
				$retrunamount =  (float)$gross_amt; //RESPONSE AMOUNT
				$epsilon = 0.01;
				
				if(($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus = 'ERROR';  // change response status to ERROR FOR AMOUNT ONLY
					$isValid = false;
					$error['desc'] = "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
				}
			}
		}*/
		/*translate the status response depending upon you payment gateway*/
		$payment_status='';
		// Translaet Payment status
		if($trxnstatus == 'ERROR'){
			$payment_status= $this->translateResponse($trxnstatus);
		}else {
			$payment_status=$this->translateResponse($data['payment_status']);	
		}
		

		$result = array(
						'order_id'=>$data['custom'],
						'transaction_id'=>$data['txn_id'],
						'buyer_email'=>$data['payer_email'],
						'status'=>$payment_status,
						'subscribe_id'=>$data['subscr_id'],
						'txn_type'=>$data['txn_type'],
						'total_paid_amt'=>$data['mc_gross'],
						'raw_data'=>$data,
						'error'=>$error,
						);
		return $result;
	}

/* This function falls under STEP 3 of the Common Payment Gateway flow 
 * It Logs the payment process data */
	function onTP_Storelog($data)
	{
		$log = plgPaymentBlankHelper::Storelog($this->_name,$data);
	}

/* Internal use functions  @TODO move to common helper
translate the status response depending upon you payment gateway*/
	function translateResponse($payment_status){
		foreach($this->responseStatus as $key=>$value)
		{
			if($key==$payment_status)
			return $value;
		}
	}

/* Internal use functions  @TODO move to common helper
 * Builds the layout to be shown, along with hidden fields.
 * */
	function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include($layout);
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

/** Internal use functions  @TODO move to common helper*/
	function buildLayoutPath($layout) {
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'default.php';
		$override	= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
		if(JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}
	}
}	


