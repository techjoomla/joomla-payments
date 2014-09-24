<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');
$plgPaymentGtpayHelper=new plgPaymentGtpayHelper();
$vars->currency_val = $plgPaymentGtpayHelper->currencyConvert();
$gtpay_tranx_id = rand();
$gtpay_cust_id = rand();
?>
<form name="gtpay_test" action="<?php echo $vars->action_url; ?>" method="post">
<div class="row-fluid form-horizontal">
			<input class="inputbox required" id="gtpay_mert_id" type="hidden" name="gtpay_mert_id" size="35" value="<?php echo $this->params->get( 'gtpay_mert_id' ); ?>" readonly>
			<input class="inputbox required" id="gtpay_tranx_id" type="hidden" name="gtpay_tranx_id" size="35" value="<?php echo $gtpay_tranx_id; ?>" >
			<input class="inputbox required" id="gtpay_tranx_amt" type="hidden" name="gtpay_tranx_amt" size="35" value="<?php echo $vars->amount * 100; ?>" readonly>
			<input class="inputbox required" id="gtpay_tranx_curr" type="hidden" name="gtpay_tranx_curr" size="35" value="<?php echo $vars->currency_val; ?>" readonly>
			<input class="inputbox required" id="gtpay_cust_id" type="hidden" name="gtpay_cust_id" size="35" value="<?php echo $gtpay_cust_id; ?>" readonly>
			<input class="inputbox required" id="gtpay_cust_name" type="hidden" name="gtpay_cust_name" size="35" value="test">
			<input class="inputbox required" id="gtpay_tranx_memo" type="hidden" name="gtpay_tranx_memo" size="35" value="">
			<input class="inputbox required" id="gtpay_tranx_noti_url" type="hidden" name="gtpay_tranx_noti_url" size="35" value="<?php echo $this->params->get( 'gtpay_tranx_noti_url' ); ?>"></div>
		<div class="control-group">
			<div class="controls"><input type="submit" name="btnContinue" value="<?php echo JText::_('GTPAY_CONTINUE'); ?>"></div>
		</div>	
</div>					
<?php $shahash = $plgPaymentGtpayHelper->generateHash($gtpay_tranx_id, $vars->amount); ?>
<!--<input type="hidden" name="gtpay_cust_name" value="<?php //echo $vars->gtpay_cust_name; ?>" />
<input type="hidden" name="gtpay_tranx_memo" value="<?php //echo $vars->gtpay_tranx_memo; ?>" />
<input type="hidden" name="gtpay_no_show_gtbank" value="<?php //echo $vars->gtpay_no_show_gtbank; ?>" />
<input type="hidden" name="gtpay_echo_data" value="<?php //echo $vars->gtpay_echo_data; ?>" />
<input type="hidden" name="gtpay_gway_name" value="<?php //echo $vars->gtpay_gway_name; ?>" />-->
<input type="hidden" name="gtpay_tranx_hash" value="<?php echo $shahash; ?>" />
</form>	 
