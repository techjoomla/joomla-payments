<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(version_compare(JVERSION, '1.6.0', 'ge')) 
	require_once(JPATH_SITE.'/plugins/payment/epaydk/epaydk/helper.php');
else

	require_once(JPATH_SITE.'/plugins/payment/epaydk/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_epaydk', JPATH_ADMINISTRATOR);
class  plgPaymentEpaydk extends JPlugin
{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$config = array_merge($config, array(
			'ppName'		=> 'epaydk',
			'ppKey'			=> 'PLG_PAYMENT_EPAYDK_TITLE',
			'ppImage'		=> '',
		));
		
		//Define Payment Status codes in eway  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'C'  => 'C','ERROR'  => 'E');
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'default.php';
		$override		= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
		if(JFile::exists($override))		{
			return $override;
		}
		else{
	  	return  $core_file;
		}
	}
	
	//Builds the layout to be shown, along with hidden fields.
	function buildLayout($data, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
        $layout = $this->buildLayoutPath($layout);
        include($layout);
        $html = ob_get_contents(); 
        ob_end_clean();
		return $html;
	}

	// Used to Build List of Payment Gatepaydk in the respective Components
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

	//Constructs the Payment form in case of On Site Payment gatepaydks like Auth.net & constructs the Submit button in case of offsite ones like Payu
	/**
	 * RETURN PAY HTML FORM
	 * */
	function onTP_GetHTML($vars)
	{
		$vars=$this->preFormatingData($vars);
		$plgPaymentEpaydkHelper= new plgPaymentEpaydkHelper();
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
		
		// Separate URL variable as it cannot be a part of the md5 checksum
		$url = $this->getPaymentURL();
		$data = array(
			'merchant'			=> $this->getMerchantID() ,
			'success'			=> $vars->return ,
			'cancel'			=> $vars->cancel_return ,
			'postback'			=> $vars->notify_url ,
			'orderid'			=> $vars->order_id ,
			'currency'			=> strtoupper($vars->currency_code),
			'amount'			=> ($vars->amount * 100),		// Epay calculates in minor amounts, and doesn't support tax differentation
			'cardtypes'			=> implode(',', $this->params->get('cardtypes', array())),
			'instantcapture'	=> '1',
			'instantcallback'	=> '1',
			'language'			=> $this->params->get('language', '0'),
			'ordertext'			=> 'Order id'. ' - [ ' . $vars->order_id . ' ]',
			'windowstate'		=> '3',
			'ownreceipt'		=> '0',
			'md5'				=> $this->params->get('secret','')										// Will be overriden with md5sum checksum
		);
		
		if ($this->params->get('md5', 1)) {
			// Security hash - must be compiled from ALL inputs sent
			$data['md5'] = md5(implode('', $data));
		}
		else {
			$data['md5'] = '';
		}
		
		$data['actionURL']=$url;  // dont make md5
		$data['submiturl']=$vars->submiturl;
		// Set array as object for compatability
		$data = (object) $data;
		$html=$this->buildLayout($data);
		
		return $html;
	}
	function onTP_Processpayment($data,$vars) 
	{
		$resData=$data;
		JLoader::import('joomla.utilities.date');
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		
		// Check return values for md5 security hash for validity (i.e. protect against fraud attempt)
		$isValid = $this->isValidRequest($data);
		if (!$isValid) {
			$error['desc'] = 'Epay reports transaction as invalid';
		}
		
		// check for order id
		$response_orderid='';
		if ($isValid) {
			$id = array_key_exists('orderid', $data) ? (int) $data['orderid'] : -1;
			$subscription = null;
			if ($id > 0) {
				$response_orderid=$id;
			}
			else {
				$isValid = false;
			}
			
			if (!$isValid) {
				$error['desc'] = 'The referenced subscription ID ("orderid" field) is invalid';
			}
		}
		
		// Check that amount is correct
		$mc_gross='';
		if ($isValid ) {
			$mc_gross = floatval($data['amount'] / 100);	// Epay uses minor values
		}

		// Check that currency is correct
		// NOTE: Epay returns a code (int) that represents currency (though they accept a string in the form!)
		$mc_currency = '';
		if ($isValid ) {
			$epay_currency_codes = array('4'=>'AFA','8'=>'ALL','12'=>'DZD','20'=>'ADP','31'=>'AZM','32'=>'ARS','36'=>'AUD','44'=>'BSD','48'=>'BHD','50'=>'BDT','51'=>'AMD','52'=>'BBD','60'=>'BMD','64'=>'BTN','68'=>'BOB','72'=>'BWP','84'=>'BZD','90'=>'SBD','96'=>'BND','100'=>'BGL','104'=>'MMK','108'=>'BIF','116'=>'KHR','124'=>'CAD','132'=>'CVE','136'=>'KYD','144'=>'LKR','152'=>'CLP','156'=>'CNY','170'=>'COP','174'=>'KMF','188'=>'CRC','191'=>'HRK','192'=>'CUP','196'=>'CYP','203'=>'CZK','208'=>'DKK','214'=>'DOP','218'=>'ECS','222'=>'SVC','230'=>'ETB','232'=>'ERN','233'=>'EEK','238'=>'FKP','242'=>'FJD','262'=>'DJF','270'=>'GMD','288'=>'GHC','292'=>'GIP','320'=>'GTQ','324'=>'GNF','328'=>'GYD','332'=>'HTG','340'=>'HNL','344'=>'HKD','348'=>'HUF','352'=>'ISK','356'=>'INR','360'=>'IDR','364'=>'IRR','368'=>'IQD','376'=>'ILS','388'=>'JMD','392'=>'JPY','398'=>'KZT','400'=>'JOD','404'=>'KES','408'=>'KPW','410'=>'KRW','414'=>'KWD','417'=>'KGS','418'=>'LAK','422'=>'LBP','426'=>'LSL','428'=>'LVL','430'=>'LRD','434'=>'LYD','440'=>'LTL','446'=>'MOP','450'=>'MGF','454'=>'MWK','458'=>'MYR','462'=>'MVR','470'=>'MTL','478'=>'MRO','480'=>'MUR','484'=>'MXN','496'=>'MNT','498'=>'MDL','504'=>'MAD','508'=>'MZM','512'=>'OMR','516'=>'NAD','524'=>'NPR','532'=>'ANG','533'=>'AWG','548'=>'VUV','554'=>'NZD','558'=>'NIO','566'=>'NGN','578'=>'NOK','586'=>'PKR','590'=>'PAB','598'=>'PGK','600'=>'PYG','604'=>'PEN','608'=>'PHP','624'=>'GWP','626'=>'TPE','634'=>'QAR','642'=>'ROL','643'=>'RUB','646'=>'RWF','654'=>'SHP','678'=>'STD','682'=>'SAR','690'=>'SCR','694'=>'SLL','702'=>'SGD','703'=>'SKK','704'=>'VND','705'=>'SIT','706'=>'SOS','710'=>'ZAR','716'=>'ZWD','736'=>'SDD','740'=>'SRG','748'=>'SZL','752'=>'SEK','756'=>'CHF','760'=>'SYP','764'=>'THB','776'=>'TOP','780'=>'TTD','784'=>'AED','788'=>'TND','792'=>'TRL','795'=>'TMM','800'=>'UGX','807'=>'MKD','810'=>'RUR','818'=>'EGP','826'=>'GBP','834'=>'TZS','840'=>'USD','858'=>'UYU','860'=>'UZS','862'=>'VEB','886'=>'YER','891'=>'YUM','894'=>'ZMK','901'=>'TWD','949'=>'TRY','950'=>'XAF','951'=>'XCD','952'=>'XOF','953'=>'XPF','972'=>'TJS','973'=>'AOA','974'=>'BYR','975'=>'BGN','976'=>'CDF','977'=>'BAM','978'=>'EUR','979'=>'MXV','980'=>'UAH','981'=>'GEL','983'=>'ECV','984'=>'BOV','985'=>'PLN','986'=>'BRL','990'=>'CLF');
			
			$mc_currency =(int)$data['currency']; // remove 0 eg if return currency=036
			//$currency = strtoupper(AkeebasubsHelperCparams::getParam('currency','EUR'));
			$mc_currencyINT =(int)$data['currency']; // remove 0 eg if return currency=036
			if (array_key_exists($mc_currency, $epay_currency_codes) || array_key_exists($mc_currencyINT, $epay_currency_codes)) {
				$mc_currency = strtoupper($epay_currency_codes[$mc_currency]);
			}
			else {
				$isValid = false;
				$error['desc']  = "Invalid currency;";
			}
		}
		
		//3.compare response order id and send order id in notify URL 
		if($isValid ) {
		 $res_orderid=$response_orderid;
			if(!empty($vars) && $res_orderid != $vars->order_id )
			{
				$isValid = false;
				$trxnstatus = 'ERROR';
				$error['desc'] = "ORDER_MISMATCH" . "Invalid ORDERID; notify order_is ". $vars->order_id .", and response ".$res_orderid;
			}
		}
		// amount check
		if($isValid ) {
			if(!empty($vars))
			{
				// Check that the amount is correct
				$order_amount=(float) $vars->amount;
				$retrunamount =  (float)$mc_gross;
				$epsilon = 0.01;
				
				if(($order_amount - $retrunamount) > $epsilon)
				{
					$trxnstatus = 'ERROR';  
					$isValid = false;
					$error['desc'] = "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
				}
			}
		}

		// Check the payment_status  --NO ERROR THE IT IS CONFORM
		$order_status= !empty($error['desc']) ? '':'C';
			if($trxnstatus == 'ERROR'){
				$order_status= $this->translateResponse($trxnstatus);
			}else{
				$order_status= $this->translateResponse($order_status);
			}
		
		$txn_id= !empty($data['txnid'])?$data['txnid'] :'';
		$email= !empty($data['email'])?$data['email'] :'';
		$data['status']=$order_status;
		$result = array(
						'order_id'=>$response_orderid,
						'transaction_id'=>$txn_id,
						'buyer_email'=>$email,
						'status'=>$order_status,
						'txn_type'=>'',
						'total_paid_amt'=>(float)$mc_gross,
						'raw_data'=>$resData,
						'error'=>$error ,
						'responseCurrency'=>$mc_currency
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
			$log = plgPaymentEpaydkHelper::Storelog($this->_name,$data);
	
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
	/**
	 * Gets the form action URL for the payment
	 */
	private function getPaymentURL()
	{
		$sandbox = $this->params->get('sandbox', 0);
		if ($sandbox) {
			// return different url if Epay ever changes
			// IN FUTURE :: IF EPAY CHANGE THEN SANDBOX AND LIVE URL
		}
		return 'https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/Default.aspx';
	}
		
	
	/**
	 * Gets the Epay Merchant ID (usually digits only)
	 */
	private function getMerchantID()
	{
		$sandbox = $this->params->get('sandbox', 0);
		if ($sandbox) {
			return $this->params->get('sandbox_merchant', '');
		}
		
		return $this->params->get('merchant', '');
	}
	
	function getOnlyResponseData($data)
	{
		$remvoveData = array('option',"controller","model","view","layout","Itemid","task","processor","hash");
		
		foreach($remvoveData as $removekey)
		{
				if(!empty($data[$removekey]))
				{
					unset($data[$removekey]);
				}
		}
		return $data;
	}
	/**
	 * Validates the incoming data against Epay's security hash to make sure this is not a
	 * fraudelent request.
	 */
	private function isValidRequest($data)
	{
		$alldata=$data;
	
		if ($this->params->get('md5', 0)) 
		{
			
			// Temp. replace hash with secret
			$hash = $data['hash'];
			
			//$data = $this->getOnlyResponseData($data);
			$data['hash'] = $this->params->get('secret', '');
			
			// Calculate checksum
			$checksum = md5(implode('', $data));  //md5(implode("", array_values($params)) . "SecretMD5Key");
			
			// Replace hash with original
			$data['hash'] = $hash;
			if ($checksum != $hash) {
				return false;
			}
		}
		return true;
	}
	
	private function _toPPDuration($days)
	{
		$ret = (object)array(
			'unit'		=> 'D',
			'value'		=> $days
		);

		// 0-90 => return days
		if ($days < 90) return $ret;

		// Translate to weeks, months and years
		$weeks = (int)($days / 7);
		$months = (int)($days / 30);
		$years = (int)($days / 365);

		// Find which one is the closest match
		$deltaW = abs($days - $weeks*7);
		$deltaM = abs($days - $months*30);
		$deltaY = abs($days - $years*365);
		$minDelta = min($deltaW, $deltaM, $deltaY);

		// Counting weeks gives a better approximation
		if ($minDelta == $deltaW) {
			$ret->unit = 'W';
			$ret->value = $weeks;

			// Make sure we have 1-52 weeks, otherwise go for a months or years
			if (($ret->value > 0) && ($ret->value <= 52)) {
				return $ret;
			} else {
				$minDelta = min($deltaM, $deltaY);
			}
		}

		// Counting months gives a better approximation
		if ($minDelta == $deltaM) {
			$ret->unit = 'M';
			$ret->value = $months;

			// Make sure we have 1-24 month, otherwise go for years
			if (($ret->value > 0) && ($ret->value <= 24)) {
				return $ret;
			} else {
				$minDelta = min($deltaM, $deltaY);
			}
		}

		// If we're here, we're better off translating to years
		$ret->unit = 'Y';
		$ret->value = $years;

		if ($ret->value < 0) {
			// Too short? Make it 1 (should never happen)
			$ret->value = 1;
		} elseif ($ret->value > 5) {
			// One major pitfall. You can't have renewal periods over 5 years.
			$ret->value = 5;
		}

		return $ret;
	}
	
}
