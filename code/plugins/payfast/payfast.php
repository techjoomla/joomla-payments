<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


jimport( 'joomla.plugin.plugin' );
if(version_compare(JVERSION, '1.6.0', 'ge')) 
	require_once(JPATH_SITE.'/plugins/payment/payfast/payfast/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/payfast/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_payfast', JPATH_ADMINISTRATOR);
class  plgPaymentPayfast extends JPlugin
{
	private $validHosts = array(
        'www.payfast.co.za',
        'sandbox.payfast.co.za',
        'w1w.payfast.co.za',
        'w2w.payfast.co.za',
        );
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		
		//Define Payment Status codes in payfast  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'COMPLETE'  => 'C',
 	 'ERROR' => 'E'  
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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Payu
	/**
	 * RETURN PAY HTML FORM
	 * */
	function onTP_GetHTML($vars)
	{
		$plgPaymentPayfastHelper= new plgPaymentPayfastHelper();
		$vars->action_url = $plgPaymentPayfastHelper->buildPayfastUrl();
		//Take this receiver email address from plugin if component not provided it

			$vars->merchant_id = $this->params->get('merchant_id','');
			$vars->merchant_key = $this->params->get('merchant_key','');	
			//$this->preFormatingData($vars);	 // fomating on data
			$html = $this->buildLayout($vars);

		return $html;
	}
	
	/* This function falls under STEP 3 of the Common Payment Gateway flow
 * If Process on the post data from the payment and pass a fixed format data to component for further process
 *
 * @param $data	array	Post data from gateway to notify url
 * @return associative	array	gateway specific fixed format data required by the component to process payment
 * */
	function onTP_Processpayment($data,$vars=array()) 
	{
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		
	// 1.Check IPN data for validity (i.e. protect against fraud attempt)
		$isValid = $this->isValidIPN($data);
		if(!$isValid){
			$data['error'] = 'Invalid response received.';
		}
		
		//2. Check that merchant_id is correct
		if($isValid ) {
			if($this->getMerchantID() != $data['merchant_id']) {
				$isValid = false;
				$data['error'] = "The received merchant_id does not match the one that was sent.";
			}
		}
				//3.compare response order id and send order id in notify URL 
		if($isValid ) {
			if(!empty($vars) && $data['custom_str1'] != $vars->order_id )
			{
				$isValid = false;
				$trxnstatus = 'ERROR';
				$data['error'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$data['custom_str1'];
			}
		}
		// Check that the amount is correct
		
		if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount;
				$return_resp['status'] ='0';
				$data['total_paid_amt'] =  (float)$data['amount_gross'];
				$epsilon = 0.01;
				
				if(($order_amount - $data['total_paid_amt']) > $epsilon)
				{
					$trxnstatus = 'ERROR';
					$isValid = false;
					$data['error'] = "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$data['total_paid_amt'];
				}
			}
		}
		
		// Translaet Payment status
		if($trxnstatus == 'ERROR'){
			$newStatus= $this->translateResponse($trxnstatus);
		}
		else
		{
			$newStatus= $this->translateResponse($data['payment_status']);
		}
			
		
		// Fraud attempt? Do nothing more!
		//if(!$isValid) return false;
		
		
		
		$data['status']=$newStatus;

		//Error Handling
		$error=array();
		$error['code']	='ERROR';
		$error['desc']	=(isset($data['error'])?$data['error']:'');

		$result = array(
						'order_id'=>$data['custom_str1'],
						'transaction_id'=>$data['pf_payment_id'],
						'buyer_email'=>$data['email_address'],
						'status'=>$newStatus,
						'txn_type'=>'',
						'total_paid_amt'=>(float)$data['amount_gross'],
						'raw_data'=>$data,
						'error'=>$error ,
						);
		return $result;
	}	
		/**
	 * Validates the incoming data.
	 */
	private function isValidIPN($data)
	{
		// 1. Check valid host
		$validIps = array();
		foreach($this->validHosts as $validHost){
			//Returns a list of IPv4 addresses to which the Internet host specified by hostname resolves. 
			$ips = gethostbynamel($validHost);
			if($ips !== false) {
				$validIps = array_merge($validIps, $ips);	
			}
		}

		$validIps = array_unique($validIps);
		if(!in_array($_SERVER['REMOTE_ADDR'], $validIps)) {
			//return false;
		}

		// 2. Check signature
		// Build returnString from 'm_payment_id' onwards and exclude 'signature'
		foreach($data as $key => $val ) {
				if($key == 'm_payment_id') $returnString = '';
				if(! isset($returnString)) continue;
				if($key == 'signature') continue;
				$returnString .= $key . '=' . urlencode($val) . '&';
		}

		$returnString = substr($returnString, 0, -1);
						
		if(md5($returnString) != $data['signature']) {
			return false;
		}
		
		// 3. Call PayFast server for validity check
		$header = "POST /eng/query/validate HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($returnString) . "\r\n\r\n";
		
		// Connect to server
		$fp = fsockopen($this->getCallbackURL(), 443, $errno, $errstr, 10);
		
		if (!$fp) {
			// HTTP ERROR
			return false;
		} else {
			 // Send command to server
			fputs($fp, $header . $returnString);
			
			// Read the response from the server
			while(! feof($fp)) {
				$res = fgets($fp, 1024);
				if (strcmp($res, "VALID") == 0) {
					fclose($fp);
					return true;
				}
			}
		}
		
		fclose($fp);
		return false;
	} // end of isValidIPN
	
	function translateResponse($payment_status){
			foreach($this->responseStatus as $key=>$value)
			{
				if($key==$payment_status){
					return $value;		
				}
			}
		
	}
	function onTP_Storelog($data)
	{
			$log = plgPaymentPayfastHelper::Storelog($this->_name,$data);
	
	}	
	/*
		@params $vars :: object
		@return $vars :: formatted object 
	*/
	function preFormatingData($vars)
	{		
		foreach($vars as $key=>$value)
		{
			$vars->$key=trim($value);	
			if($key=='amount')
				$vars->$key=round($value);
		}	
	}
		/**
	 * Gets the PayFast Merchant ID
	 */
	function getMerchantID()
	{
		//$sandbox = $this->params->get('sandbox',0);
		return trim($this->params->get('merchant_id',''));
		
	}
	/**
	 * Gets the IPN callback URL
	 */
	private function getCallbackURL()
	{
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			return 'ssl://sandbox.payfast.co.za';
		} else {
			return 'ssl://www.payfast.co.za';
		}
	}

	
	
}
