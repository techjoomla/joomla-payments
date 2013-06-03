<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.plugin.plugin' );

if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/authorizenet/authorizenet/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/authorizenet/helper.php');
$lang =  JFactory::getLanguage();
$lang->load('plg_payment_authorizenet', JPATH_ADMINISTRATOR);

class plgpaymentAuthorizenet extends JPlugin 
{
	var $_payment_gateway = 'payment_authorizenet';
	var $_log = null;
	
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		
		//Define Payment Status codes in Authorise  And Respective Alias in Framework
		//1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
		$this->responseStatus= array(
			'1' =>'C',
			'2' =>'D',
			'3' =>'E',
			'4'=>'UR',  
		);
		

		
 		$this->login_id = $this->params->get( 'login_id', '1' );
		 $this->tran_key = $this->params->get( 'tran_key', '1' );
		
	}
	
	/* Internal use functions */
	function buildLayoutPath($layout="default") {
		if(empty($layout))
		$layout="default";
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__).DS.$this->_name.DS.'tmpl'.DS.$layout.'.php';
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
	//gets param values
    function getParamResult($name, $default = '') 
    {
    	$sandbox_param = "sandbox_$name";
    	$sb_value = $this->params->get($sandbox_param);
    	
        if ($this->params->get('sandbox') && !empty($sb_value)) {
            $param = $this->params->get($sandbox_param, $default);
        }
        else {
        	$param = $this->params->get($name, $default);
        }
        
        return $param;
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
	
		if(!empty($vars->payment_type) and $vars->payment_type!='')
		$payment_type=$vars->payment_type;
		else
		$payment_type='';
		$html = $this->buildLayout($vars,$payment_type);
		return $html;
	}
	
	function onTP_Processpayment($data) 
	{
		if($data['payment_type']=="recurring")
		{
			$response=plgpaymentAuthorizenet::onTP_Processpayment_recurring($data);
			return $response;
		}
		$error=array();		
		$action_url = plgPaymentAuthorizenetHelper::buildAuthorizenetUrl();	
		
		$authnet_values				= array(
									"x_login"					=> $this->login_id,
								 	"x_tran_key"			=> $this->tran_key,
									"x_version"				=> "3.1",
									"x_delim_char"		=> "|",
									"x_delim_data"		=> "TRUE",
									"x_type"					=> "AUTH_CAPTURE",
									"x_method"				=> "CC",
								 	"x_relay_response"=> "FALSE",
									"x_card_num"			=> $data['cardnum'],
									"x_card_code"			=> $data['cardcvv'],
									"x_exp_date"			=> $data['cardexp'],
									"x_description"		=> "",
									"x_amount"				=> $data['amount'],
									"x_first_name"		=> $data['cardfname'],
									"x_last_name"			=> $data['cardlname'],
									"x_address"				=> $data['cardaddress1'],
									"x_city"					=> $data['cardcity'],
									"x_state"					=> $data['cardstate'],
									"x_zip"						=> $data['cardzip'],
									"x_country"				=>$data['cardcountry'],
									"x_cust_id"				=>$data['user_id'],
									"x_email"					=>$data['email'],
									"x_order_id"			=>$data['order_id'],
		
									);

		$fields = "";
		foreach($authnet_values as $key => $value) 
			$fields .= "$key=".urlencode($value). "&";	
			
		//call to curl	
		$ch = curl_init($action_url); 		
		curl_setopt($ch, CURLOPT_HEADER, 0); 		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); 		
		$resp = curl_exec($ch); //execute post and get results
		curl_close ($ch);
    $allresp = explode('|',$resp);
		//call to curl	
		
    $payment_status=$this->translateResponse($allresp[0]);		
 	  $error['code']=$allresp[0];
	  $error['desc']=$allresp[3];
		
		 
    $transaction_id = $allresp[6];     

    $result = array('transaction_id'=>$transaction_id,
    				'order_id'=>$data['order_id'],
						'status'=>$payment_status,
						'total_paid_amt'=>$allresp[9],
						'raw_data'=>$resp,
						'error'=>$error,
						'return'=>$data['return'],
						);
    return $result;
    
	}
	
