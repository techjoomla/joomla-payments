<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Payments controller class.
 */
class TjcpgControllerPayment extends TjcpgController
{
	function getHTML() {
		$model= $this->getModel( 'payment');
		$jinput=JFactory::getApplication()->input;
		$pg_plugin = $jinput->get('processor');
		$user = JFactory::getUser();
		$session =JFactory::getSession();
		$order_id = $jinput->get('order');
		$html=$model->getHTML($pg_plugin,$order_id);
		if(!empty($html[0]))
		echo $html[0];
		jexit();
	}

	function confirmpayment(){
		$model= $this->getModel( 'payment');
		$session =JFactory::getSession();
		$jinput=JFactory::getApplication()->input;
		$order_id = $session->get('order_id');
		$pg_plugin = $jinput->get('processor');
		$response=$model->confirmpayment($pg_plugin,$order_id);
	}

	/** Payment gateway sends payment response to notify URL.
	 */
	function processpayment()
	{
		$mainframe=JFactory::getApplication();
		$jinput=JFactory::getApplication()->input;
		$session =JFactory::getSession();
		$post = JRequest::get('post');
		
		if($session->has('payment_submitpost')){
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else{
			//$post = JRequest::get('post');
			$rawDataPost = JRequest::get('POST');
			$rawDataGet = JRequest::get('GET');
			$post = array_merge($rawDataGet, $rawDataPost);
		}
		$pg_plugin = $jinput->get('processor');
		$model= $this->getModel('payment');
		$order_id = $jinput->get('order_id','','STRING');

		if(empty($post) || empty($pg_plugin) ){
			JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');
			return;
		}
		$person=json_encode($post);
		$response=$model->processpayment($post,$pg_plugin,$order_id);
		$mainframe->redirect($response['return'],$response['msg']);
	}
	function save()
	{
		$mainframe=JFactory::getApplication();
		$jinput=JFactory::getApplication()->input;

		// GETTING POST DATA
		$post	= JRequest::get('post');
		// GET MODEL
		$model= $this->getModel('payment');
		$order_id=$model->store($post);
		$session = JFactory::getSession();
			//$session->set('final_amt',$data['final_amt_pay_inputbox']);
			$session->set('order_id',$order_id);		
		$Itemid=0;
		$msg='';
		$link=JURI::root().substr(JRoute::_('index.php?option=com_tjcpg&view=payment&layout=pay&order_id='.$order_id.'&Itemid='.$Itemid,false),strlen(JURI::base(true))+1);
		$mainframe->redirect($link,$msg);
	}



}
