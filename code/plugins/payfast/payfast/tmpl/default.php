<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access'); 

//$vars->item_name=JText::sprintf('PLG_PAYMENT_PAYFAST_PINFO',$vars->order_id); 
$txnid=$vars->order_id;
//$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

// MUST replace &amp; &

$return_url=$vars->return; //str_replace('&amp;','&',$vars->return);
$cancel_url=$vars->cancel_return;
$notify_url= $vars->notify_url;

?>
<div class="akeeba-bootstrap">
<p align="center">
<form action="<?php echo htmlentities($vars->action_url) ?>"  method="post" id="paymentForm">

	<!-- Receiver Details -->
	<input type="hidden" name="merchant_id" value="<?php echo $vars->merchant_id ?>" />
	<input type="hidden" name="merchant_key" value="<?php echo $vars->merchant_key ?>" />
	<input type="hidden" name="return_url" value="<?php echo $return_url ?>" />
	<input type="hidden" name="cancel_url" value="<?php echo $cancel_url ?>" />
	<input type="hidden" name="notify_url" value="<?php echo $notify_url ?>" />
	
	
	<!-- Payer Details -->
	<input type="hidden" name="name_first" value="<?php if($vars->user_firstname) echo trim($vars->user_firstname); ?>" />
	<input type="hidden" name="name_last" value="<?php if($vars->user_firstname) echo trim($vars->user_firstname); ?>" />
	<input type="hidden" name="email_address" value="<?php echo $vars->user_email ?>" />	
	
	<!-- Transaction Details -->
	<input type="hidden" name="amount" value="<?php echo $vars->amount; ?>" />
	<input type="hidden" name="item_name" value="<?php echo trim($vars->item_name) ?>" />
	<input type="hidden" name="item_description" value="<?php echo trim($vars->item_name) ?>" />
	<input type="hidden" name="custom_str1" value="<?php echo $txnid ?>" /> 
	<?php
	//1. CREATING SIGNATURE  STRING 
		 $sigString="merchant_id=".urlencode($vars->merchant_id).		"&merchant_key=".urlencode($vars->merchant_key).
						"&return_url=".urlencode($return_url).					"&cancel_url=".urlencode($cancel_url).
						"&notify_url=".urlencode($notify_url).			"&name_first=".urlencode($vars->user_firstname).
						"&name_last=".urlencode($vars->user_firstname).	"&email_address=".urlencode($vars->user_email).
						"&amount=".urlencode($vars->amount).							"&item_name=".urlencode($vars->item_name).
						"&item_description=".urlencode($vars->item_name)."&custom_str1=".urlencode($txnid);

	//2.SIGNATURE :: MD5 generation(Encoded):
	$vars->signature = md5($sigString);
	 // DOC LINK =https://www.payfast.co.za/help/integration
	?>
	<input type="hidden" name="signature" value="<?php echo $vars->signature ?>" />
	<input type="submit" class="btn" />
</form>
</p>
</div>
