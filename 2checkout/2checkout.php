<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
$lang=JFactory::getLanguage();
$lang->load('plg_payment_2checkout', JPATH_ADMINISTRATOR);
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/2checkout/2checkout/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/2checkout/helper.php');

class  plgPayment2checkout extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config =& JFactory::getConfig();


		//Define Payment Status codes in Paypal  And Respective Alias in Framework
		$this->responseStatus= array(
			'deposited'  => 'C',
			'pending'  => 'P',
			'approved'=>'p',
			'declined'=>'X',
			'Refunded'=>'RF'
		);
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
	$layout=trim($layout);
	if(empty($layout))
	$layout='default';

		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__).DS.$this->_name.DS.'tmpl'.DS.$layout.'.php';
		$override		= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$this->_type.DS.$this->_name.DS.$layout.'.php';
		if(JFile::exists($override)) {
			return $override;
		} else {
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
     $vars->action_url = 'https://www.2checkout.com/checkout/purchase';
		 $vars->sid = $this->params->get('sid','');
		 $vars->demo = $this->params->get('demo',0) ? 'Y' : 'N';
		 $vars->lang = $this->params->get('lang','en');
		 $vars->pay_method = $this->params->get('pay_method','cc');


		$html = $this->buildLayout($vars);
		return $html;
	}



	function onTP_Processpayment($data)
	{
		$secret = $this->params->get('secret','cc');
		/*$verify = plgPayment2checkoutHelper::validateIPN($data,$secret);
		if (!$verify) { return false;
		}	*/

		$id = array_key_exists('vendor_order_id', $data) ? (int)$data['vendor_order_id'] : -1;

		$message_type=$data['message_type'];
		$payment_status=$this->translateResponse($data['invoice_status']);
		if($message_type == 'REFUND_ISSUED'){
			$payment_status='RF';
		}

		$result = array();
		if($id)
		{
			$result = array(
						'order_id'=>$id,
						'transaction_id'=>$data['order_number'],
						'buyer_email'=>$data['email'],
						'status'=>$payment_status,
						'subscribe_id'=>$data['subscr_id'],
						'txn_type'=>$data['pay_method'],
						'total_paid_amt'=>$data['total'],
						'raw_data'=>$data,
						'error'=>$error,
						);
		}
		return $result;
	}

	function translateResponse($invoice_status){

    	foreach($this->responseStatus as $key=>$value)
				{
					if($key==$invoice_status)
					return $value;
				}
	}
	function onTP_Storelog($data)
	{
			$log = plgPayment2checkoutHelper::Storelog($this->_name,$data);

	}
}
