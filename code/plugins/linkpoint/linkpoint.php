<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$lang =  JFactory::getLanguage();
$lang->load('plg_payment_linkpoint', JPATH_ADMINISTRATOR);
jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/linkpoint/linkpoint/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/linkpoint/helper.php');
	
class plgPaymentLinkpoint extends JPlugin
{
	var $_cache = null;

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->store_id 		= $this->params->get( 'store_id');
		$this->port		 	= $this->params->get( 'port', '1129');
		
		//Define Payment Status codes in Link[oint  And Respective Alias in Framework
		//APPROVED DECLINED, or FRAUD.
		
		$this->responseStatus= array(
			'APPROVED' =>'C',
			'DECLINED' =>'D',
			'FRAUD' =>'F',
		);
		

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

	function buildLayoutPath($layout) 
	{		
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__).DS.$this->_name.DS.'tmpl'.DS.'form.php';
		$override	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$this->_type.DS.$this->_name.DS.$layout.'.php';
		
		return (JFile::exists($override)) ? $override : $core_file;
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
	
		//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like linkpoint
	function onTP_GetHTML($vars)
	{
		$html = $this->buildLayout($vars);
		return $html;
	}
	
	
	function onTP_Processpayment($data) 
	{
	if(JVERSION >='1.6.0')
	include	JPATH_SITE.'/plugins/payment/linkpoint/linkpoint/lib/lphp.php';
	else
	include	JPATH_SITE.'/plugins/payment/linkpoint/lib/lphp.php';
	
	if(JVERSION >='1.6.0')
		$pemfilepath=JPATH_SITE.'/plugins/payment/linkpoint/linkpoint/staging_cert.pem';
	else
		$pemfilepath=JPATH_SITE.'/plugins/payment/linkpoint/staging_cert.pem';
		
		
	$plgPaymentLinkpointHelper=new plgPaymentLinkpointHelper();	
	$host=$plgPaymentLinkpointHelper->buildLinkpointUrl();		
	$orderid = $data['oid'];
	
	$mylphp = new lphp;
	$order["host"]       	= $host;
	$order["port"]       	= $this->port;
	$order["keyfile"] 		= $pemfilepath;
	$order["configfile"] 	= $this->store_id;       

	$order["ordertype"]         = "SALE";
	$testmode 		= $this->params->get( 'testmode', '1' );
	if($testmode==1)
	{
		$order["result"]            = "GOOD";  		# For test transactions, set to GOOD, DECLINE, or DUPLICATE


	}
	else
	{
			$order["result"]            = "LIVE"; 
	}
	$order["transactionorigin"] = "ECI"; 		# For credit card retail txns, set to RETAIL, for Mail order/telephone order, set to MOTO, for e-commerce, leave out or set to ECI
	$order["oid"]               = $data['oid'];  # Order ID number must be unique. If not set, gateway will assign one.

	// Transaction Details		
	$order["chargetotal"] = $data['chargetotal'];

	//Card Info
	
	$order["cardnumber"]   = $data['creditcard_number']; 
	$order["cardexpmonth"] = str_pad($data['expire_month'], 2, "0", STR_PAD_LEFT);
	$order["cardexpyear"]  = substr($data['expire_year'], 2);
	$order["cvmvalue"]     = $data['creditcard_code'];
	$order["debug"] = "true";  # for development only - not intended for production use

	$raw_data = $mylphp->curl_process($order);  # use curl methods
	$status=$this->translateResponse($raw_data['r_approved']);
	
	//Error Handling
	$error=array();
	$error['code']	=$raw_data['r_code'];
	$error['desc']	=$raw_data['r_message '];
	
	$result = array('transaction_id'=>md5($data['oid']),
					'order_id'=>$data['oid'],
					'status'=>$status,
					'total_paid_amt'=>$data["chargetotal"],
					'raw_data'=>$raw_data,
					'error'=>$error,
					'return'=>$data['return'],
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
			$log = plgPaymentLinkpointHelper::Storelog($this->_name,$data);
	
	}
	
	

	
}
