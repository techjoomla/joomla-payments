<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.plugin.plugin' );

require_once(JPATH_SITE.'/plugins/payment/transfirst/transfirst/helper.php');


//load language
$lang =JFactory::getLanguage();
$extension = 'plg_payment_transfirst';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

class plgpaymentTransfirst extends JPlugin
{
	var $_payment_gateway = 'payment_transfirst';
	var $_log = null;

	function __construct(& $subject, $config)
	{
		global $transfirst_merchid,$transfirst_authnetmode,$transfirst_transkey,$godaddy_hosting;
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		//Define Payment Status codes in Transfirst  And Respective Alias in Framework
		//00 = Approved, 16 = Declined, 06 = Error, 10 = Held for Review
		$this->responseStatus= array(
			'00'=>'C',
			'08'=>'D',
			'11'=>'D',
			'16'=>'D',
			'39'=>'D',
			'51'=>'D',
			'54'=>'D',
			'62'=>'D',
			'82'=>'D',
			'85'=>'D',
			'N7'=>'D',
			'P2'=>'D',
			'R0'=>'D',
			'R1'=>'D',
			'Q1'=>'D',
			'03'=>'E',
			'06'=>'E',
			'12'=>'E',
			'13'=>'E',
			'14'=>'E',
			'79'=>'E',
			'ERROR' => 'E',
			'02'=>'p',
			'10'=>'P',
			'32'=>'P',
			
		);
 		$this->merchant_id = $this->params->get('merchant_id', '1' );
		$this->reg_key = $this->params->get('reg_key', '1' );
		$this->godaddy_hosting = $godaddy_hosting;
	}

	/* Internal use functions */
	function buildLayoutPath($layout="default") {
		if(empty($layout))
		$layout="default";
		$app = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout.'.php';
		$override= JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . 'plugins' . '/' . $this->_type . '/' . $this->_name . '/' . $layout.'.php';
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
	function buildLayout($vars, $layout='default')
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
	function getParamResult($name,$default='')
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

	function onTP_Processpayment($data,$vars) 
	{
		$resData=$data;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		$isValid = true;
		
		//require transfirst.class file
		require_once(JPATH_SITE . '/' . 'plugins' . '/' . 'payment' . '/' . 'transfirst' . '/' . 'transfirst' . '/' . 'Transfirst.class.php');
		//YYMM Expiration Date: This is the expiration date of the card
		$exp_year = substr($data['card_exp_year'],2); //Year YYYY to YY
		$exp_month=$data['card_exp_month'];
		//MM if less than 10
		if($data['card_exp_month']<10)
		{
			$exp_month="0".$data['card_exp_month'];
		}

		//This is the type of card. Valid Values: 0 = VISA  1 = MasterCard 2 = AMEX 3 = Discover 4 = Diner’s Club 5 = JCB
		switch($data['activated'])
		{
			case 'Visa':
				$card_no=0;
			break;

			case 'Mastercard':
				$card_no=1;
			break;

			case 'AmericanExpress':
				$card_no=2;
			break;

			case 'Discover':
				$card_no=3;
			break;

			case 'DinersClub':
				$card_no=4;
			break;

			case 'JCB':
				$card_no=5;
			break;
		}

		// convert the amount upto two decimal number format
		$amount=$data['amount']*100;
		$amount= round($amount);
		// Add leading 0 to the amount
		$amount="0".$amount;

		//build the paramer_array to send data over gateway
		$parameter_array=array(
			'merc'=>array(
				'id'		=>$this->merchant_id,
				'regKey'	=>$this->reg_key,
				'inType'	=>"1", //Input Type: This is the input type from which the request is initiated. Valid Values: 1 = Merchant Web Service
				),
			'tranCode'	=>"1", //This indicates the type of transaction. Valid Values: 1 = Auth & Settle
			'card'=>array(
				'type'		=>$card_no, //This is the type of card. Valid Values: 0 = VISA  1 = MasterCard 2 = AMEX 3 = Discover 4 = Diner’s Club 5 = JCB
				'pan'		=>$data['card_num'],//Primary Account Number
				'sec'			=>$data['card_code'],//CVV2/CVC2/CID
				'xprDt'		=>$exp_year.$exp_month, //YYMM Expiration Date: This is the expiration date of the card
				'dbtOrCdt'	=>$data['card_type'],
				),
			'reqAmt'=>$amount, //This contains the amount associated with this transaction in minor denominations. Conditions: No special characters are allowed. A leading zero is required.
		);
		/*	"usrDef" => array(
			'name'=>$data['order_id'],
			'val'=>$data['order_id']
			),*/
		//print_r($parameter_array);die;

		//get the action url to send the data to the gateway
		$plgPaymentTransfirstHelper=new plgPaymentTransfirstHelper();
		$soapUrl=$plgPaymentTransfirstHelper->buildTransfirstUrl();

		$Transfirst=new Transfirst($soapUrl,'');
		$resp=$Transfirst->SendTran($parameter_array);

		$tranData=array();
		$tranData=$resp->tranData;
		$transaction_id =$tranData->tranNr;
		
		// amount check
		// response amount in cent
		$gross_amt=(float)(($tranData->amt) / (100));
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
					$error['code'] .= 'ERROR';
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
				}
			}
		}
		// END OF AMOUNT CHECK
		
		if($trxnstatus == 'ERROR'){
			$payment_status=$this->translateResponse($trxnstatus);
		}else {
			$payment_status=$this->translateResponse($resp->rspCode);
		}
		//Add the status in array status if not exists
		if(!$payment_status)
		{
			$payment_status='P';
		}
		
		// if error code is present then add error detail in error array
		if($resp->resCode != '00'){
			$error['code'] .= $resp->rspCode;
			if($resp->rspCode)
				$error['desc'] .=JText::_('PLG_TRANSFIRST_RESP_CODE_'.$resp->rspCode);
		}
		
		$result=array('transaction_id'=>$transaction_id,
						'order_id'=>$data['order_id'],
						'status'=>$payment_status,
						'total_paid_amt'=>$gross_amt,
						'raw_data'=>$resp,
						'error'=>$error,
						'return'=>$data['return'],
				);
	return $result;
	}

	function translateResponse($payment_status)
	{
		foreach($this->responseStatus as $key=>$value)
		{
			if($key==$payment_status)
			return $value;
		}
	}

	function onTP_Storelog($data)
	{
			$plgPaymentTransfirstHelper = new plgPaymentTransfirstHelper();
			$log = $plgPaymentTransfirstHelper->Storelog($this->_name,$data);
	}
}

