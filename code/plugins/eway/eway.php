<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(version_compare(JVERSION, '1.6.0', 'ge')) 
	require_once(JPATH_SITE.'/plugins/payment/eway/eway/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/eway/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_eway', JPATH_ADMINISTRATOR);
class  plgPaymentEway extends JPlugin
{
	
	private $responseCodes = array(
		'CX'	=> 'Customer Cancelled Transaction',
		'00'	=> 'Transaction Approved',
		'02'	=> 'Refer to Issuer',
		'03'	=> 'No Merchant',
		'04'	=> 'Pick Up Card',
		'05'	=> 'Do Not Honour',
		'06'	=> 'Error',
		'07'	=> 'Pick Up Card, Special',
		'08'	=> 'Honour With Identification',
		'09'	=> 'Request In Progress',
		'10'	=> 'Approved For Partial Amount',
		'11'	=> 'Approved, VIP',
		'12'	=> 'Invalid Transaction',
		'13'	=> 'Invalid Amount',
		'14'	=> 'Invalid Card Number',
		'15'	=> 'No Issuer',
		'16'	=> 'Approved, Update Track 3',
		'19'	=> 'Re-enter Last Transaction',
		'21'	=> 'No Action Taken',
		'22'	=> 'Suspected Malfunction',
		'23'	=> 'Unacceptable Transaction Fee',
		'25'	=> 'Unable to Locate Record On File',
		'30'	=> 'Format Error',
		'31'	=> 'Bank Not Supported By Switch',
		'33'	=> 'Expired Card, Capture',
		'34'	=> 'Suspected Fraud, Retain Card',
		'35'	=> 'Card Acceptor, Contact Acquirer, Retain Card',
		'36'	=> 'Restricted Card, Retain Card',
		'37'	=> 'Contact Acquirer Security Department, Retain Card',
		'38'	=> 'PIN Tries Exceeded, Capture',
		'39'	=> 'No Credit Account',
		'40'	=> 'Function Not Supported',
		'41'	=> 'Lost Card',
		'42'	=> 'No Universal Account',
		'43'	=> 'Stolen Card',
		'44'	=> 'No Investment Account',
		'51'	=> 'Insufficient Funds',
		'52'	=> 'No Cheque Account',
		'53'	=> 'No Savings Account',
		'54'	=> 'Expired Card',
		'55'	=> 'Incorrect PIN',
		'56'	=> 'No Card Record',
		'57'	=> 'Function Not Permitted To Cardholder',
		'58'	=> 'Function Not Permitted To Terminal',
		'59'	=> 'Suspected Fraud',
		'60'	=> 'Acceptor Contact Acquirer',
		'61'	=> 'Exceeds Withdrawal Limit',
		'62'	=> 'Restricted Card',
		'63'	=> 'Security Violation',
		'64'	=> 'Original Amount Incorrect',
		'66'	=> 'Acceptor Contact Acquirer, Security',
		'67'	=> 'Capture Card',
		'75'	=> 'PIN Tries Exceeded',
		'82'	=> 'CVV Validation Error',
		'90'	=> 'Cutoff in Progress',
		'91'	=> 'Card Issuer Unavailable',
		'92'	=> 'Unable to Route Transaction',
		'93'	=> 'Cannot Complete, Violation Of The Law',
		'94'	=> 'Duplicate Transaction',
		'96'	=> 'System Error'
	);
	

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$config = array_merge($config, array(
			'ppName'		=> 'eway',
			'ppKey'			=> 'PLG_PAYMENT_EWAY_TITLE',
			'ppImage'		=> '',
		));
		//Set the language in the class
		$config = JFactory::getConfig();

		
		//Define Payment Status codes in eway  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'True'  => 'C','ERROR'  => 'E');
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
			$plgPaymentEwayHelper= new plgPaymentEwayHelper();
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
			
			// Construct the transaction key request URL
		JLoader::import('joomla.environment.uri');
		$country='au'; 
		switch($this->params->get('site', 0))
		{
			case '0':
			default:
				$apiURL = 'https://au.ewaygateway.com/Request';
				$country='au'; 
				break;
			case '1':
				$apiURL = 'https://payment.ewaygateway.com/Request';
				$country='gb'; 
				break;
			case '2':
				$apiURL = 'https://nz.ewaygateway.com/Request';
				$country='nz'; 
				break;
		}
		$eWayURL = new JURI($apiURL);
		$eWayURL->setVar('CustomerID', urlencode($this->params->get('customerid','')));
		$eWayURL->setVar('UserName', urlencode($this->params->get('username','')));
		$eWayURL->setVar('Amount', urlencode(sprintf('%0.2f',$vars->amount)));
		$eWayURL->setVar('Currency', urlencode($vars->currency_code)); 
		//$eWayURL->setVar('Currency', urlencode("AUD")); //@TODO  you can only test in the currency for the sandbox, either AUD or GBP
		//$eWayURL->setVar('ReturnURL', urlencode($vars->return));
		$eWayURL->setVar('ReturnURL', urlencode($vars->notify_url));
		$eWayURL->setVar('CancelURL', urlencode($rootURL.str_replace('&amp;','&',$vars->cancel_return)));
		
