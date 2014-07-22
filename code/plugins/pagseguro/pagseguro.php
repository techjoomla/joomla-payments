<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/pagseguro/pagseguro/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/pagseguro/helper.php');
	
	require_once JPATH_SITE.'/plugins/payment/pagseguro/lib/PagSeguroLibrary.php';
class  plgPaymentPagseguro extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		/*
1	Waiting for payment : the buyer initiated the transaction, but so far the PagSeguro not received any payment information.
2	In Review : The buyer chose to pay with a credit card and PagSeguro is analyzing the risk of the transaction.
3	Pay : the transaction was paid by the buyer and PagSeguro already received a confirmation from the financial institution responsible for processing.
4	Available : the transaction was paid and reached the end of their period of release and returned without having been without any dispute opened.
5	In dispute : the buyer, the deadline for release of the transaction, opened a dispute.
6	Returned : The value of the transaction was returned to the buyer.
7	Canceled : the transaction was canceled without having been finalized.
*/
		//Define Payment Status codes in Pagseguro  And Respective Alias in Framework
		$this->responseStatus= array(
 	'1'=>'P',
 	'2'=>'UR',
 	'3'=>'C',
  '4'=>'RV',
  '5'=>'DP',
  '6'=>'RT',
  '7'=>'D',
  'ERROR'  => 'E',
		);
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'default.php';
		$override		= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
		if(JFile::exists($override))
		{
			return $override;
		}
		else
		{
	  	return  $core_file;
	}
	}
	
	//Builds the layout to be shown, along with hidden fields.
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

	// Used to Build List of Payment Gateway in the respective Components
	function onTP_GetInfo($config)
	{

	if(!in_array($this->_name,$config))
	return;
		$obj 		= new stdClass;
		$obj->name 	=$this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Pagseguro
	function onTP_GetHTML($vars)
	{
		$vars->sellar_email = $this->params->get('sellar_email');
		$vars->token = $this->params->get('token');		
		$plgPaymentPagseguroHelper = new plgPaymentPagseguroHelper();
		$vars->action_url = $plgPaymentPagseguroHelper->buildPagseguroUrl($vars,1);
		//Take this receiver email address from plugin if component not provided it

		
		$html = $this->buildLayout($vars);

		return $html;
	}

	
	
	function onTP_Processpayment($data,$vars=array()) 
	{
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		
		$vars->sellar_email = $this->params->get('sellar_email');
		$vars->token = $this->params->get('token');
		$plgPaymentPagseguroHelper = new plgPaymentPagseguroHelper();
		$verified_Data = $plgPaymentPagseguroHelper->validateIPN($data,$vars);
		//if (!$verify) { return false; }	
		$pstatus=$verified_Data['payment_statuscode'];
		
		//3.compare response order id and send order id in notify URL 
		$res_orderid='';
		if($isValid ) {
		 $res_orderid = $verified_Data['order_id'];
			if(!empty($vars) && $res_orderid != $vars->order_id )
			{
				$trxnstatus = 'ERROR';
				$isValid = false;
				$error['desc'] = "ORDER_MISMATCH " . " Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$res_orderid;
			}
		}
				// amount check
		if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount;
				$retrunamount =  (float)$verified_Data['total_paid_amt'];
				$epsilon = 0.01;
				
				if(($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus = 'ERROR';  // change response status to ERROR FOR AMOUNT ONLY
					$isValid = false;
					$error['desc'] = "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
				}
			}
		}
		// END OF AMOUNT CHECK
		
		if($trxnstatus == 'ERROR'){
			$status= $this->translateResponse($trxnstatus);
		}else {
			$status=$this->translateResponse($pstatus);		
		}
		
		if(!$status) {
			$status='P';
		}
		

		$result = array(
						'order_id'=>$verified_Data['order_id'],
						'transaction_id'=>$verified_Data['transaction_id'],
						'buyer_email'=>$verified_Data['buyer_email'],
						'status'=>$status,
						'txn_type'=>$verified_Data['payment_method'],
						'total_paid_amt'=>$verified_Data['total_paid_amt'],
						'raw_data'=>$verified_Data['raw_data'],
						'error'=>$error,
						);
		return $result;
	}	
	
	function translateResponse($payment_status){
			foreach($this->responseStatus as $key=>$value)
			{
				if($key==$payment_status)
				return $value;		
			}
	}
	function onTP_Storelog($data)
	{
		$plgPaymentPagseguroHelper = new plgPaymentPagseguroHelper;
			$log = $plgPaymentPagseguroHelper->Storelog($this->_name,$data);
	
	}	
}
