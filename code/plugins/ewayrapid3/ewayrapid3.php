<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(version_compare(JVERSION, '1.6.0', 'ge')) 
	require_once(JPATH_SITE.'/plugins/payment/ewayrapid3/ewayrapid3/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/ewayrapid3/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_ewayrapid3', JPATH_ADMINISTRATOR);
class  plgPaymentEwayrapid3 extends JPlugin
{
	private $ewayService = null;
	
	function __construct(& $subject, $config)
	{
		$config = array_merge($config, array(
			'ppName'		=> 'ewayrapid3',
			'ppKey'			=> 'PLG_AKPAYMENT_EWAYRAPID3_TITLE',
			'ppImage'		=> '',
		));
		//Define Payment Status codes in eway  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'True'  => 'C','ERROR'  => 'E');
		
		parent::__construct($subject, $config);
		
		// Load libraray and initialize settings
		require_once dirname(__FILE__).'/ewayrapid3/library/Rapid3.0.php';
		$service = new RapidAPI();
		$sandbox = $this->params->get('sandbox',0);
		if($sandbox) {
			$soapURL = 'https://api.sandbox.ewaypayments.com/Soap.asmx?WSDL';
			$key = trim($this->params->get('sb_key',''));
			$password = trim($this->params->get('sb_password',''));
		} else {
			$soapURL = 'https://api.ewaypayments.com/Soap.asmx?WSDL';
			$key = trim($this->params->get('key',''));
			$password = trim($this->params->get('password',''));
		}
		$service->APIConfig['Payment.Username'] = $key;
		$service->APIConfig['Payment.Password'] = $password;
		$service->APIConfig['PaymentService.Soap'] = $soapURL;
		$service->APIConfig['Request:Method'] = 'SOAP';
		$this->ewayService = $service;
		//Set the language in the class
		$config = JFactory::getConfig();
		
		//Define Payment Status codes in ewayrapid3  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'success'  => 'C','pending'  => 'P',
 	 'failure'=>'E'
  
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
		$plgname=$this->params->get( 'plugin_name' );
		$obj->name 	=!empty($plgname)?$plgname:$this->_name;
		$obj->id	= $this->_name;
		return $obj;
	}

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Payu
	/**
	 * RETURN PAY HTML FORM
	 * */
	function onTP_GetHTML($vars)
	{
			$vars=$this->preFormatingData($vars);	 // fomating on data
			$plgPaymentEwayrapid3Helper= new plgPaymentEwayrapid3Helper();
			// Split the name in first and last name
			$user= JFactory::getUser();
			
			$nameParts =$user->name; // explode(' ', $user->name, 2);
			$firstName = $user->name;
			$lastName = $user->name;
			
			// Get the base URL without the path
			$rootURL = rtrim(JURI::base(),'/');
			$subpathURL = JURI::base(true);
			if(!empty($subpathURL) && ($subpathURL != '/')) {
				$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
			}
			
			// Customer
		$request = new CreateAccessCodeRequest();
		$request->Customer->Reference = $vars->order_id;
	//	$request->Customer->TokenCustomerID="91665902";
		$request->Customer->Title = 'Mr.';
		$request->Customer->FirstName = $firstName;
		$request->Customer->LastName = $lastName;
		$country = !empty($vars->country) ? strtolower(trim($vars->country)) :'au';
		$request->Customer->Country = $country;
		$request->Customer->Email = trim($vars->user_email);
		
		// shipping details
		$request->ShippingAddress->Country=!empty($vars->country) ? strtolower(trim($vars->country)) :'au';
		// Item/product
		$item = new LineItem();   
		$item->SKU = $vars->order_id;
		$item->Description = !empty($vars->item_name) ? strtolower(trim($vars->item_name)) :'';
		$request->Items->LineItem[0] = $item;

		// Payment
		$request->Payment->TotalAmount = (int)($vars->amount * 100);
		$request->Payment->InvoiceNumber = $vars->order_id;
		$request->Payment->InvoiceDescription = "Orderid -" . ' #' . $vars->order_id;
		$request->Payment->InvoiceReference = $vars->order_id;
		$request->Payment->CurrencyCode = strtoupper($vars->currency_code);
		// Url to the page for getting the result with an AccessCode
		$vars->return=trim($vars->return);
		$request->RedirectUrl = (string)$vars->notify_url;
		
		//Populate values for Options
    $opt1 = new Option();
    $opt1->Value = $vars->user_email;    
    $request->Options->Option[0]= $opt1;
		
		// Method for this request. e.g. ProcessPayment, Create TokenCustomer, Update TokenCustomer & TokenPayment
		$request->Method = 'ProcessPayment';
		
		try {
			// Call RapidAPI
			$result = $this->ewayService->CreateAccessCode($request);
		} catch(Exception $e) {
			JError::raiseError(500, 'You have an error in your eWay Rapid 3.0 setup: ' . $e->getMessage());
			return false;
		}
		if(isset($result->Errors)) {
			$errorMsg = '';
			foreach(explode(',', $result->Errors) as $e) {
				$errorMsg .= $this->ewayService->APIConfig[$e] . ', ';
			}
			$errorMsg = substr($errorMsg, 0, -2);
			JError::raiseError(500, 'You have an error in your eWay Rapid 3.0  setup: ' . $errorMsg);
			return false;
		}

		$vars->AccessCode=$result->AccessCode;
		$vars->FormActionURL=$result->FormActionURL;
		$html = $this->buildLayout($vars);
	/*	@ob_start();
		include dirname(__FILE__).'/ewayrapid3/form.php';
		$html = @ob_get_clean();*/
		return $html;

	}
	function onTP_Processpayment($data,$vars=array()) 
	{
		JLoader::import('joomla.utilities.date');;
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		
		if($isValid) {
			// Build request for getting the result with the access code
			$request = new GetAccessCodeResultRequest();
			$request->AccessCode = $data['AccessCode'];
			// Call RapidAPI to get the result
			$result = $this->ewayService->GetAccessCodeResult($request);
			
			// CHECK FOR ERROR
			if(isset($result->Errors)) {
				$errorMsg = '';
				$ERROR=explode(',', $result->Errors);
				$error['code']=json_encode($ERROR); 
				foreach($ERROR as $e) {
					$errorMsg .= $this->ewayService->APIConfig[$e] . ', ';
				}
				$errorMsg = substr($errorMsg, 0, -2);
				$isValid = false;
				$error['desc']	= $errorMsg;
			}
		}
			// CHECK RESPONSE MASSAGE
		if($isValid) {
			$errorMsg = '';
			$RESMSG=explode(',', $result->ResponseMessage);
			foreach($RESMSG as $m) {
				if($m != 'A2000'){
					// NOT APPROVED
					 $isValid = false;
				}
				$errorMsg .= $this->ewayService->APIConfig[$m] . ', ';
			}
			if(!$isValid) {
				// NOT APPROVED
				$errorMsg = substr($errorMsg, 0, -2);
				$error['code']	= json_encode($RESMSG);
				$error['desc']	= $errorMsg;
			}
		}
		//3.compare response order id and send order id in notify URL 
		$res_orderid='';
		$res_orderid = $result->InvoiceReference;
		if($isValid ) {
		 $res_orderid=$result->InvoiceReference;
		if(!empty($vars) && $res_orderid != $vars->order_id )
			{
				$trxnstatus = 'ERROR';
				$isValid = false;
				$error['desc'] = "ORDER_MISMATCH " . " Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$res_orderid;
			}
		}
		
		// amount check
		// response amount in cent
		$gross_amt=(float)(($result->TotalAmount) / (100));
		if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount;
				$retrunamount =  (float)$gross_amt;
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
		$newStatus='';
		// Translaet Payment status
		if($trxnstatus == 'ERROR'){
			$newStatus= $this->translateResponse($trxnstatus);
		}else {
			$newStatus= $this->translateResponse($result->TransactionStatus);
		}
		
		$txn_id= !empty($result->ResponseMessage)?$result->ResponseMessage :'';
		//	print"<pre>"; print_r($result	); die;
		$OPTIONS=(array)$result->Options->Option;
		$buyer_email=$OPTIONS['Value'];
		
		
		// RETURN URL OR CANCEL URL IS NOT USED PREVIOUSLY
		
		$ret_result = array(
					'order_id'=>$res_orderid,
					'transaction_id'=>$txn_id,
					'buyer_email'=>$buyer_email,
					'status'=>$newStatus,
					'txn_type'=>'',
					'total_paid_amt'=>(float)$gross_amt,
					'raw_data'=>$result,
					'error'=>$error ,
					);
		return $ret_result;
			
	}	// END OF FUN
	
