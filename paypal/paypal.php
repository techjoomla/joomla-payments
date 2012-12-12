<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
$lang=JFactory::getLanguage();
$lang->load('plg_payment_paypal', JPATH_ADMINISTRATOR);
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/paypal/paypal/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/paypal/helper.php');
class  plgPaymentPaypal extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config =& JFactory::getConfig();


		//Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus= array(
 	 'Completed'  => 'C','Pending'  => 'P',
 	 'Failed'=>'E','Denied'=>'D',
 	 'Refunded'=>'RF','Canceled_Reversal'=>'CRV',
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

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones like Paypal
	function onTP_GetHTML($vars)
	{
		$vars->action_url = plgPaymentPaypalHelper::buildPaypalUrl();
		//Take this receiver email address from plugin if component not provided it
		if(empty($vars->business))
			$vars->business=$this->params->get('business');

		//if component does not provide cmd
		if(empty($vars->cmd))
			echo $vars->cmd='_xclick';

		$html = $this->buildLayout($vars);

		return $html;
	}


	function onTP_Processpayment($data)
	{
		$verify = plgPaymentPaypalHelper::validateIPN($data);
		if (!$verify) { return false; }

		$payment_status=$this->translateResponse($data['payment_status']);

		$result = array(
						'order_id'=>$data['custom'],
						'transaction_id'=>$data['txn_id'],
						'buyer_email'=>$data['payer_email'],
						'status'=>$payment_status,
						'subscribe_id'=>$data['subscr_id'],
						'txn_type'=>$data['txn_type'],
						'total_paid_amt'=>$data['mc_gross'],
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
			$log = plgPaymentPaypalHelper::Storelog($this->_name,$data);

	}
}
