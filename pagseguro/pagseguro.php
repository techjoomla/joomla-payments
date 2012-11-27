<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/pagseguro/pagseguro/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/pagseguro/helper.php');
	
	require_once JPATH_SITE.'/plugins/payment/pagseguro/lib/PagSeguroLibrary.php';
class  plgPaymentPagseguro extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config =& JFactory::getConfig();

		
		//Define Payment Status codes in Pagseguro  And Respective Alias in Framework
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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Pagseguro
	function onTP_GetHTML($vars)
	{
		$vars->sellar_email = $this->params->get('sellar_email');
		$vars->token = $this->params->get('token');		
		$vars->action_url = plgPaymentPagseguroHelper::buildPagseguroUrl($vars,1);
		//Take this receiver email address from plugin if component not provided it

		
		$html = $this->buildLayout($vars);

		return $html;
	}

	
	
	function onTP_Processpayment($data) 
	{
	$vars->sellar_email = $this->params->get('sellar_email');
		$vars->token = $this->params->get('token');		
		$verify = plgPaymentPagseguroHelper::validateIPN($data,$vars);
		//if (!$verify) { return false; }	
		$pstatus=$data->get('status');
		$status=$this->translateResponse($pstatus);		

		

		$result = array(
						'order_id'=>$data->get('udf1'),
						'transaction_id'=>$data->get('mihpayid'),
						'buyer_email'=>$data->get('email'),
						'status'=>$data->get('status'),
						'txn_type'=>$data->get('mode'),
						'total_paid_amt'=>$data->get('amount'),
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
			$log = plgPaymentPagseguroHelper::Storelog($this->_name,$data);
	
	}	
}
