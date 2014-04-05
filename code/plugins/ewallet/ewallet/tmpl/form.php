<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

 ?>
<script type="text/javascript">
function calculate(convert_val,count,user_points,not_enough_pts_message,success_message)
{
	var charge_points = convert_val * count;
	var newRow="";
	var flag=0;
	if(user_points==0 || user_points < charge_points)
	{
		newRow=not_enough_pts_message;
		alert(newRow);
	}
	else if(user_points >= charge_points)
	{
		flag=1;
	}

	if(flag==1)
	{
		alert(success_message);
		document.getElementById('js_buy').setAttribute('type', 'submit');
		return true;
	}
	else
	{
		document.getElementById('js_buy').disabled='disabled';
		return false;
	}
}
</script>
<?php 
$comparams = JComponentHelper::getParams( 'com_ewallet' );
$wallet_currency_nam = $comparams->get( "wallet_currency_nam" );
?>
<div class="techjoomla-bootstrap">
	<form action="<?php echo $vars->url ?>" class="form-validate form-horizontal"  method="post" id="paymentForm" name="paymentForm">
		<div>
			<div class="alert alert-info">
				<?php echo JText::_("FULL_WALLET_PAYMENT_MSG_TEXT"); ?>
			</div>
			<div class="controls">
				<?php 
				echo JText::sprintf( 'CONVERSION_RATE_MESSAGE', $vars->convert_val,$wallet_currency_nam, $vars->currency_code);?>
			</div>
			<div class="controls">
				<?php
					$charge_points = $vars->convert_val * $vars->amount;
					$charge_points = number_format($charge_points ,2);
					echo JText::sprintf( 'TOTAL_POINTS_NEEDED_MESSAGE', $charge_points,$wallet_currency_nam);?>
			</div>
			<div class="controls">
				<?php echo JText::sprintf( 'CURRENT_POINTS_SITUATION', $vars->user_points,$wallet_currency_nam);?>
			</div>
			<?php $not_enough_pts_message="'".JText::sprintf('NOT_ENOUGH_POINTS_MESSAGE',$wallet_currency_nam)."'";
				$success_message="'".JText::sprintf( 'TOTAL_POINTS_DEDUCTED_MESSAGE',$charge_points,$wallet_currency_nam)."'";
			?>
			<div class="form-actions">
				<input class="btn btn-success btn-large" id="js_buy" type="button" value="<?php echo JText::_('SUBMIT');?>" onclick="calculate(<?php echo $vars->convert_val;?>,<?php echo $vars->amount;?>,<?php echo $vars->user_points;?>,<?php echo $not_enough_pts_message; ?>,<?php echo $success_message; ?> );">
				<input type="hidden" name="order_id" value="<?php echo $vars->order_id; ?>" />
				<input type="hidden" name="client" value="<?php echo $vars->client; ?>" />
				<input type="hidden" name="total" value="<?php echo number_format($vars->amount ,2) ?>" />
				<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
				<input type='hidden' name='return' value="<?php echo $vars->return;?>" >
				<input type="hidden" name="plugin_payment_method" value="onsite" />
			</div>
		</div>
	</form>
</div>
