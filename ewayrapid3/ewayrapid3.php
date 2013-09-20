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
		$core_file 	= dirname(__FILE__).DS.$this->_name.DS.'tmpl'.DS.'default.php';
		$override		= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$this->_type.DS.$this->_name.DS.$layout.'.php';
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
		$request->RedirectUrl = (string)$vars->return;
		
		// Method for this request. e.g. ProcessPayment, Create TokenCustomer, Update TokenCustomer & TokenPayment
		$request->Method = 'ProcessPayment';
		
		
		try {
			// Call RapidAPI
			$result = $this->ewayService->CreateAccessCode($request);
		} catch(Exception $e) {
			JError::raiseError(500, 'You have an error in your eWay setup: ' . $e->getMessage());
			return false;
		}
		if(isset($result->Errors)) {
			$errorMsg = '';
			foreach(explode(',', $result->Errors) as $e) {
				$errorMsg .= $this->ewayService->APIConfig[$e] . ', ';
			}
			$errorMsg = substr($errorMsg, 0, -2);
			JError::raiseError(500, 'You have an error in your eWay setup: ' . $errorMsg);
			return false;
		}

		@ob_start();
		include dirname(__FILE__).'/ewayrapid3/form.php';
		$html = @ob_get_clean();

		return $html;
	}

	
	
	function onTP_Processpayment($data) 
	{
		JLoader::import('joomla.utilities.date');
		// Check if we're supposed to handle this
		JLoader::import('joomla.environment.uri');
		
		switch($this->params->get('site', 0))
		{
			case '0':
			default:
				$apiURL = 'https://au.ewaygateway.com/Result';
				break;
			case '1':
				$apiURL = 'https://payment.ewaygateway.com/Result';
				break;
			case '2':
				$apiURL = 'https://nz.ewaygateway.com/Result';
				break;
		}
		
		$eWayrapid3URL = new JURI($apiURL);
		$eWayrapid3URL->setVar('CustomerID', urlencode($this->params->get('customerid','')));
		$eWayrapid3URL->setVar('UserName', urlencode($this->params->get('username','')));
		$eWayrapid3URL->setVar('AccessPaymentCode', urlencode($data['AccessPaymentCode']));
		
		$posturl=$eWayrapid3URL->toString();
		$posturl = str_replace('Result?', 'Result/?', $posturl);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True')
		{
			$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		}
		$response = curl_exec($ch);

		$authecode = $this->fetch_data($response, '<authCode>', '</authCode>');
		$responsecode = $this->fetch_data($response, '<responsecode>', '</responsecode>');
		$retrunamount = $this->fetch_data($response, '<returnamount>', '</returnamount>');
		$trxnnumber = $this->fetch_data($response, '<trxnnumber>', '</trxnnumber>');
		$trxnstatus = $this->fetch_data($response, '<trxnstatus>', '</trxnstatus>');
		$trxnresponsemessage = $this->fetch_data($response, '<trxnresponsemessage>', '</trxnresponsemessage>');
		// order id
		$MerchantOption1_orderid=$this->fetch_data($response, '<MerchantOption1>', '</MerchantOption1>');
		$MerchantOption2_email=$this->fetch_data($response, '<MerchantOption2>', '</MerchantOption2>');
		$merchantreference = $this->fetch_data($response, '<merchantreference>', '</merchantreference>');
		
		$isValid = true;
		// Check that the amount is correct // checked in model payment 
		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}
		$order_status='';
		switch($trxnstatus) {
				case 'True':	
				$order_status = 'C';
				break;
		}
		
		// IF REQUIRE:: add the AfterPaymentCallback events
	
		$data['status']=$trxnstatus;

		//Error Handling
		$responseCodes=$this->responseCodes;
		$error=array();
		if($responsecode!= '00')
		{
		$error['code']	=$responsecode;
		$error['desc']	=(isset($responsecode)?$responseCodes[$responsecode]:'');
		}

		$result = array(
						'order_id'=>$MerchantOption1_orderid,
						'transaction_id'=>$authecode,
						'buyer_email'=>$MerchantOption2_email,
						'status'=>$order_status,
						'txn_type'=>'',
						'total_paid_amt'=>(float)$retrunamount,
						'raw_data'=>$response,
						'error'=>$error ,
						);
		//return true;
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
