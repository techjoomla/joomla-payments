<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access'); 

$vars->item_name=JText::sprintf('PAYU_PINFO',$vars->order_id); 
$txnid=$vars->order_id;
//$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
?>
<div class="akeeba-bootstrap">
<form action="<?php echo $vars->action_url ?>" class="form-horizontal" method="post">			
	<input type="hidden" name="key" value="<?php echo trim($vars->key) ?>" />
	<input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
	<input type="hidden" name="udf1" value="<?php echo $vars->order_id ?>" />				
	<input type="hidden" name="productinfo" value="<?php echo trim($vars->item_name) ?>" />
	<input type="hidden" name="Firstname" value="<?php if($vars->user_firstname) echo trim($vars->user_firstname); ?>" />
	<input type="hidden" name="Email" value="<?php echo $vars->user_email; ?>" />						
	<input type="hidden" name="phone" value="<?php echo $vars->phone; ?>" />
	<input type="hidden" name="curl" value="<?php echo $vars->cancel_return ?>" />
	<input type="hidden" name="surl" value="<?php echo $vars->notify_url ?>" />				
	<input type="hidden" name="furl" value="<?php echo $vars->notify_url ?>" />
	<input type="hidden" name="amount" value="<?php echo $vars->amount; ?>" />

<?php $text = trim($vars->key).'|'.$txnid.'|'.$vars->amount.'|'.trim($vars->item_name).'|'.trim($vars->user_firstname).'|'.$vars->user_email.'|'.$vars->order_id.'||||||||||'.trim($vars->salt); 
//$text = 'C0Dr8m|00d7be5c0b59cdf0a5d|'.$vars->amount.'|'.$vars->item_name.'|test_Firstname|diptijagadale3@gmail.com|||||||||||3sf0jURk';
//echo hash('sha512',$text);
?>
	<input type="hidden" name="Hash" value="<?php echo strtolower(hash('sha512',$text)); ?>" />
	<div class="form-actions">
		<input type="submit" class="btn btn-success btn-large" border="0"  value="<?php echo JText::_('PAYU_SUBMIT'); ?>" alt="PayU India" />	
	</div>		
</form>
</div>
