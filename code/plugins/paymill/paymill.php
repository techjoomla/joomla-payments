<?php


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   CategoryName
 * @package    PackageName
 * @author     Original Author <author@example.com>
 * @author     Another Author <another@example.com>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PackageName
 * @see        NetOther, Net_Sample::Net_Sample()
 * @since      File available since Release 1.2.0
 * @deprecated File deprecated in Release 2.0.0
 */

 /**
 * Methods return this if they succeed
 */

defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

jimport( 'joomla.filesystem.file' );

jimport( 'joomla.plugin.plugin' );

if(JVERSION >='1.6.0')
require_once(JPATH_SITE.'/plugins/payment/paymill/paymill/helper.php');
else
require_once(JPATH_SITE.'/plugins/payment/paymill/helper.php');
//Set the language in the class
$lang =  JFactory::getLanguage();
$lang->load('plg_payment_paymill', JPATH_ADMINISTRATOR);
class plgpaymentpaymill extends JPlugin 
{
	private $_payment_gateway = 'payment_paymill';
	private $_log = null;
	
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		
		$config = JFactory::getConfig();
		//PUBLIC_KEY IN JS 
		//PRIVATE_KEY IN API KEY
		//Define Payment Status codes in Authorise  And Respective Alias in Framework
		//closed = Approved, Pending = Declined, failed = Error, open = Held for Review
		$this->responseStatus= array(
			'closed' =>'C',
			'Pending' =>'D',
			'failed' =>'E',
			'open'=>'UR');
		//error code in api error
		$this->code_arr = array (
		'internal_server_error'       => JText::_('INTERNAL_SERVER_ERROR'),
		'invalid_public_key'    	  => JText::_('INVALID_PUBLIC_KEY'),
		'unknown_error'               => JText::_('UNKNOWN_ERROR'),	
		'3ds_cancelled'               => JText::_('3DS_CANCELLED'),
		'field_invalid_card_number'   => JText::_('FIELD_INVALID_CARD_NUMBER'),
		'field_invalid_card_exp_year' => JText::_('FIELD_INVALID_CARD_EXP_YEAR'),
		'field_invalid_card_exp_month'=> JText::_('FIELD_INVALID_CARD_EXP_MONTH'),
		'field_invalid_card_exp'      => JText::_('FIELD_INVALID_CARD_EXP'),
		'field_invalid_card_cvc'      => JText::_('FIELD_INVALID_CARD_CVC'),
		'field_invalid_card_holder'   => JText::_('FIELD_INVALID_CARD_HOLDER'),
		'field_invalid_amount_int'    => JText::_('FIELD_INVALID_AMOUNT_INT'),
		'field_invalid_amount'        => JText::_('FIELD_INVALID_AMOUNT'),
		'field_invalid_currency'      => JText::_('FIELD_INVALID_CURRENCY'),
		'field_invalid_account_number'=> JText::_('FIELD_INVALID_AMOUNT_NUMBER'),
		'field_invalid_account_holder'=> JText::_('FIELD_INVALID_ACCOUNT_HOLDER'),
		'field_invalid_bank_code'     => JText::_('FIELD_INVALID_BANK_CODE')
		);
		$this->public_key = $this->params->get('public_key');
		$this->private_key = $this->params->get( 'private_key');
		$this->testmode = $this->params->get( 'payment_mode', '1' );	
	}
	
	/* Internal use functions */
	public function buildLayoutPath($layout="default") {
		if(empty($layout))
			$layout="default";
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout.'.php';
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
	public function buildLayout($vars, $layout = 'default' )
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
    public function getParamResult($name, $default = '') 
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
	public function onTP_GetInfo($config)
	{
		if(!in_array($this->_name,$config))
		return;
		$obj 		= new stdClass;
		$obj->name 	= $this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}
	
	
	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Paypal
	public function onTP_GetHTML($vars)
	{
		$session = JFactory::getSession();
		$session->set('amount', $vars->amount);
		$session->set('currency_code', $vars->currency_code);
		if(!empty($vars->payment_type) and $vars->payment_type!='')
			$payment_type=$vars->payment_type;
		else
			$payment_type='';
		$html = $this->buildLayout($vars,$payment_type);
		return $html;
	}
	
	function onTP_Processpayment($data,$vars=array()) 
	{
		$isValid = true;
		$error=array();
		$error['code']	='';
		$error['desc']	='';
		$trxnstatus='';
		
		//API HOST KEY
		define('PAYMILL_API_HOST', 'https://api.paymill.com/v2/');
		//FROM PAYMILL PLUGIN BACKEND 
		define('PAYMILL_API_KEY', $this->private_key);
		set_include_path(implode(PATH_SEPARATOR, array(realpath(realpath(dirname(__FILE__)) . '/lib'),get_include_path(),)));
		//CREATED TOKEN 
		$token = $data["token"];
		$session = JFactory::getSession();
		if ($token) 
		{
				// access lib folder
				require "paymill/lib/Services/Paymill/Transactions.php";
				//pass api key and private key to Services_Paymill_Transactions function
				$transactionsObject = new Services_Paymill_Transactions(PAYMILL_API_KEY, PAYMILL_API_HOST);

				$params = array(
				'amount'      => ($session->get('amount') *100), //amount *100
				'currency'    => $session->set('currency_code') ,   // ISO 4217
				'token'       => $token,
				'description' => 'Test Transaction'
				);
				$transaction = $transactionsObject->create($params);

				if($transaction['error'])
				{
					$error['code']	='';
					$error['desc']	=$transaction['error'];
					
					$result = array('transaction_id'=>'',
								'order_id'=>$data["order_id"],
								'status'=>'E',
								'total_paid_amt'=>'0',
								'raw_data'=>'',
								'error'=>$transaction['error'],
								'return'=>$data['return']
								);
								return $result;
				}
				else
				{
					//if error not find 
					//$status varible
					
					// amount check // response amount in cent
					$gross_amt=(float)(($transaction['origin_amount']) / (100));
					if($isValid ) {
						if(!empty($vars))
						{
							// Check that the amount is correct
							$order_amount=(float) $vars->amount;
							$retrunamount =  (float)$gross_amt;
							$epsilon = 0.01;
							
							if(($order_amount - $retrunamount) > $epsilon)
							{
								$trxnstatus = 'failed';  // change response status to ERROR FOR AMOUNT ONLY
								$isValid = false;
								$error['desc'] .= " ORDER_AMOUNT_MISTMATCH - order amount= ".$order_amount . ' response order amount = '.$retrunamount;
							}
						}
					}
					if($trxnstatus ==  'failed'){
						$status=$this->translateResponse($ttrxnstatus);
					} else {
						$status=$this->translateResponse($transaction['status']);
					}
					//array pass to translate function 
					$result = array('transaction_id'=>$transaction['id'],
									'order_id'=>$data["order_id"],
									'status'=>$status,
									'total_paid_amt'=>$transaction['origin_amount'],
									'raw_data'=>json_encode($transaction),
									'error'=>$error,
									'return'=>$data['return']
									);
									
									return $result;
				}
			
		
		}
		else
		{
			$result = array('transaction_id'=>'',
								'order_id'=>$data["order_id"],
								'status'=>'E',
								'total_paid_amt'=>'0',
								'raw_data'=>'',
								'error'=>$transaction['error'],
								'return'=>$data['return']
								);
			return $result;
			
		}//end if token
	}
	
	//translate response in required format
	private function translateResponse($payment_status)
	{
			foreach($this->responseStatus as $key=>$value)
			{
				if($key==$payment_status)
				return $value;		
			}
	}
	
	//store order log in log files
	private function onTP_Storelog($data)
	{
			$log = plgPaymentpaymillHelper::Storelog($this->_name,$data);
	
	}
	
}

