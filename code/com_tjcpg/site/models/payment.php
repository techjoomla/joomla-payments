<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Tjcpg model.
 */
class TjcpgModelPayment extends JModelLegacy
{
	
	function confirmpayment($pg_plugin,$oid)
	{
		$post	= JRequest::get('post');
		$vars = $this->getPaymentVars($pg_plugin,$oid);
		if(!empty($post) && !empty($vars) ){
			JPluginHelper::importPlugin('payment', $pg_plugin);
			$dispatcher = JDispatcher::getInstance();
			$result = $dispatcher->trigger('onTP_ProcessSubmit', array($post,$vars));
		}
		else{
			JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');
		}
		//die("000");
	}
function processpayment($post,$pg_plugin,$order_id)
	{
		$tjcpgHelper = new tjcpgHelper;
		//	GETTING MENU Itemid
		$jinput=JFactory::getApplication()->input;
		$jinput->set('remote',1);
		
		//$sacontroller = new quick2cartController();
		//$sacontroller->execute('clearcart');
		$orderItemid = $tjcpgHelper->getitemid('index.php?option=com_tjcpg&view=payment');
		$chkoutItemid=$orderItemid;
		//$chkoutItemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cartcheckout');
		$return_resp=array();

		//Authorise Post Data
		if(!empty($post['plugin_payment_method']) && $post['plugin_payment_method']=='onsite')
			$plugin_payment_method=$post['plugin_payment_method'];
		
		$vars = $this->getPaymentVars($pg_plugin,$order_id);
		
		//START :: TRIGGER PAYMENT PLUGIN
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment', $pg_plugin);
		$data = $dispatcher->trigger('onTP_Processpayment', array($post,$vars));
		$data = $data[0];
		//END :: TRIGGER PAYMENT PLUGIN
		
		// Add details in log file
		$res=@$this->storelog($pg_plugin,$data);
	
		//get order id
		if(empty($order_id))
			$order_id=$data['order_id'];
		
		// RIGHT NOW WE R NOT ADDING CODE FOR GUEST USER
		$guest_email="";
		$data['processor']=$pg_plugin;
		$data['status']=trim($data['status']);

		if(empty($data['status'])){
			$data['status'] = 'P';
			$return_resp['status']='0';
		}
		else if($data['status']=='C' )
		{
			$data['status'] = 'C';
			$return_resp['status']='1';
		}
		/*else if($order_amount != $data['total_paid_amt']){
			$data['status'] = 'E';
			$return_resp['status']='0';
		}*/
		// IF NOT CONFORM ORDER GET ERORR MSG
		if($data['status']!='C' && !empty($data['error']) ){
			$return_resp['msg']=$data['error']['code']." ".$data['error']['desc'];
		}
		$this->updateOrder($data);
		//$comquick2cartHelper->updatestatus($order_id,$data['status']);
		$return_resp['return']=JURI::root().substr(JRoute::_("index.php?option=com_tjcpg&view=orders&layout=order".$guest_email."&orderid=".($order_id)."&processor={$pg_plugin}&Itemid=".$orderItemid,false),strlen(JURI::base(true))+1);	
		return $return_resp;
	}
	function store($post)
	{	
		$db= JFactory::getDBO();
		$user = JFactory::getUser();
			
		$row = new stdClass;
		
		// GETTING DATE AND TIME
		$timestamp	= date("Y-m-d H:i:s");
		// Get the IP Address
		if (! empty ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = 'unknown';
		}
		
		$row->payee_id 				= $user->id;
		$row->user_info_id 			= $user->id;
		$row->name 	=$user->name;
		$row->email =$user->email;
		// FINAL AMOUNT
		$row->amount 			= $post['amount'];
		
		// ORIGINAL AMT FOR PRODUCT/ ITEMS    // we are not considering tax and shipping charges
		$row->original_amount 		= $post['amount'] ;
		
		//NOT CONSIDERING TAX, ADD ACCORDING TO YOUR NEED
		$row->order_tax 			= 0;
		$row->order_tax_details 	= '';
		
		//NOT CONSIDERING SHIPPING, ADD ACCORDING TO YOUR NEED
		$row->order_shipping 		=0;
		$row->order_shipping_details 	='';
		
		//NOT CONSIDERING COUPON, ADD ACCORDING TO YOUR NEED
		$row->coupon_code  		= '';
		
		$row->customer_note 		= '';
		$row->processor 		= $post['gateways'];
			
		$row->cdate 				= $timestamp;
		$row->mdate 				= $timestamp;
		$row->ip_address 			= $ip;
		
		// GETTING CURRENCY FROM COMPONENT PARAMS
		$params = JComponentHelper::getParams('com_tjcpg');
		$row->currency			= $params->get("addcurrency","USD");

		if(!$db->insertObject('#__tjcpg_orders',$row,'id'))
		{
			echo $db->stderr();
			return 0;
		}	
		return $insert_order_id=$db->insertid();
	}
	function getOrderInfo($order_id)
	{
		$db = JFactory::getDBO();
		$query="SELECT * FROM `#__tjcpg_orders` WHERE `id`=".$order_id;
		$db->setQuery($query);
		return $order_result = $db->loadObject();
	}
	/**
	 * @params
	 * 			$pg_plugin - plugin name
	 * 			$tid - order id
	 * @return - HTML from payment gateway
	 * */
	function getHTML($pg_plugin,$tid)
	{
		// GETTING PAYMENT FORM VARIABLES
		$vars = $this->getPaymentVars($pg_plugin,$tid);
		//GETTING PAYMENT HTML
		JPluginHelper::importPlugin('payment', $pg_plugin);
		$dispatcher = JDispatcher::getInstance();
		$html = $dispatcher->trigger('onTP_GetHTML', array($vars));
		return $html;
	}
	/**
	 * @params
	 * 			$pg_plugin - plugin name
	 * 			$oid - order id
	 * @return - HTML from payment gateway
	 * */
	
