<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/adaptive_paypal/adaptive_paypal/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/adaptive_paypal/helper.php');
$lang =  JFactory::getLanguage();
$lang->load('plg_payment_adaptive_paypal', JPATH_ADMINISTRATOR);

class  plgPaymentAdaptive_Paypal extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);


		//Set the language in the class
		$config = JFactory::getConfig();

		//Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus= array(
		 'COMPLETED'  => 'C',
		 'INCOMPLETE'  => 'P','PROCESSING'=>'P','PENDING'=>'P','CREATED'=>'P',
		 'ERROR'=>'E','DENIED'=>'D','FAILED'=>'E',
		 'PARTIALLY_REFUNDED'=>'RF','REVERSALERROR'=>'CRV','REFUNDED'=>'RF',
		 'REVERSED'=>'RV'
		);

		$this->headers=array(
			"X-PAYPAL-SECURITY-USERID:".$this->params->get('apiuser'),
			"X-PAYPAL-SECURITY-PASSWORD:".$this->params->get('apipass'),
			"X-PAYPAL-SECURITY-SIGNATURE:".$this->params->get('apisign'),
			"X-PAYPAL-REQUEST-DATA-FORMAT:JSON",
			"X-PAYPAL-RESPONSE-DATA-FORMAT:JSON",
			"X-PAYPAL-APPLICATION-ID:".$this->params->get('apiid')
		);

		$this->envelope=array(
			"errorLanguage"=>"en_US",
			"detailLevel"=>"ReturnAll"
		);
		$plugin = JPluginHelper::getPlugin('payment', 'adaptive_paypal');
		$params=json_decode($plugin->params);
		$this->apiurl= $params->sandbox ? 'https://svcs.sandbox.paypal.com/AdaptivePayments/' : 'https://svcs.paypal.com/AdaptivePayments/';
		$this->paypalurl= $params->sandbox ? 'https://www.sandbox.paypal.com/websrc?cmd=_ap-payment&paykey=' : 'https://www.paypal.com/websrc?cmd=_ap-payment&paykey=';
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
		$app = JFactory::getApplication();
		if($layout=='recurring')
			$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'recurring.php';
		else
			$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'default.php';

			$override		= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . 'recurring.php';
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
		$plgPaymentAdaptivePaypalHelper=new plgPaymentAdaptivePaypalHelper();
		$vars->action_url = $plgPaymentAdaptivePaypalHelper->buildPaypalUrl();
		//Take this receiver email address from plugin if component not provided it
		if(empty($vars->business))
			$vars->business=$this->params->get('business');

		//if component does not provide cmd
		if(empty($vars->cmd))
			$vars->cmd='_xclick';
			$html = $this->buildLayout($vars);
		return $html;
	}

	function onTP_ProcessSubmit($data,$vars)
	{
		$adaptiveReceiverList = $vars->adaptiveReceiverList;
		//Take this receiver email address from plugin if component not provided it
		$plgPaymentAdaptivePaypalHelper=new plgPaymentAdaptivePaypalHelper();
		$receiver = array();
		$data = $this->getFormattedReceiver($vars->adaptiveReceiverList);
		$receiver = $data['receiver'];
		$receiverOptions = $data['receiverOptions'];
		//create the pay request
		$createPacket=array(
			"actionType"=>"PAY",
			"currencyCode"=>$vars->currency_code,
			"receiverList"=>array(
				"receiver"=>$receiverOptions
			),
			"returnUrl"=>$vars->return,
			"cancelUrl"=>$vars->cancel_return,
			"ipnNotificationUrl"=>$vars->notify_url,//ipnNotificationUrl notifyUrl
			"trackingId"=>$vars->order_id,
			"requestEnvelope"=>$this->envelope
		);

		$response=$this->_paypalSend($createPacket,"Pay");
		$paykey=$response['payKey'];
		//Set payment detials
		$detailsPacket=array(
			"requestEnvelope"=>$this->envelope,
			"payKey"=>$response['payKey'],
			"receiverOptions"=>$receiver
		);

		$response=$this->_paypalSend($detailsPacket,"SetPaymentOptions");
		$detls=$this->getPaymentOptions($paykey);

		$file = 'testing2.txt';
		// The new person to add to the file
		$person = json_encode($_REQUEST);
		//header to paypal
		header("Location:".$this->paypalurl.$paykey);
	}
	/**Make plugin specific receiver format
	 * */
	function getFormattedReceiver($receiverList) {
		$receiver = array();
		$receiverOptions = array();

		foreach($receiverList as $rec) {
			$temp['amount'] = $rec['amount'];
			$temp['email'] = $rec['receiver'];
			$temp['primary'] = $rec['primary'];
			$receiverOptions[] = $temp;
			$emails['email'] = $temp['email'] ;
			$r = array();
			$r['receiver'] = $emails;
			$receiver[] = $r;
		}

		$data['receiverOptions'] = $receiverOptions;
		$data['receiver'] = $receiver;

		return $data;
	}
	function onTP_Processpayment($data)
	{
		/*$verify = plgPaymentAdaptivePaypalHelper::validateIPN($data);
		if (!$verify) { return false; }
		*/
		$payment_status=$this->translateResponse($data['status']);
		$paymentDetails=$this->getTransactionDetails($data);

		$result = array(
						'order_id'=>$data['tracking_id'],
						'transaction_id'=>$data['pay_key'],
						'action_type'=>$data['action_type'],
						'status'=>$payment_status,
						'txn_type'=>$data['transaction_type'],
						'total_paid_amt'=>$paymentDetails['paymentInfoList']['paymentInfo'][1]['receiver']['amount'],
						'raw_data'=>$paymentDetails,
						'error'=>$paymentDetails,
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
		$log = plgPaymentAdaptivePaypalHelper::Storelog($this->_name,$data);
	}

	function _paypalSend($data,$call){
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$this->apiurl.$call);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this->headers);
		return json_decode(curl_exec($ch),TRUE);
	}
	//Wrapper for getting payment details
	function getPaymentOptions($paykey){
		$packet=array(
			"requestEnvelope"=>$this->envelope,
			"payKey"=>$paykey
			);
		return $this->_paypalSend($packet,"GetPaymentOptions");
	}
	//get the complete transaction details
	function getTransactionDetails($data)
	{
		$detailsPacket=array(
			"payKey"=>$data['pay_key'],
			"requestEnvelope"=>$this->envelope
		);

		$res=$this->_paypalSend($detailsPacket,'PaymentDetails');
		return $res;
	}
}
