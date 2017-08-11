<?php
/**
 * @package    Common_Code
 * @author     TechJoomla <extensions@techjoomla.com>
 * @website    http://techjoomla.com
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
require_once dirname(__FILE__) . '/ccavenue/helper.php';
require_once dirname(__FILE__) . '/ccavenue/Crypto.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_ccavenue', JPATH_ADMINISTRATOR);

/**
 * plgPaymentCcavenue plugin class.
 *
 * @package  JGive
 * @since    1.8
 */
class  PlgPaymentCcavenue extends JPlugin
{
	/**
	 * Method _construct
	 *
	 * @param   String  &$subject  Subject
	 * @param   String  $config    Config
	 *
	 * @since    1.8.1
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();

		// Define Payment Status codes in payu  And Respective Alias in Framework
		$this->responseStatus = array('Success' => 'C', 'Failure' => 'P', 'Aborted' => 'E','ERROR' => 'E');
	}

	/**
	 * Method to take layout and return the file
	 *
	 * @param   String  $layout  Layout
	 *
	 * @return  file
	 *
	 * @since   1.8.1
	 */
	public function buildLayoutPath($layout)
	{
		$app = JFactory::getApplication();

		if (empty($layout))
		{
			$layout = "default";
		}

		$core_file = dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . $layout . '.php';
		$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' .
		$this->_type . '/' . $this->_name . '/' . $layout . '.php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return  $core_file;
		}
	}

	/**
	 * Method to Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   String  $vars    PAss the Variable
	 * @param   String  $layout  Default layout is default
	 *
	 * @return  html
	 *
	 * @since   1.8.1
	 */
	public function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Method to Build List of Payment Gateway in the respective Components.
	 *
	 * @param   String  $config  Config
	 *
	 * @return  Object
	 *
	 * @since   1.8.1
	 */
	public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj 		= new stdClass;
		$obj->name 	= $this->params->get('plugin_name');
		$obj->id	= $this->_name;

		return $obj;
	}

	/**
	 * Method to Constructs the Payment form in case of On Site Payment gateways like Auth.net
	 * & constructs the Submit button in case of offsite ones like Amazon.
	 *
	 * @param   String  $vars  Var
	 *
	 * @return  html
	 *
	 * @since   1.8.1
	 */
	public function onTP_GetHTML($vars)
	{
		$plgPaymentCcavenueHelper = new plgPaymentCcavenueHelper;

		/*
		$vars->action_url = $plgPaymentCcavenueHelper->buildCcavenueUrl();
		*/

		/*
		Take this receiver email address from plugin if component not provided it
		if(empty($vars->business))
		*/

		$vars->merchant_id = trim($this->params->get('merchant_id'));
		$working_key = $this->params->get('sandbox') ? trim($this->params->get('sandbox_working_key')) : trim($this->params->get('working_key'));
		$access_code = $this->params->get('sandbox') ? trim($this->params->get('sandbox_access_code')) : trim($this->params->get('access_code'));
		$vars->amount = (float) $vars->amount;

		/* $vars->notify_url = JURI::base().'ccavenue.'.JRequest::getCmd('option').'.php'; */

		$vars->order_id = (string) $vars->order_id;

		/* $vars->checksumval = $this->getCheckSum($vars->merchant_id,$vars->amount,$vars->order_id,$vars->notify_url,$vars->working_key);
		*/
		$merchant_data						= '';

		$gatewaydata						= array();
		$gatewaydata['merchant_id']  		= $vars->merchant_id;
		$gatewaydata['amount']  	  		= $vars->amount;
		$gatewaydata['order_id']  			= $vars->order_id;
		$gatewaydata['redirect_url'] 		= $vars->notify_url;
		$gatewaydata['billing_name']  		= $vars->userInfo['firstname'] . ' ' . $vars->userInfo['lastname'];
		$gatewaydata['billing_address']  	= $vars->userInfo['add_line1'];
		$gatewaydata['billing_city']		= $vars->userInfo['city'];
		$gatewaydata['billing_state']		= $vars->userInfo['state_code'];
		$gatewaydata['billing_zip']		    = $vars->userInfo['zipcode'];
		$gatewaydata['billing_country']	    = $vars->userInfo['country_code'];
		$gatewaydata['billing_tel']  		= $vars->phone;
		$gatewaydata['billing_email']  		= $vars->user_email;
		$gatewaydata['currency']  			= $vars->currency_code;

		foreach ($gatewaydata as $key => $value)
		{
			$merchant_data .= $key . '=' . urlencode($value) . '&';
		}

		$encrypted_data		= encrypt($merchant_data, $working_key);
		$ccavenue_url = $plgPaymentCcavenueHelper->buildCcavenueUrl();
		$vars->action_url = $ccavenue_url . $encrypted_data . '&access_code=' . $access_code;

		$html = $this->buildLayout($vars);

		return $html;
	}

	/*function getchecksum($MerchantId,$Amount,$OrderId,$URL,$WorkingKey) {
		$str = "$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";

		$adler = 1;
		$adler = $this->adler32($adler,$str);
		return $adler;
	}*/

	/*function verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey) {
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);

		if($adler == $CheckSum)
			return "true" ;
		else
			return "false" ;
	}*/

	/*function adler32($adler , $str) {
		$BASE =  65521 ;

		$s1 = $adler & 0xffff ;
		$s2 = ($adler >> 16) & 0xffff;
		for($i = 0 ; $i < strlen($str) ; $i++)
		{
			$s1 = ($s1 + Ord($str[$i])) % $BASE ;
			$s2 = ($s2 + $s1) % $BASE ;
			// echo "s1 : $s1 <BR> s2 : $s2 <BR>";

		}
		return $this->leftshift($s2 , 16) + $s1;
	}*/

	/*function leftshift($str , $num)
	{
		$str = DecBin($str);

		for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
		$str = "0".$str ;

		for($i = 0 ; $i < $num ; $i++) {
			$str = $str."0";
			$str = substr($str , 1 ) ;
			// echo "str : $str <BR>";
		}
		return $this->cdec($str) ;
	}*/

	/*function cdec($num)
	{
		$dec =  '';
		for ($n = 0 ; $n < strlen($num) ; $n++) {
			$temp = $num[$n] ;
			$dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
		}
		return $dec;
	}*/

	/**
	 * Method to Constructs the Payment form in case of On Site Payment gateways like Auth.net
	 * & constructs the Submit button in case of offsite ones like Amazon.
	 *
	 * @param   String  $data  Data
	 * @param   String  $vars  Pass the array
	 *
	 * @return  array
	 *
	 * @since   1.8.1
	 */
	public function onTP_Processpayment($data,$vars=array())
	{
		$isValid = true;
		$error = array();
		$error['code']	= '';
		$error['desc']	= '';

		/*
		$verify = $this->verifychecksum($data['Merchant_Id'], $data['Order_Id'], $data['Amount'], $data['AuthDesc'], $data['Checksum'], $working_key);
		$data['verify'] = $verify;
		*/

		/* commented by Dipti @7/9/12
		if (!$verify) { return false; }
		*/

		// Decrypt server data
		$decrypted_data = $this->validateData($data);

		// Error Handling
		$error = array();
		$error['code']	= $decrypted_data['status_code'];
		$error['desc']	= $decrypted_data['status_message'];

		// CHECK :compare response order id and send order id in notify URL
		$trxnstatus = '';
		$res_orderid = '';
		$res_orderid = $decrypted_data['order_id'];

		if ($isValid)
		{
			if (!empty($vars) && $res_orderid != $vars->order_id)
			{
				$trxnstatus = 'ERROR';
				$isValid = false;
				$error['desc'] .= "ORDER_MISMATCH " . " Invalid ORDERID; notify order_is " . $vars->order_id . ", and response " . $res_orderid;
			}
		}

		// Amount check
		if ($isValid)
		{
			if (!empty($vars))
			{
				// Check that the amount is correct
				$order_amount = (float) $vars->amount;
				$retrunamount = (float) $decrypted_data['amount'];
				$epsilon = 0.01;

				if (($order_amount - $retrunamount) > $epsilon)
				{
					// Change response status to ERROR FOR AMOUNT ONLY
					$trxnstatus = 'ERROR';
					$isValid = false;
					$error['desc'] .= "ORDER_AMOUNT_MISTMATCH - order amount= " . $order_amount . ' response order amount = ' . $retrunamount;
				}
			}
		}

		// END OF AMOUNT CHECK
		if (!empty($trxnstatus))
		{
			$payment_status = $this->translateResponse($trxnstatus);
		}
		else
		{
			$payment_status = $this->translateResponse($decrypted_data['order_status']);
		}

		$result = array(
						'order_id' => $decrypted_data['order_id'],
						'transaction_id' => $decrypted_data['tracking_id'],
						'buyer_email' => $decrypted_data['billing_email'],
						'status' => $payment_status,
						'txn_type' => $decrypted_data['payment_mode'],
						'total_paid_amt' => $decrypted_data['amount'],
						'raw_data' => $decrypted_data,
						'error' => $error
						);

		return $result;
	}

	/**
	 * Method to translate response according to status.
	 *
	 * @param   String  $payment_status  Payment Status
	 *
	 * @return  array
	 *
	 * @since   1.8.1
	 */
	public function translateResponse($payment_status)
	{
		foreach ($this->responseStatus as $key => $value)
		{
			if ($key == $payment_status)
			{
				return $value;
			}
		}
	}

	/**
	 * Method onTP_Storelog.
	 *
	 * @param   String  $data  Data
	 *
	 * @return  void
	 *
	 * @since   1.8.1
	 */
	public function onTP_Storelog($data)
	{
		$log_write = $this->params->get('log_write', '0');

		if ($log_write == 1)
		{
			$log = plgPaymentCcavenueHelper::Storelog($this->_name, $data);
		}
	}

	/**
	 * Method to validate data.
	 *
	 * @param   array  $data  Data
	 *
	 * @return  array
	 *
	 * @since   1.8.1
	 */
	public function validateData($data)
	{
		// Working Key should be provided here.
		$working_key = $this->params->get('sandbox') ? trim($this->params->get('sandbox_working_key')) : trim($this->params->get('working_key'));

		// This is the response sent by the CCAvenue Server
		$encResponse		= $data["encResp"];

		// Crypto Decryption used as per the specified working key.
		$rcvdString			= decrypt($encResponse, $working_key);
		$order_status		= "";
		$decryptValues		= explode('&', $rcvdString);
		$dataSize			= sizeof($decryptValues);

		for ($i = 0; $i < $dataSize; $i++)
		{
			$information		= explode('=', $decryptValues[$i]);

			if ($i == 3)
			{
				$order_status 	= $information[1];
			}
		}

		for ($i = 0; $i < $dataSize; $i++)
		{
			$information					= explode('=', $decryptValues[$i]);
			$response[$information[0]]	 	= urldecode($information[1]);
		}

		return $response;
	}
}
