<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');
$session 	= JFactory::getSession();
$document = JFactory::getDocument();
JHTML::_('behavior.formvalidation');

// for billing info
$userInfo = array();
$plg_billStyle = "block";
$plg_billStyleMsg = JText::_('PLG_AUTHONET_HIDE_BILL_INFO');
$wholeAddress = '';
if(!empty($vars->userInfo))
{
	$plg_billStyle="none";
	$userInfo = $vars->userInfo;
	$plg_billStyleMsg = JText::_('PLG_AUTHONET_SHOW_BILL_INFO');
	$wholeAddress = $userInfo['add_line1'] . ' ' . $userInfo['add_line2'];
	$wholeAddress = trim($wholeAddress);
}

?>
<script type="text/javascript">
function myValidate(f)
{
	if (document.formvalidator.isValid(f)) {
		f.check.value='<?php echo JSession::getFormToken(); ?>';
		return true;
	}
	else {
		var msg = 'Some values are not acceptable.  Please retry.';
		alert(msg);
	}
	return false;
}


function plg_auth_showHide()
{
	// Get the DOM reference
	var billEle = document.getElementById("tj_payGway_billInfo");
	// Toggle
	var eleStatus = billEle.style.display == "block" ? 'block':'none';// billEle.style.display = "none" :billEle.style.display = "block";
	if(eleStatus == "block")
	{
		billEle.style.display = "none";

		var showBillMsg = "<?php echo JText::_('PLG_AUTHONET_SHOW_BILL_INFO');?>";
		document.getElementById('tj_payGway_billMsg').innerHTML = showBillMsg;

	}
	else
	{
		// if not visible then show
		billEle.style.display = "block";

		var hideBillMsg = "<?php echo JText::_('PLG_AUTHONET_HIDE_BILL_INFO');?>";
		document.getElementById('tj_payGway_billMsg').innerHTML = hideBillMsg;

	}
}

</script>


<div class="akeeba-bootstrap">
<form action="<?php echo $vars->url ?>" name="adminForm" id="adminForm" method="post"	class="form-validate  form-horizontal" onSubmit="return myValidate(this);">
	<div>

		<div class="control-group">
			<label for="cardfname" class="control-label"><?php echo JText::_('Credit Card Type'); ?></label>
			<div class="controls">	<?php
						$types = array();
						$types[] = JHTML::_('select.option', 'Visa', JText::_( "VISA" ) );
						$types[] = JHTML::_('select.option', 'Mastercard', JText::_( "MASTERCARD" ) );
						$types[] = JHTML::_('select.option', 'AmericanExpress', JText::_( "AMERICAN_EXPRESS" ) );
						$types[] = JHTML::_('select.option', 'Discover', JText::_( "DISCOVER" ) );
						$types[] = JHTML::_('select.option', 'DinersClub', JText::_( "DINERS_CLUB" ) );
						$types[] = JHTML::_('select.option', 'JCB', JText::_( "JCB" ) );

						$return = JHTML::_('select.genericlist', $types,'activated',null, 'value','text', 0);
						echo $return;
				?></div>
		</div>

		<div class="control-group">
			<label for="creditcard_name" class="control-label"><?php echo JText::_('Name On Card'); ?></label>
			<div class="controls">	<input type="text" name="creditcard_name" id="creditcard_name" size="25" class="inputbox required" value="" />
			</div>
		</div>

		<div class="control-group">
			<label for="creditcard_number" class="control-label"><?php echo JText::_('Credit Card Number'); ?></label>
			<div class="controls"><input type="text" name="creditcard_number" id="creditcard_number" maxlength="16" size="25" class="inputbox required" value="" />
			</div>
		</div>

		<div class="control-group">
			<label for="creditcard_code" class="control-label"><?php echo	JText::_('Credit Card Security Code '); ?></label>
			<div class="controls"><input type="text" name="creditcard_code" id="creditcard_code" size="25" class="inputbox required" value="" />
			</div>
		</div>

		<div class="control-group">
			<label for="expire_month" class="control-label"><?php echo JText::_('Expiration Date');?></label>
			<div class="controls"><?php
					$all=array();
					$all[0]=new stdClass;

					$all[0]->value = '0';
					$all[0]->text = 'Months';
					for($i=1; $i<13; $i++) {
						$timestamp = mktime(0,0,0,$i+1, 0, date("Y"));
						$months[$i]=new stdClass;
						$months[$i]->value = $i;
						$months[$i]->text = date("M", $timestamp);
					}
					$months = array_merge($all, $months);
					echo JHTML::_('select.genericlist',$months, 'expire_month', 'class="inputbox required" ', 'value', 'text', date('m'));
					echo JHTML::_('select.integerlist',date('Y'), 2030, 1, 'expire_year', 'size="1" class="inputbox required" ');
				?>
			</div>
		</div>
		<hr>

		<div class="control-group">

			<div class="alert alert-success " id="">
				 <span  onClick="plg_auth_showHide()"><strong>
					<a id='tj_payGway_billMsg'><?php echo $plg_billStyleMsg ; ?></a></strong>
				 </span>
			</div>

		</div>


		<div id="tj_payGway_billInfo" style="display:<?php echo $plg_billStyle; ?>">
			<div class="control-group">
				<label for="address" class="control-label"><?php echo JText::_('Address');?></label>
				<div class="controls"><input type="text" name="address" id="address" class="inputbox required" value="<?php echo $wholeAddress; ?>" />
				</div>
			</div>

			<div class="control-group">
				<label for="address" class="control-label"><?php echo JText::_('City');?></label>
				<div class="controls"><input type="text" name="city" id="city" class="inputbox required" value="<?php echo !empty($userInfo['city']) ?$userInfo['city']:'' ;?>" />
				</div>
			</div>

			<div class="control-group">
				<label for="state" class="control-label"><?php echo JText::_('State');?></label>
				<div class="controls"><input type="text" name="state" id="state" class="inputbox required" value="<?php echo !empty($userInfo['state_code']) ?$userInfo['state_code']:'' ;?>" />
				</div>
			</div>
			<div class="control-group">
				<label for="state" class="control-label"><?php echo JText::_('Zip');?></label>
				<div class="controls"><input type="text" name="zip" id="zip" class="inputbox required" value="<?php echo !empty($userInfo['zipcode']) ?$userInfo['zipcode']:'' ;?>" />
				</div>
			</div>
		</div>
<div class="form-actions">
	<!--<button type="button" name="submit" class="inputbox" onclick="submitbutton('ConfirmPayment');"><?php echo JText::_('Make Payment') ?></button>	-->
	<input type="submit" name="submit"  value="<?php echo JText::_('SUBMIT');?>" class="btn btn-success btn-large"/>
	<input type="hidden" name="oid" value="<?php echo $vars->order_id;?>" />
	<input type="hidden" name="check" value="" />
	<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
	<input type="hidden" name="return" size="10" value="<?php echo $vars->return;?>" />
	<input type="hidden" name="chargetotal" value="<?php echo $vars->amount;?>" />
	<input type="hidden" name="plugin_payment_method" value="onsite" />
</div>
</div>
</form>
</div>
