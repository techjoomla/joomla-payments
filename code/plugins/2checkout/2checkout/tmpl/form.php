<?php
/**
 * @package     Joomla_Payments
 * @subpackage  plg_payments_2checkout
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="tjcpg-wrapper">
<form action="<?php echo $vars->action_url ?>" class="form-horizontal" method="post" id="paymentForm">
	<input type="hidden" name="sid" value="<?php echo $vars->sid?>" />
	<input type="hidden" name="cart_order_id" value="<?php echo $vars->order_id ?>" />
	<input type="hidden" name="total" value="<?php echo sprintf('%02.2f', $vars->amount) ?>" />
	<input type="hidden" name="demo" value="<?php echo  $vars->demo; ?>" />
	<input type="hidden" name="merchant_order_id" value="<?php echo $vars->order_id ?>" />
	<input type="hidden" name="fixed" value="Y" />
	<input type="hidden" name="lang" value="<?php echo $vars->lang; ?>" />
	<input type='hidden' name='x_receipt_link_url' value="<?php echo $vars->return;?>" >
	<input type="hidden" name="pay_method" value="<?php echo strtoupper($vars->pay_method); ?>" />
	<input type="hidden" name="card_holder_name" value="<?php echo $vars->user_firstname . " " . $vars->user_lastname?>" />
	<input type="hidden" name="email" value="<?php echo $vars->user_email?>" />
	<input type="hidden" name="street_address" value="<?php echo $vars->address?>" />
	<input type="hidden" name="street_address2" value="<?php echo $vars->address2?>" />
	<input type="hidden" name="zip" value="<?php echo $vars->zipcode?>" />
	<input type="hidden" name="phone" value="<?php echo $vars->contactNumber?>" />
	<input type="hidden" name="city" value="<?php echo $vars->cityName?>" />
	<input type="hidden" name="state" value="<?php echo $vars->stateName?>" />
	<input type="hidden" name="country" value="<?php echo $vars->countryName?>" />
	<input type="hidden" name="id_type" value="1" />
	<div class="form-actions">
		<input name="submit" type="submit" class="btn btn-success btn-large" value="Pay Now" >
	</div>
</form>
</div>
