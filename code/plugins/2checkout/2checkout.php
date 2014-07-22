<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/2checkout/2checkout/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/2checkout/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_2checkout', JPATH_ADMINISTRATOR);
class  plgPayment2checkout extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		
		//Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus= array(
 	 		 'deposited'  => 'C',
			 'pending'  => 'P',
			 'approved'=>'p',
			 'declined'=>'X',
 	     'Refunded'=>'RF','ERROR'  => 'E');
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
	$layout=trim($layout);
	if(empty($layout))
	$layout='default';
	
		$app = JFactory::getApplication();
	
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout.'.php';
		$override		= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html'. '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Paypal
	function onTP_GetHTML($vars)
	{
     $vars->action_url = 'https://www.2checkout.com/checkout/purchase';
		 $vars->sid = $this->params->get('sid','');
		 $vars->demo = $this->params->get('demo',0) ? 'Y' : 'N';
		 $vars->lang = $this->params->get('lang','en');
		 $vars->pay_method = $this->params->get('pay_method','cc');	
		

		$html = $this->buildLayout($vars);
		return $html;
	}
	
	function onTP_ProcessSubmit($data,$vars) 
	{
		$submitVaues['sid'] =$this->params->get('sid','');
		$submitVaues['cart_order_id'] =$vars->order_id;
		$submitVaues['total'] = sprintf('%02.2f',$vars->amount);
		$submitVaues['demo'] =$this->params->get('demo',0) ? 'Y' : 'N';
		$submitVaues['merchant_order_id'] =$vars->order_id;
		$submitVaues['fixed'] ='Y';
		$submitVaues['lang'] =$this->params->get('lang','en');
		$submitVaues['return_url'] =$vars->return;
	
		$submitVaues['x_receipt_link_url'] =$vars->notify_url;
		//$submitVaues['currency_code'] =$vars->currency_code;
		$submitVaues['pay_method'] =strtoupper($this->params->get('pay_method','cc'));
		$submitVaues['id_type'] ='1';
		
		
		$postaction = 'https://www.2checkout.com/checkout/purchase';
		/* for offsite plugin */
		$postvalues = http_build_query($submitVaues);
		header('Location: '.$postaction.'?' . $postvalues);
	}
	
	function onTP_Processpayment($data,$vars=array())  
	{
			$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		$secret = $this->params->get('secret','cc');	
		/*$verify = plgPayment2checkoutHelper::validateIPN($data,$secret);
		if (!$verify) { return false; 
		}	*/

		$id = array_key_exists('vendor_order_id', $data) ? (int)$data['vendor_order_id'] : -1;
		
		//3.compare response order id and send order id in notify URL 
		$res_orderid='';
		
		if($isValid ) {
		$res_orderid = $id;
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
				$retrunamount =  (float)$data['total'];
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

		$message_type=$data['message_type'];
		if($trxnstatus == 'ERROR'){
			$payment_status=$this->translateResponse($trxnstatus);
		}else{
			$payment_status=$this->translateResponse($data['invoice_status']);
		}
		
		if($message_type == 'REFUND_ISSUED'){
			$payment_status='RF';
		}

		$result = array();
		if($id)
		{
			$result = array(
						'order_id'=>$id,
						'transaction_id'=>$data['order_number'],
						'buyer_email'=>$data['email'],
						'status'=>$payment_status,
						'subscribe_id'=>$data['subscr_id'],
						'txn_type'=>$data['pay_method'],
						'total_paid_amt'=>$data['total'],
						'raw_data'=>$data,
						'error'=>$error,
						);
		}
		return $result;						
	}	
	
	function translateResponse($invoice_status){
			
    	foreach($this->responseStatus as $key=>$value)
				{
					if($key==$invoice_status)
					return $value;		
				}
	}
	function onTP_Storelog($data)
	{
			$log = plgPayment2checkoutHelper::Storelog($this->_name,$data);
	
	}
}
