<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
 
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
//require_once JPATH_COMPONENT . DS . 'helper.php';
$lang = JFactory::getLanguage();
$lang->load('plg_payment_ewallet', JPATH_ADMINISTRATOR);

require_once(JPATH_SITE.'/plugins/payment/ewallet/ewallet/helper.php');

$api_wallet = JPATH_SITE . '/' . 'components' . '/' . 'com_ewallet' . '/' . 'ewallet.php';
if ( file_exists($api_wallet))
{
	$path = JPATH_SITE . '/' . 'components' . '/' . 'com_ewallet' . '/' . 'helper.php';
	if(!class_exists('comewalletHelper'))
	{
		JLoader::register('comewalletHelper', $path );
		JLoader::load('comewalletHelper');
	}
}

class plgpaymentewallet extends JPlugin
{
	var $_payment_gateway = 'payment_ewallet';
	var $_log = null;

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//Set the language in the class
		$config = JFactory::getConfig();

		//Define Payment Status codes in Authorise  And Respective Alias in Framework
		//1 = Approved, 2 = Declined, 3 = Error, 4 = Held for Review
		$this->responseStatus= array(
			'Success' =>'C',
			'Failure' =>'E',
			// Manoj - added start.
			'Refund' => 'RF'
			// Manoj - added end.
		);
	}

	function buildLayoutPath($layout) {
		$app = JFactory::getApplication();
		$core_file 	= dirname(__FILE__) . '/' . $this->_name . '/' . 'tmpl' . '/' . 'form.php';
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

	//Builds the layout to be shown, along with hidden f'Failure' =>'X',ields.
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

	function onTP_GetHTML($vars)
	{
		$db = JFactory::getDBO();
		$api_wallet = JPATH_SITE . '/' . 'components' . '/' . 'com_ewallet' . '/' . 'ewallet.php';
		if ( file_exists($api_wallet))
		{
			$comewalletHelper = new comewalletHelper();
			$user_balance = $comewalletHelper->getUserBalance();
			$vars->user_points = $user_balance;
			$vars->convert_val = $this->params->get('conversion');

			$html = $this->buildLayout($vars);
			return $html;
		}
	}

	function onTP_GetInfo($config)
	{
		if(!in_array($this->_name,$config))
		return;
		$obj 		= new stdClass;
		$obj->name 	=$this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}

	function onTP_ProcessSubmit($data,$vars)
	{
		$submitVaues['order_id'] =$vars->order_id;
		$submitVaues['client'] =$vars->client;
		$submitVaues['total'] =number_format($vars->amount ,2);
		$submitVaues['return'] =$vars->return;
		$submitVaues['user_id'] =$vars->user_id;
		$submitVaues['plugin_payment_method'] ='onsite';
		$submitVaues['payment_description'] =$vars->payment_description;

		/* for onsite plugin set the post data into session and redirect to the notify URL */
		$session = JFactory::getSession();
		$session->set('payment_submitpost',$submitVaues);
		JFactory::getApplication()->redirect($vars->url);
	}

	//Adds a row for the first time in the db, calls the layout view
	function onTP_Processpayment($data)
	{
		$api_wallet = JPATH_SITE . '/' . 'components' . '/' . 'com_ewallet' . '/' . 'helper.php';
		$payment_status=$this->translateResponse('Failure');
		if ( file_exists($api_wallet))
		{
			$comewalletHelper = new comewalletHelper();
			$points_count = $comewalletHelper->getUserBalance($data['user_id']);
			$convert_val = $this->params->get('conversion');
			$points_charge=$data['total']*$convert_val;
			//$count = $points_count - $points_charge;
			if($points_charge <= $points_count )
			{
				if($comewalletHelper->addUserSpent($data['user_id'],$points_charge, $data['client'],$data['payment_description']) )
				{
					$payment_status=$this->translateResponse('Success');
				}
			}
		}
		$result = array('transaction_id'=>'',
				'order_id'=>$data['order_id'],
					'status'=>$payment_status,
					'total_paid_amt'=>$data['total'],
					'raw_data'=>json_encode($data),
					'error'=>'',
					'return'=>$data['return'],
					);
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
		$plgPaymentEwalletHelper = new plgPaymentEwalletHelper();
			$log = $plgPaymentEwalletHelper->Storelog($this->_name,$data);

	}

	// Manoj - added refund function start.
	/*
	 * Sample $data array data expected -
	$data                        = array();
	$data['order_id']            = $orderData->id;
	$data['user_id']             = $orderData->donor_id;
	$data['total']               = $orderData->amount;
	$data['client']              = 'com_jgive';
	$data['payment_description'] = JText::_('COM_JGIVE_PROCESS_REFUND_DEFAULT_MSG') . ' ' . $orderData->title;
	$data['return']              = '';
	*/
	function onTP_ProcessRefund($data)
	{
		$api_wallet     = JPATH_SITE  . '/' . 'components' . '/' . 'com_ewallet' . '/' .  'helper.php';
		$payment_status = $this->translateResponse('Refund');

		if(file_exists($api_wallet))
		{
			$comewalletHelper = new comewalletHelper();
			$points_count     = $comewalletHelper->getUserBalance($data['user_id']);
			$convert_val      = $this->params->get('conversion');
			$points_to_add    = $data['total'] * $convert_val;

			// Call component api function-> function addTransaction($user_id,$amount,$type,$comment).
			if($comewalletHelper->addTransaction($data['user_id'], $points_to_add, 'C', $data['payment_description']))
			{
				$payment_status = $this->translateResponse('Success');
			}

		}

		$result = array(
			'transaction_id'  => '',
			'order_id'        => $data['order_id'],
			'client'          => $data['client'],
			'status'          => $payment_status,
			'total_paid_amt'  => $data['total'],
			'raw_data'        => json_encode($data),
			'error'           => '',
			'return'          => $data['return'],
		);

		return $result;
	}
	// Manoj - added refund function end.
}