	function getPaymentVars($pg_plugin, $orderid)
	{
		$tjcpgHelper = new tjcpgHelper;
		
		//	GETTING MENU Itemid
		$params = JComponentHelper::getParams( 'com_tjcpg' );
		$orderItemid = $tjcpgHelper->getitemid('index.php?option=com_tjcpg&view=payment');

		$pass_data = $this->getOrderInfo($orderid);
		$vars = new stdClass;
		$vars->order_id = $orderid;
		$vars->user_id=$pass_data->user_info_id;
		$vars->user_firstname = $pass_data->name;
		$vars->user_email = $pass_data->email;
		$vars->phone =!empty($pass_data->phone)?$pass_data->phone: '';
		$vars->item_name = "Test Techjoomla Product";  //  order prod name
		$vars->payment_description = JText::_('COM_EWALLET_ORDER_PAYMENT_DESC');
		
		// URL SPECIFICATIONS
		$vars->submiturl = JRoute::_("index.php?option=com_tjcpg&controller=payment&task=confirmpayment&processor={$pg_plugin}");
		$vars->return = JURI::root().substr(JRoute::_("index.php?option=com_tjcpg&view=orders&layout=order&orderid=".($orderid)."&processor={$pg_plugin}&Itemid=".$orderItemid,false),strlen(JURI::base(true))+1);
		
		$vars->cancel_return = JURI::root().substr(JRoute::_("index.php?option=com_tjcpg&view=orders&layout=cancel&processor={$pg_plugin}&Itemid=".$orderItemid,false),strlen(JURI::base(true))+1);
		$vars->url=$vars->notify_url=JRoute::_(JURI::root()."index.php?option=com_tjcpg&controller=payment&task=processpayment&order_id=".($orderid)."&processor=".$pg_plugin,false);
		$vars->currency_code = $pass_data->currency;
		$vars->comment = $pass_data->customer_note;
		$vars->amount = $pass_data->amount;
		return $vars;
	}
	function storelog($name,$data)
	{
    $data1=array();
    $data1['raw_data']=$data['raw_data'];
		$data1['JT_CLIENT']="com_tjcpg";
  
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment', $name);
		$data = $dispatcher->trigger('onTP_Storelog', array($data1));
	
	}
	function updateOrder($data)
	{
			$db= JFactory::getDBO();
			$res = new stdClass();
			$eoid=$data['order_id']; // $eoid means extracted order id
			$res->id = $eoid;
			$res->mdate 			= date("Y-m-d H:i:s"); 
			$res->transaction_id 	= $data['transaction_id']; 
			$res->status 	  		= $data['status'];
			$res->processor 		= $data['processor']; 
//			$res->payee_id			= $data['buyer_email'];
			//appending raw data to orders's extra field data
			$tjcpgHelper = new tjcpgHelper;
			$res->extra = $tjcpgHelper->appendExtraFieldData($data['raw_data'],$eoid);
			if(!$db->updateObject( '#__tjcpg_orders', $res, 'id' )) 
			{
				//return false;
			}
	}

}
