<?php 
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access'); 

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
?>
<div class="akeeba-bootstrap">
<form action="<?php echo $vars->action_url ?>" class="form-horizontal" method="post">			
	<input type="hidden" name="key" value="<?php echo $vars->key ?>" />
	<input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
	<input type="hidden" name="udf1" value="<?php echo $vars->order_id ?>" />				
	<input type="hidden" name="productinfo" value="<?php echo $vars->item_name ?>" />
	<input type="hidden" name="Firstname" value="<?php if($vars->user_firstname) echo $vars->user_firstname; ?>" />
	<input type="hidden" name="Email" value="<?php echo $vars->user_email; ?>" />						
	<input type="hidden" name="phone" value="<?php echo $vars->phone; ?>" />
	<input type="hidden" name="curl" value="<?php echo $vars->cancel_return ?>" />
	<input type="hidden" name="surl" value="<?php echo $vars->notify_url ?>" />				
	<input type="hidden" name="furl" value="<?php echo $vars->notify_url ?>" />
	<input type="hidden" name="amount" value="<?php echo round($vars->amount); ?>" />

<?php $text = trim($vars->key).'|'.trim($txnid).'|'.trim(round($vars->amount)).'|'.trim($vars->item_name).'|'.trim($vars->user_firstname).'|'.trim($vars->user_email).'|'.trim($vars->order_id).'||||||||||'.trim($vars->salt); 
//$text = 'C0Dr8m|00d7be5c0b59cdf0a5d|'.$vars->amount.'|'.$vars->item_name.'|test_Firstname|diptijagadale3@gmail.com|||||||||||3sf0jURk';

//echo hash('sha512',$text);
?>
	<input type="hidden" name="Hash" value="<?php echo strtolower(hash('sha512',$text)); ?>" />
	<div class="form-actions">
		<input type="submit" class="btn btn-success btn-large" border="0" value="Pay Now" alt="PayU India" />	
	</div>		
</form>
</div>
