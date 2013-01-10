<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
$lang=JFactory::getLanguage();
$lang->load('plg_payment_amazon', JPATH_ADMINISTRATOR);
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/amazon/amazon/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/amazon/helper.php');
class  plgPaymentAmazon extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config =& JFactory::getConfig();

/*
PS The payment transaction was successful.
PF The payment transaction failed and the money was not transferred. You
  can redirect your customer to the Amazon Payments Payment
 Authorization page to select a different payment method.
PI Payment has been initiated. It will take between five seconds and 48
  hours to complete, based on the availability of external payment networks
 and the riskiness of the transaction.
PR The reserve transaction was successful.
RS The refund transaction was successful.
RF The refund transaction failed.
PaymentSuccess Amazon collected a subscription payment.
PendingUserAction Amazon tried to collect a payment which failed due to a payment method
                 error. The subscriber has been advised to adjust the method. Amazon
                will retry the payment after 6 days.
PaymentRescheduled Amazon tried to collect a payment which failed due to an error not
                  involving a payment method. Amazon will retry the payment after 6 days.
PaymentCancelled Amazon has failed to collect a payment, and will not make any more
                attempts. Other subscription payments will be attempted on schedule.
SubscriptionCancelled The subscription was canceled successfully. Amazon will make no further
                     attempts to collect payment against the subscription.
SubscriptionCompleted The subscription was completed. All payments have been collected.
SubscriptionSuccessful The subscription was created successfully.

*/
		//Define Payment Status codes in Amazon  And Respective Alias in Framework
		$this->responseStatus= array(
		'PI'=>'P',
 	 'PS'  => 'C',
 	 'PF'=>'E','Denied'=>'D',
 	 'PR'=>'C',
 	 'RS'=>'RF',
 	 'Reversed'=>'RV'

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
		$obj->name 	=$this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Amazon
	function onTP_GetHTML($vars)
	{
		$html = $this->buildLayout($vars);
		return $html;
	}


	function onTP_Processpayment($data)
	{

		//$file="jtt4.log";

		$urlEndPoint=JURI::getInstance()->toString();
		//$data=json_decode('{"paymentReason":"1234578jticketing jomsocail","signatureMethod":"RSA-SHA1","transactionAmount":"USD 3.000000","transactionId":"17G2TGQO126924NNZ1RFBIMMBVUGSO88VLA","status":"PS","buyerEmail":"sagar_c@mailinator.com","referenceId":"JT_00016","recipientEmail":"sagar_c@tekdi.net","transactionDate":"1357307734","buyerName":"sagar","operation":"pay","recipientName":"sagar chinchavade","signatureVersion":"2","certificateUrl":"https:\/\/fps.sandbox.amazonaws.com\/certs\/090911\/PKICert.pem?requestId=5v3w76vo20x0th5zoyr220qvodpaiganjsd695svculh0","paymentMethod":"CC","signature":"oMSb1OZFJaH\/w9MBKp9qtyInUZY5S50hfk5bVHR72C9kE0ng+e7EWW8TmCwdxGbIizrhISiKN\/cy\n0x8+0lrXo6KJdFYhg9L35+RtvQHRA8P3pzB\/ypvUrKLkcTykxo+s2NFtC8G9WSc+UOx+LpAFmNDk\nIZQvTcJklMbTkvyIxIA="}',true);
		$verify = plgPaymentAmazonHelper::validateIPN($data,$urlEndPoint);
		if(!strtoupper($verify)=='SUCCESS')
		return false;



		$payment_status=$this->translateResponse($data['status']);

		$result = array(
						'order_id'=>$data['referenceId'],
						'transaction_id'=>$data['transactionId'],
						'buyer_email'=>$data['payer_email'],
						'status'=>$payment_status,
						'subscribe_id'=>$data['subscr_id'],
						'txn_type'=>$data['paymentMethod'],
						'total_paid_amt'=>$data['transactionAmount'],
						'raw_data'=>json_encode($data),
						'error'=>json_encode($error),
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
			$log = plgPaymentAmazonHelper::Storelog($this->_name,$data);

	}
}
?>
