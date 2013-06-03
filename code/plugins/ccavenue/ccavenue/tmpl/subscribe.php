<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');  
//print_r($vars);?>

<table class="userlist">
	<tbody>
	<tr>
		<td class="title">
<form action="<?php echo $vars->action_url ?>" method="post">
				  <input type="hidden" name="cmd" value="_xclick-subscriptions">
				  <input type="hidden" name="business" value="shanta_1271224505_biz@rediffmail.com">
				  <input type="hidden" name="item_name" value="<?php echo $vars->title ;?>">
				  <input type="hidden" name="item_number" value="123">
				  <input type="hidden" name="image_url"
				value="https://www.yoursite.com/logo.gif">
				  <input type="hidden" name="no_shipping" value="1">
				  <input type="hidden" name="return"
				value="http://192.168.1.200/~shantanu/campus/">
				  <input type="hidden" name="cancel_return"
				value="http://192.168.1.200/~shantanu/campus/">
				 
				  <input type="hidden" name="a3" value="<?php echo $vars->fee_value ; ?>">
				  <input type="hidden" name="p3" value="1">
				  <input type="hidden" name="t3" value="M">
				  <input type="hidden" name="src" value="1">
				  <input type="hidden" name="sra" value="0">
				  <input type="hidden" name="srt" value="<?php echo $vars->srt; ?>">
				  <input type="hidden" name="no_note" value="1">
				  <input type="hidden" name="custom" value="customcode">
				  <input type="hidden" name="invoice" value="invoicenumber">
				  <input type="hidden" name="usr_manage" value="1">
				  <input type="image"
					 src="http://images.paypal.com/images/x-click-but01.gif"
				border="0" name="submit"
				alt="Make payments with PayPal - it’s fast, free and secure!">
</form>
	   </td>
   </tr>
   </tbody>
</table>
