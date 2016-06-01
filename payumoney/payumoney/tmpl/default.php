<?php
/**
 * @copyright  Copyright (c) 2009 - 2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

$vars->item_name = JText::sprintf('PAYUMONEY_PINFO', $vars->order_id);
$txnid = $vars->order_id;

$posted = array();
$posted['key'] = trim($vars->key);
$posted['txnid'] = $txnid;
$posted['amount'] = $vars->amount;
$posted['productinfo'] = trim($vars->item_name);
$posted['firstname'] = isset($vars->user_firstname) ? trim($vars->user_firstname) : 'First_name';
$posted['email'] = $vars->user_email;
$posted['phone'] = $vars->phone;
$posted['curl'] = $vars->cancel_return;
$posted['surl'] = $vars->notify_url;
$posted['furl'] = $vars->notify_url;

$posted['udf1'] = $vars->order_id;
$posted['udf2'] = isset($vars->udf2) ? trim($vars->udf2) : '';
$posted['udf3'] = isset($vars->udf3) ? trim($vars->udf3) : '';
$posted['udf4'] = isset($vars->udf4) ? trim($vars->udf4) : '';

$posted['udf5'] = isset($vars->udf5) ? trim($vars->udf5) : '';
$posted['udf6'] = isset($vars->udf6) ? trim($vars->udf6) : '';
$posted['udf7'] = isset($vars->udf7) ? trim($vars->udf7) : '';
$posted['udf8'] = isset($vars->udf8) ? trim($vars->udf8) : '';
$posted['udf9'] = isset($vars->udf9) ? trim($vars->udf9) : '';
$posted['udf10'] = isset($vars->udf10) ? trim($vars->udf10) : '';

$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
$hashVarsSeq = explode('|', $hashSequence);
$hash_string = '';

foreach ($hashVarsSeq as $hash_var)
{
	$hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
	$hash_string .= '|';
}

$hash_string .= $vars->salt;
$hash = strtolower(hash('sha512', $hash_string));

?>
<div class="tjcpg-wrapper">
<form action="<?php echo $vars->action_url ?>" class="form-horizontal" method="post">

	<!-- Mandatory Parameters -->
	<input type="hidden" name="key" value="<?php echo trim($posted['key']); ?>" />
	<input type="hidden" name="txnid" value="<?php echo $posted['txnid']; ?>" />
	<input type="hidden" name="amount" value="<?php echo $posted['amount']; ?>" />
	<input type="hidden" name="productinfo" value="<?php echo $posted['productinfo']; ?>" />
	<input type="hidden" name="Firstname" value="<?php echo $posted['firstname']; ?>" />
	<input type="hidden" name="Email" value="<?php echo $posted['email']; ?>" />
	<input type="hidden" name="phone" value="<?php echo $posted['phone']; ?>" />
	<input type="hidden" name="surl" value="<?php echo $posted['surl']; ?>" />
	<input type="hidden" name="furl" value="<?php echo $posted['furl']; ?>" />

	<!-- Optional Parameters -->
	<input type="hidden" name="curl" value="<?php echo $posted['curl']; ?>" />

	<!-- Mandatory Parameters -->
	<input type="hidden" name="udf1" value="<?php echo $posted['udf1']; ?>" />

	<!-- Optional Parameters -->
	<input type="hidden" name="udf2" value="<?php echo $posted['udf2']; ?>" />
	<input type="hidden" name="udf3" value="<?php echo $posted['udf3']; ?>" />
	<input type="hidden" name="udf4" value="<?php echo $posted['udf4']; ?>" />
	<input type="hidden" name="udf5" value="<?php echo $posted['udf5']; ?>" />
	<input type="hidden" name="udf6" value="<?php echo $posted['udf6']; ?>" />
	<input type="hidden" name="udf7" value="<?php echo $posted['udf7']; ?>" />
	<input type="hidden" name="udf8" value="<?php echo $posted['udf8']; ?>" />
	<input type="hidden" name="udf9" value="<?php echo $posted['udf9']; ?>" />
	<input type="hidden" name="udf10" value="<?php echo $posted['udf10']; ?>" />
	<input type="hidden" name="service_provider" value="payu_paisa" size="64" />

	<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
	<div class="form-actions">
		<input type="submit" class="btn btn-success btn-large" border="0"  value="<?php echo JText::_('PAYUMONEY_SUBMIT'); ?>" alt="PayU India" />
	</div>
</form>
</div>