	function translateResponse($payment_status){
			foreach($this->responseStatus as $key=>$value)
			{
				if($key==$payment_status)
					return $value;		
			}
	}
	function onTP_Storelog($data)
	{
			$log = plgPaymentEwayrapid3Helper::Storelog($this->_name,$data);
	
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
		return $vars;
	}
	private function fetch_data($string, $start_tag, $end_tag)
	{
		$position = stripos($string, $start_tag);  
		$str = substr($string, $position);  		
		$str_second = substr($str, strlen($start_tag)); 
		$second_positon = stripos($str_second, $end_tag);
		$str_third = substr($str_second, 0, $second_positon); 
		$fetch_data = trim($str_third);		
		return $fetch_data; 
	}
	
	public function selectMonth()
	{
		$options = array();
		$options[] = JHTML::_('select.option',0,'--');
		for($i = 1; $i <= 12; $i++) {
			$m = sprintf('%02u', $i);
			$options[] = JHTML::_('select.option',$m,$m);
		}
		
		return JHTML::_('select.genericlist', $options, 'EWAY_CARDEXPIRYMONTH', 'class="input-small"', 'value', 'text', '', 'EWAY_CARDEXPIRYMONTH');
	}
	
	public function selectYear()
	{
		$year = gmdate('Y');
		
		$options = array();
		$options[] = JHTML::_('select.option',0,'--');
		for($i = 0; $i <= 10; $i++) {
			$y = sprintf('%04u', $i+$year);
			$options[] = JHTML::_('select.option',$y,$y);
		}
		
		return JHTML::_('select.genericlist', $options, 'EWAY_CARDEXPIRYYEAR', 'class="input-small"', 'value', 'text', '', 'EWAY_CARDEXPIRYYEAR');
	}
	//function 
	
	
	
}
