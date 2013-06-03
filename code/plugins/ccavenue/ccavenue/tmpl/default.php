<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access'); 

?>

			<form name="frmccavenue" id="frmccavenue" method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp">			
				<input type="hidden" name="Merchant_Id" value="<?php echo $vars->merchant_id; ?>" />
				<input type="hidden" name="Amount" value="<?php echo (float)$vars->amount; ?>" />
				<input type="hidden" name="Order_Id" value="<?php echo $vars->order_id; ?>" />				
				<input type="hidden" name="Redirect_Url" value="<?php echo $vars->notify_url; ?>" />
				<input type="hidden" name="Checksum" value="<?php echo $vars->checksumval; ?>" />
				<input type="hidden" name="billing_cust_name" value="<?php echo $vars->user_firstname; ?>" />
				<input type="hidden" name="billing_cust_email" value="<?php echo $vars->user_email; ?>" />						
				<input type="hidden" name="billing_cust_country" value="">
				<input type="hidden" name="billing_cust_state" value="">
				<input type="hidden" name="billing_cust_city" value="">
				<input type="hidden" name="billing_zip" value=""> 
				<input type="hidden" name="billing_cust_tel" value="<?php if(isset($vars->phone) ) echo $vars->phone; ?>">
				<input type="hidden" name="delivery_cust_name" value=""> 
				<input type="hidden" name="delivery_cust_address" value="">
				<input type="hidden" name="delivery_cust_country" value="">
				<input type="hidden" name="delivery_cust_state" value="">
				<input type="hidden" name="delivery_cust_tel" value="">
				<input type="hidden" name="delivery_cust_notes" value="">
				<input type="hidden" name="Merchant_Param" value="">
				<input type="hidden" name="billing_zip_code" value="">
				<input type="hidden" name="delivery_cust_city" value="">
				<input type="hidden" name="delivery_zip_code" value="">
				<input type="submit" class="btn btn-success btn-large" border="0"  value="<?php echo JText::_('SUBMIT'); ?>" alt="CCAvenue Pay" />
			
		</form>

