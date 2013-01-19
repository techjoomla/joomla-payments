<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
$lang=JFactory::getLanguage();
$lang->load('plg_payment_ogone', JPATH_ADMINISTRATOR);
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/ogone/ogone/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/ogone/helper.php');
class  plgPaymentOgone extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config =& JFactory::getConfig();

/*
5 Authorised 
9 Payment
requested
0 Invalid or 
incomplete 
2 Authorization 
refused 
51 Authorisation
waiting
91 Payment 
processing 
92 Payment
uncertain
93 Payment
refused


*/
		//Define Payment Status codes in Ogone  And Respective Alias in Framework
		$this->responseStatus= array(
		'5'=>'PA',
 	 '9'  => 'C',
 	 '0'=>'E',
 	 '91'=>'PU',
 	 '92'=>'RF',
 	 '93'=>'D'

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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Ogone
	function onTP_GetHTML($vars)
	{
		$html = $this->buildLayout($vars);
		return $html;
	}


	function onTP_Processpayment($data)
	{

		//$file="jtt4.log";

		//$urlEndPoint=JURI::getInstance()->toString();
		//$str='{"orderID":"JT_FY0II_00000107","currency":"EUR","amount":"0.01","PM":"CreditCard","ACCEPTANCE":"test123","STATUS":"9","CARDNO":"XXXXXXXXXXXX1111","ED":"0214","CN":"name of your client","TRXDATE":"01\/11\/13","PAYID":"18573392","NCERROR":"0","BRAND":"VISA","IP":"202.88.154.166","SHASIGN":"E05652152C1F273B281A4F98471CA0D89EC0935D"}';
		//$_POST=$data=json_decode($str,true);		
		$verify = plgPaymentOgoneHelper::validateIPN($data);
		if(!$verify)
		return false;

		$payment_status=$this->translateResponse($data['STATUS']);
		$result = array(
			'order_id'=>$data['orderID'],
			'transaction_id'=>$data['PAYID'],
			'buyer_email'=>$data['payer_email'],
			'status'=>$payment_status,
			'subscribe_id'=>$data['subscr_id'],
			'txn_type'=>$data['paymentMethod'],
			'total_paid_amt'=>$data['amount'],
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
			$log = plgPaymentOgoneHelper::Storelog($this->_name,$data);

	}
}
?>
