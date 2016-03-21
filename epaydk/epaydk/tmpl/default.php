<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access'); 
/*
$vars->item_name=JText::sprintf('PLG_PAYMENT_PAYFAST_PINFO',$vars->order_id); 
$txnid=$vars->order_id; */
//$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

// MUST replace &amp; &
/*
$return_url=str_replace('&amp;','&',$vars->return);
$cancel_url=str_replace('&amp;','&',$vars->cancel_return);
$notify_url=str_replace('&amp;','&',$vars->notify_url);*/

?>
<?php defined('_JEXEC') or die(); ?>

<p align="center">
<form action="<?php echo $data->actionURL ?>"  method="post" id="paymentForm">
	
	<input type="hidden" name="merchantnumber" value="<?php echo $data->merchant ?>" />
	<input type="hidden" name="accepturl" value="<?php echo $data->success ?>" />
	<input type="hidden" name="cancelurl" value="<?php echo $data->cancel ?>" />
	<input type="hidden" name="callbackurl" value="<?php echo $data->postback ?>" />
	<input type="hidden" name="orderid" value="<?php echo $data->orderid ?>" />
	
	<?php /** @see http://tech.epay.dk/Currency-codes_60.html */ ?>
	<input type="hidden" name="currency" value="<?php echo $data->currency ?>" />
	<input type="hidden" name="amount" value="<?php echo $data->amount ?>" />
	
	
	<?php /** @see http://tech.epay.dk/Specification_85.html#paymenttype */ ?>
	<input type="hidden" name="paymenttype" value="<?php echo $data->cardtypes ?>" />
	<input type="hidden" name="instantcapture" value="<?php echo $data->instantcapture ?>" />
	<input type="hidden" name="instantcallback" value="<?php echo $data->instantcallback ?>" />
	
	<input type="hidden" name="language" value="<?php echo $data->language ?>" />
	<input type="hidden" name="ordertext" value="<?php echo $data->ordertext ?>" />
	
	<input type="hidden" name="windowstate" value="<?php echo $data->windowstate ?>" />
	<input type="hidden" name="ownreceipt" value="<?php echo $data->ownreceipt ?>" />
	<input type="hidden" name="hash" value="<?php echo $data->md5 ?>" />
	<!--  -->
	<input type="submit" id="payment-button" class="btn" value="<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_PAYBUTTON') ?>" />
</form>
</p>
