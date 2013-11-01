<?php
/**
 *  @package    Quick2Cart
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( ';)' );

jimport( 'joomla.application.component.view');


class TjcpgViewOrders extends JViewLegacy
{

	function display($tpl = null)
	{
			$jinput=JFactory::getApplication()->input;
      $layout=$jinput->get("layout",'order');
      
			//$user		= JFactory::getUser();
			//$params = JComponentHelper::getParams('com_tjcpg');
			if($layout=="order")
			{
				$tjcpgHelper=new tjcpgHelper;
				$order_id=$jinput->get("orderid",'');
				
				if(!empty($order_id))
				{
					$this->orderinfo = $tjcpgHelper->getOrderInfo($order_id);
					
					
				}
				else
				{
					echo JText::_('COM_TJCPG_ILLEGAL_ORDERID');
				}
			}
			
			parent::display($tpl);
		
	}//function display ends here
	
	
	
}// class