function 	onTP_Processpayment_recurring($data)
	{	
		$order_id=$data['order_id'];
		

		if(JVERSION >=1.6)
		require_once(JPATH_SITE.DS.'plugins'.DS.'payment'.DS.'authorizenet'.DS.'authorizenet'.DS.'lib'.DS.'AuthorizeNet.php');
		else
		require_once(JPATH_SITE.DS.'plugins'.DS.'payment'.DS.'authorizenet'.DS.'lib'.DS.'AuthorizeNet.php');


		
		 $auth_net_login_id = $this->params->get( 'login_id', '1' );
		$auth_net_tran_key = $this->params->get( 'tran_key', '1' );

		
		$auth_net_url = plgPaymentAuthorizenetHelper::buildAuthorizenetUrl();	
		$auth_net_url;
		$DEBUGGING					= 1;				# Display additional information to track down problems
		$TESTING					= 1;				# Set the testing flag so that transactions are not live
		$ERROR_RETRIES				= 2;				# Number of transactions to post if soft errors occur
		
		$exp_date = explode('-',$data['expirationDate']);
		$data['expirationDate'] = $exp_date[1]."-".$exp_date[0];		
		
		define("AUTHORIZENET_API_LOGIN_ID", $auth_net_login_id);
		define("AUTHORIZENET_TRANSACTION_KEY", $auth_net_tran_key);
		
		$subscription = new AuthorizeNet_Subscription;

		$subscription->name = $data['sub_name'];
		$subscription->intervalLength = $data['intervalLength'];
		$subscription->intervalUnit = $data['intervalUnit'];
		$subscription->startDate = $data['startDate'];
		$subscription->totalOccurrences =  $data['totalOccurrences'];
		$subscription->amount = $data['amount'];
		$subscription->orderInvoiceNumber =$data['order_id'];;
		$subscription->creditCardCardNumber = $data['cardNumber'];
		$subscription->creditCardExpirationDate= $data['expirationDate'];
		$subscription->creditCardCardCode = $data['cardcode'];
		$subscription->billToFirstName = $data['firstName'];
		$subscription->billToLastName = $data['lastName'];
		$subscription->billToAddress = $data['cardaddress1'];
		$subscription->billToCity = $data['cardcity'];
		$subscription->billToState = $data['cardstate'];
		$subscription->billToZip = $data['cardzip'];
		
		// Create the subscription. 
		$request = new AuthorizeNetARB;
		$refId=$subscription->orderInvoiceNumber;
		$request->setRefId($refId);
		$testmode = $this->params->get( 'sandbox', '1' );
		
		if($testmode == 0){		
			$request->setSandbox(false);	//turn OFF sandbox
		}
		else{
			$request->setSandbox(true);	//turn ON sandbox
		}
		$response = $request->createSubscription($subscription);


		$subscription_id = $response->getSubscriptionId();
		
	
		$error="";
		if ($response->xml->messages->resultCode != 'Ok')
		{
			$payment_status="P";
			
			$error=JText::_('AUTH_SUB_FAIL').$response->xml->messages->message->text;
		

		}
		else
		{
		$payment_status="C";
			$success=JText::_('AUTH_SUB_SUCCESS').$subscription_id;
		
		}
			$result = array('transaction_id'=>$refId,
						'subscription_id'=>$subscription_id,							
    				'order_id'=>$data['order_id'],
						'status'=>$payment_status,
						'total_paid_amt'=>$data['amount'],
						'raw_data'=>$response,
						'payment_type'=>'recurring',
						'error'=>$error,
						'success'=>$success,
						'return'=>$data['return'],
						);
						return $result;

	}
		//Function to take silent post data
	function 	confirm_recurring_payment_Update($json)
	{
		$db = JFactory::getDBO();
		$data=json_decode($json,true);
		$payment_status=plgpaymentAuthorizenet::translateResponse($data['x_response_code']);
			
					$result = array('transaction_id'=>$data['x_trans_id'],			
						'subscription_id'=>$data['x_subscription_id'],							
    				'order_id'=>$data['x_trans_id'],			
						'status'=>$payment_status,
						'total_paid_amt'=>$data['x_amount'],
						'raw_data'=>$json,
						'pg_plugin'=>'authorizenet',
						'payment_type'=>'recurring',
						'payment_number'=>$data['x_subscription_paynum'],
						'success'=>1,
						'return'=>'',
						);
						return $result;
		
		
		
		

	

	
	}
	//this is for cancel automated Recurring Billing
	function cancelsubscription($data)
	{
		$subid = $data['0'];	
		$id=$data['1'];	
		$gateway = $data['2'];
		$ad_id = $data['3'];
		$db = JFactory::getDBO();
		if($subid)
		{
			if(JVERSION >=1.6)
			require_once(JPATH_SITE.DS.'plugins'.DS.'payment'.DS.'authorizenet'.DS.'authorizenet'.DS.'lib'.DS.'AuthorizeNet.php');
			else
			require_once(JPATH_SITE.DS.'plugins'.DS.'payment'.DS.'authorizenet'.DS.'lib'.DS.'AuthorizeNet.php');
			$auth_net_login_id = $this->params->get( 'login_id', '1' );
			$auth_net_tran_key = $this->params->get( 'tran_key', '1' );

			$auth_net_url = plgPaymentAuthorizenetHelper::buildAuthorizenetUrl();	
			define("AUTHORIZENET_API_LOGIN_ID", $auth_net_login_id);
			define("AUTHORIZENET_TRANSACTION_KEY", $auth_net_tran_key);

			$refId=$id;
			// Cancel the subscription. 
			$cancellation = new AuthorizeNetARB;
			$cancellation->setRefId($refId);
			$response = $cancellation->cancelSubscription($subid);

			if ($response->xml->messages->resultCode != 'Ok')
			{
				$payment_status="P";
				$error=JText::_('AUTH_SUB_CANCEL_FAIL').$response->xml->messages->message->text;

			}
			else
			{
				$payment_status="C";
				$success=JText::_('AUTH_SUB_CANCEL_SUCCESS');
				$paymentdata=new stdClass;

			}
				$result = array('transaction_id'=>$refId,
						'subscription_id'=>$subid,							
    				'order_id'=>$data['order_id'],
						'status'=>$payment_status,
						'total_paid_amt'=>$data['amount'],
						'raw_data'=>$response,
						'payment_type'=>'recurring',
						'error'=>$error,
						'success'=>$success,
						'return'=>$data['return'],
						);
						return $result;
		
				
		}
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
			$log = plgPaymentAuthorizenetHelper::Storelog($this->_name,$data);
	
	}
	
}

