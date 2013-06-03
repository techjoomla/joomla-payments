<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/payu/payu/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/payu/helper.php');

$lang =  JFactory::getLanguage();
$lang->load('plg_payment_payu', JPATH_ADMINISTRATOR);
class  plgPaymentPayu extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		
		//Define Payment Status codes in payu  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'success'  => 'C','pending'  => 'P',
 	 'failure'=>'E'
  
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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Payu
	function onTP_GetHTML($vars)
	{
		$plgPaymentPayuHelper= new plgPaymentPayuHelper();
		$vars->action_url = $plgPaymentPayuHelper->buildPayuUrl();
		//Take this receiver email address from plugin if component not provided it
//		if(empty($vars->business))

			$vars->key = $this->params->get('key');
			$vars->salt = $this->params->get('salt');	
			$this->preFormatingData($vars);	 // fomating on data
			$html = $this->buildLayout($vars);

		return $html;
	}

	
	
	function onTP_Processpayment($data) 
	{
		//$verify = plgPaymentPayuHelper::validateIPN($data);
		//if (!$verify) { return false; }	
		
		$data['status']=$this->translateResponse($data['status']);		

		//Error Handling
		$error=array();
		$error['code']	=$data['unmappedstatus']; //@TODO change these $data indexes afterwards
		$error['desc']	=(isset($data['field9'])?$data['field9']:'');

		$result = array(
						'order_id'=>$data['udf1'],
						'transaction_id'=>$data['mihpayid'],
						'buyer_email'=>$data['email'],
						'status'=>$data['status'],
						'txn_type'=>$data['mode'],
						'total_paid_amt'=>$data['amount'],
						'raw_data'=>$data,
						'error'=>$error,
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
			$log = plgPaymentPayuHelper::Storelog($this->_name,$data);
	
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
	}
	
}