		if($this->params->get('companylogo','')) 
			$eWayURL->setVar('CompanyLogo', urlencode($this->params->get('companylogo','')));
		if($this->params->get('pagebanner','')) 
			$eWayURL->setVar('Pagebanner', urlencode($this->params->get('pagebanner','')));
			
		$eWayURL->setVar('ModifiableCustomerDetails', 'True');
		if($this->params->get('language','')) 
			$eWayURL->setVar('Language', urlencode($this->params->get('language','')));
		if($this->params->get('companyname','')) 
			$eWayURL->setVar('CompanyName', urlencode($this->params->get('companyname','')));
		$eWayURL->setVar('CustomerFirstName', urlencode($vars->user_firstname));
		$eWayURL->setVar('CustomerLastName', urlencode($vars->user_firstname));

		$eWayURL->setVar('CustomerCountry', $country);//urlencode($kuser->country));
		$eWayURL->setVar('CustomerEmail', urlencode($vars->user_email));
		
		$title="Mr.";    //@TODO SET DEFAULT
		//$eWayURL->setVar('InvoiceDescription', urlencode($level->title . ' - [ ' . $vars->order_id . ' ]'));
		$eWayURL->setVar('InvoiceDescription', urlencode($title .' '.$firstName. ' - [ ' . $vars->order_id . ' ]'));
		//$eWayURL->setVar('MerchantReference', urlencode($vars->order_id));
		$eWayURL->setVar('MerchantOption1', urlencode($vars->order_id));
		$eWayURL->setVar('MerchantOption2', urlencode($vars->user_email));
		if($this->params->get('pagetitle',''))
			$eWayURL->setVar('PageTitle', urlencode($this->params->get('pagetitle','')));
		if($this->params->get('pagedescription','')) 
			$eWayURL->setVar('PageDescription', urlencode($this->params->get('pagedescription','')));
		if($this->params->get('pagefooter','')) 
			$eWayURL->setVar('PageFooter', urlencode($this->params->get('pagefooter','')));

		$postURL = $eWayURL->toString();
		$postURL = str_replace('Request?', 'Request/?', $postURL);
		
		// Send the transaction key request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $postURL);
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
		$responsemode = $this->fetch_data($response, '<result>', '</result>');
		// $responseurl = eway server give redirect url 
		// like https://au.ewaygateway.com/PaymentPage.aspx?value=FDutUBfBS3oVR7PTbg78zlS8tz8xrif4QGDIfvuICBi8L9TZHq
	 	$responseurl = $this->fetch_data($response, '<uri>', '</uri>'); 
		if($responsemode=="True") {
			//JFactory::getApplication()->redirect($responseurl);
			$vars->responseurl=$responseurl;
			return	$html = $this->buildLayout($vars);
		} else {
			return JError::raiseError(500, '************* You have an error in your eWay setup: <br> '.$response);
		}
		//return $html;
	}
	
	function onTP_Processpayment($data,$vars=array()) 
	{
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
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
		
		$eWayURL = new JURI($apiURL);
		$eWayURL->setVar('CustomerID', urlencode($this->params->get('customerid','')));
		$eWayURL->setVar('UserName', urlencode($this->params->get('username','')));
		$eWayURL->setVar('AccessPaymentCode', urlencode($data['AccessPaymentCode']));
		
		$posturl=$eWayURL->toString();
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
		
		//3.compare response order id and send order id in notify URL 
		if($isValid ) {
			if(!empty($vars) && $MerchantOption1_orderid != $vars->order_id )
			{
				$isValid = false;
				$trxnstatus = 'ERROR';
				$data['error'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$MerchantOption1_orderid;
			}
		}

		// amount check
		if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount;
				$return_resp['status'] ='0';
				$retrunamount =  (float)$retrunamount;
				$epsilon = 0.01;
				
				if(($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus = 'ERROR';  // change response status to ERROR FOR AMOUNT ONLY
					$isValid = false;
					$data['error'] = "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
				}
			}
		}
		$order_status='';
		// Translaet Payment status
		$order_status= $this->translateResponse($trxnstatus);
		
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
			$log = plgPaymentEwayHelper::Storelog($this->_name,$data);
	
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
	//function 
	
	
	
}
