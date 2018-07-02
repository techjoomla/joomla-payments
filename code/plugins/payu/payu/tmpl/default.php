<?php
/**
 * @version    SVN: <svn_id>
 * @package    Payu
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

$vars->item_name = JText::sprintf('PLG_PAYMENT_PAYU_PINFO', $vars->order_id);
$txnid=$vars->order_id;

?>
<div class="tjcpg-wrapper">
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
?>
	<input type="hidden" name="Hash" value="<?php echo strtolower(hash('sha512',$text)); ?>" />
	<div class="form-actions">
		<input type="submit" class="btn btn-success btn-large" border="0"  value="<?php echo JText::_('PLG_PAYMENT_PAYU_SUBMIT'); ?>" alt="<?php echo JText::_('PLG_PAYMENT_PAYU_SUBMIT_ALT'); ?>" />
	</div>
</form>
</div>
