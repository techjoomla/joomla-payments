<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$plgPaymentGtpayHelper=new plgPaymentGtpayHelper();
$vars->currency_val = $plgPaymentGtpayHelper->currencyConvert();
$gtpay_tranx_id = rand();
$gtpay_cust_id = rand();
?>
<form name="gtpay_test" action="<?php echo $vars->action_url; ?>" method="post">
<div class="row-fluid form-horizontal">
	<input id="gtpay_mert_id" type="hidden" name="gtpay_mert_id" value="<?php echo $this->params->get( 'gtpay_mert_id' ); ?>">
	<input id="gtpay_tranx_id" type="hidden" name="gtpay_tranx_id" value="<?php echo $gtpay_tranx_id; ?>" >
	<input id="gtpay_tranx_amt" type="hidden" name="gtpay_tranx_amt" value="<?php echo $vars->amount * 100; ?>" >
	<input id="gtpay_tranx_curr" type="hidden" name="gtpay_tranx_curr" value="<?php echo $vars->currency_val; ?>">
	<input id="gtpay_cust_id" type="hidden" name="gtpay_cust_id" value="<?php echo $gtpay_cust_id; ?>">
	<input id="gtpay_cust_name" type="hidden" name="gtpay_cust_name" value="test">
	<input id="gtpay_tranx_memo" type="hidden" name="gtpay_tranx_memo" value="">
	<input id="gtpay_tranx_noti_url" type="hidden" name="gtpay_tranx_noti_url" value="<?php echo $this->params->get( 'gtpay_tranx_noti_url' ); ?>"></div>
	<div class="control-group">
		<div class="controls"><input type="submit" name="btnContinue" value="<?php echo Text::_('GTPAY_CONTINUE'); ?>"></div>
	</div>	
</div>					
<?php $shahash = $plgPaymentGtpayHelper->generateHash($gtpay_tranx_id, $vars->amount); ?>
<input type="hidden" name="gtpay_tranx_hash" value="<?php echo $shahash; ?>" />
</form>
