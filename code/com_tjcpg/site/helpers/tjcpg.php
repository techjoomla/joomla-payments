<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */

defined('_JEXEC') or die;

class tjcpgHelper
{
	public static function myFunction()
	{
		$result = 'Something';
		return $result;
	}
		// pass the link for which you want the ItemId.
	function getitemid($link)
	{
		$itemid = 0;
		
		global $mainframe;
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin()){
			$db=JFactory::getDBO();
			$query = "SELECT id FROM #__menu WHERE link LIKE '%".$link."%' AND published = 1 LIMIT 1";
			$db->setQuery($query);
			$itemid = $db->loadResult();
		}
		else{
			// getting MENU  Itemid
			$menu = $mainframe->getMenu();
			$items= $menu->getItems('link',$link);  
			if(isset($items[0])){
				$itemid = $items[0]->id;
			}
			
			//IF NO MENU FOR LINK THEN FETCH FROM db
			if(empty($itemid))
			{
				$db=JFactory::getDBO();
				$query = "SELECT id FROM #__menu WHERE link LIKE '%".$link."%' AND published = 1 LIMIT 1";
				$db->setQuery($query);
				$itemid = $db->loadResult();
			}
		}
		// if Itemid is empty then get from request and return it
		if(!$itemid)
			{
				$jinput=JFactory::getApplication()->input;
				$itemid = $jinput->get('Itemid');	
			}
		return $itemid;
	}
	
	/**
	THIS function take orderid and array of data to be store in extra field of order table
	@data array :: data to be store in extra field
	@order_id INTERGER :: order id
	@return json string  :: json_encoded extra field data 
	
	*/
	function appendExtraFieldData($data,$order_id,$curr_exchange_msg=0)
	{
		$db = JFactory::getDBO();
		$q="SELECT  `extra`FROM  `#__kart_orders` WHERE `id` =".$order_id;
		$db->setQuery($q);
		$oldres = $db->loadResult();
		if(empty($oldres))
		{
			if($curr_exchange_msg==1)
			{
				// called from currecy exchange function
				$exchange_msg['currency_exchange']=$data;
			}
			elseif($curr_exchange_msg==0)
			{  // mean we are going to save payment response msg
				$exchange_msg['payment_response']=$data;
			}
				
			
			return json_encode($exchange_msg);
		}
		else
		{
			// Take already exist extra data
			$olddata=json_decode($oldres);
			//ADD or UPDATE  currency_exchange data
			if($curr_exchange_msg==1)
			{
				// called from currecy exchange function
				$olddata->currency_exchange=$data;
			}
			elseif($curr_exchange_msg==0)
			{  // mean we are going to save payment response msg
				$olddata->payment_response=$data;
			}
			return json_encode($olddata);
		}
	}// end of appendExtraFieldData

		/*
	 * Function to update status of order
	 * 
	 	   Parameters:
	 	   order_id : int id of order
	 	   status : string status of order
	 	   comment : string default='' comment added if any
	 	   $send_mail : int default=1 weather to send status change mail or not.
	 	   @param $store_id :: INTEGER (1/0) if we are updating store product status
	*/	
	function updatestatus($order_id,$status,$comment='',$send_mail=1,$store_id=0){
		global $mainframe;
		$params = JComponentHelper::getParams( 'com_quick2cart' );
		$comquick2cartHelper = new comquick2cartHelper();
		switch($status)
			{
				case 'C' :
					/// to reduce stock
					$usestock = $params->get( 'usestock' );
					$outofstock_allowship = $params->get( 'outofstock_allowship' );
					
					if($usestock==1)//$outofstock_allowship==1)  
					{
						$comquick2cartHelper->updateItemStock($order_id);
					}		
					$comquick2cartHelper->updateStoreFee($order_id);
			}
			$mainframe = JFactory::getApplication();
			$db= JFactory::getDBO();
			if($send_mail == 1)
			{
				if(!empty($store_id)) // for changing store product order
				{
					$query = 'SELECT o.status FROM `#__kart_order_item` as o WHERE o.order_id ='.$order_id.' AND o.`store_id`='.$store_id.' order by `order_item_id`';
					//die(" work is in progress(store product status change) die in helper ");
				}
				else
					$query = "SELECT o.status FROM #__kart_orders as o WHERE o.id =".$order_id;
				$db->setQuery($query);
				$order_oldstatus = $db->loadResult();
			}
			$res = new stdClass();	
			// UPDATING STORE ORDER CHANGES
			if(!empty($store_id))
			{
				// change ORDER_ITEM STATUS// here i want order_item_id to update status of all order item releated to store
				$isOrderStatusChanged=$comquick2cartHelper->updateOrderItemStatus($order_id,$store_id,$status);
				if(empty($isOrderStatusChanged))  // 1 for order status change, 0 for order item change 
				{
				//	return ;
				}
			}
			else
			{
				// IF admin changes ORDER status
				$res->status=$status;
				$res->id 		= $order_id;
				if(!$db->updateObject( '#__kart_orders', $res, 'id' )) 
				{
					return 2;
				}
				$isOrderStatusChanged=$comquick2cartHelper->updateOrderItemStatus($order_id,0,$status);
				// UPDATE ORDER ITEM STATUS ALSO
			}
			//START Q2C Sample development
			$query = "SELECT o.* FROM #__kart_orders as o WHERE o.id =".$order_id;
			$db->setQuery($query);
 			$orderobj	= $db->loadObject();
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$result=$dispatcher->trigger('Onq2cOrderUpdate',array($orderobj));//Call the plugin and get the result
			//END Q2C Sample development
		if($send_mail == 1 && $order_oldstatus != $status)
		{
			$params = JComponentHelper::getParams( 'com_quick2cart' );
			//$adminemails = comquick2cartHelper::adminMails();
			$query = "SELECT ou.user_id,ou.user_email,ou.firstname FROM #__kart_users as ou WHERE ou.address_type='BT' AND ou.order_id = ".$order_id;
			$db->setQuery($query);
 			$orderuser	= $db->loadObjectList();
			//Change for backward compatiblity for user info not saving order id against it
			if(empty($orderuser)){
				$query = "SELECT ou.user_id,ou.user_email,ou.firstname
				FROM #__kart_users as ou 
				WHERE ou.address_type='BT' AND ou.order_id IS NULL AND ou.user_id = (SELECT o.user_info_id FROM #__kart_orders as o WHERE o.id =".$order_id.")";
				$db->setQuery($query);
				$orderuser	= $db->loadObjectList();
			}
			$orderuser = $orderuser[0];
			switch($status)
			{
				case 'C' :
					$orderstatus =  JText::_('QTC_CONFR');
				/*for invoice*/
					$jinput=JFactory::getApplication()->input;
					$jinput->set( 'orderid',$order_id);
					$order=$order_bk = $comquick2cartHelper->getorderinfo($order_id);	
					$this->orderinfo = $order['order_info'];
					$this->orderitems = $order['items'];
					$this->orders_site=1;
					$this->orders_email=1;
					$this->order_authorized=1;
					if($this->orderinfo[0]->address_type == 'BT')
						$billemail = $this->orderinfo[0]->user_email;
					else if($this->orderinfo[1]->address_type == 'BT')
						$billemail = $this->orderinfo[1]->user_email;
					$fullorder_id = $order['order_info'][0]->prefix.$order_id;
					if(!JFactory::getUser()->id && $params->get( 'guest' ) ){
						$jinput->set( 'email',md5($billemail) );
					}
					
					// check for view override
					 $view=$comquick2cartHelper->getViewpath('orders','invoice');
					ob_start();
						include($view);
						$invoicehtml = ob_get_contents();
					ob_end_clean();
				/*for invoice*/
				break;
				case 'RF' :
					$orderstatus = JText::_('QTC_REFUN') ;
				break;
				case 'S' :
					$orderstatus = JText::_('QTC_SHIP') ;
				break;
				case 'E' :
					$orderstatus = JText::_('QTC_ERR') ;
				break;
				case 'P' :
					$orderstatus = JText::_('QTC_PENDIN') ;
				break;
				default:
					$orderstatus = $status;
				break;
			}

			$fullorder_id = $orderobj->prefix.$order_id;
			if(!empty($store_id))
			{
				$productStatus=$comquick2cartHelper->getProductStatus($order_id);
				$body =JText::sprintf('QTC_STORE_PRODUCT_STATUS_CHANGE_BODY',$productStatus); 
			}
			else {
				$body = JText::_('QTC_STATUS_CHANGE_BODY');
			}
			$site = $mainframe->getCfg('sitename');
			if($comment)
			{
			$comment	= str_replace('{COMMENT}', $comment, JText::_('QTC_COMMENT_TEXT'));
			$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
			$replace= array($fullorder_id,$orderstatus,$site,$orderuser->firstname,$comment);
			}
			else
			{			
			$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
			$replace= array($fullorder_id,$orderstatus,$site,$orderuser->firstname,'');
			}
			
			$body	= str_replace($find, $replace, $body);
			$guest_email = '';
			if(!$orderuser->user_id && $params->get( 'guest' )){
				$guest_email = "&email=".md5($orderuser->user_email);
			}
			
			$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders');
			$link = JURI::root().substr(JRoute::_('index.php?option=com_quick2cart&view=orders&layout=order'.$guest_email.'&orderid='.$order_id.'&Itemid='.$Itemid),strlen(JURI::base(true))+1);
			$order_link = '<a href="'.$link.'">'.JText::_('QTC_ORDER_GUEST_LINK').'</a>';
			$body	= str_replace('{LINK}', $order_link, $body);
			$body = nl2br($body);
			if(!empty($invoicehtml )){
				$body = $body.'<div>'.JText::_('QTC_ORDER_INVOICE_IN_MAIL').'</div>';
				$invoicehtml= $body.$invoicehtml;
				$invoicesubject = JText::sprintf('QTC_INVOICE_MAIL_SUB',$site,$fullorder_id);
				$comquick2cartHelper->sendmail($orderuser->user_email,$invoicesubject,$invoicehtml,$params->get( 'sale_mail' ));
			}else{
				$subject = JText::sprintf('QTC_STATUS_CHANGE_SUBJECT',$fullorder_id);
				$comquick2cartHelper->sendmail($orderuser->user_email,$subject,$body,$params->get( 'sale_mail' ));
			}
		}			
			 			
	}	// END OF updatestatus
	
	function getOrderInfo($order_id)
	{
		$db = JFactory::getDBO();
		$query="SELECT * FROM `#__tjcpg_orders` WHERE `id`=".$order_id;
		$db->setQuery($query);
		return $order_result = $db->loadObject();
	}
}

