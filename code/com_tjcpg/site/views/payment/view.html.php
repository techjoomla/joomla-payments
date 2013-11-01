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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class TjcpgViewPayment extends JViewLegacy {

    /**
     * Display the view
     */
    public function display($tpl = null) {
			$jinput=JFactory::getApplication()->input;
      $layout=$jinput->get("layout",'default');
      
			$user		= JFactory::getUser();
			$params = JComponentHelper::getParams('com_tjcpg');
		//	print"<pre>" ; print_r($params); 
			
			if($layout=="default")
			{
				//START :: getting payment gateway data
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('payment'); 
				if(!is_array($params->get( 'gateways' )) ){
					$gateway_param[] = $params->get( 'gateways' );
				}
				else{
					$gateway_param = $params->get( 'gateways' ); 	
				}
				
				if(!empty($gateway_param))
					$gateways = $dispatcher->trigger('onTP_GetInfo',array($gateway_param));
					
				$this->gateways = $gateways;
				
				//START :: getting payment gateway data
			}
			else
			{
				// getting order id
				$order_id=$jinput->get("order_id",'');
				
				if(!empty($order_id))
				{
					$model= $this->getModel('payment');
					
					// GETTING ORDER INFO
					$orderinfo=$model->getOrderInfo($order_id);
					$this->processor=$orderinfo->processor;
					
					// GETTING USER PAYMENT HTML
					$this->payhtml = $model->getHTML($orderinfo->processor,$order_id);
					
				}
			}
			
			parent::display($tpl);

    }

    
}
