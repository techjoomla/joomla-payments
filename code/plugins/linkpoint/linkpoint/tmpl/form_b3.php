<?php
/**
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$session  = Factory::getSession();
$document = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');

// For billing info
$userInfo         = array();
$plg_billStyle    = "block";
$plg_billStyleMsg = Text::_('PLG_AUTHONET_HIDE_BILL_INFO');
$wholeAddress     = '';

if (!empty($vars->userInfo))
{
	$plg_billStyle    = "none";
	$userInfo         = $vars->userInfo;
	$plg_billStyleMsg = Text::_('PLG_AUTHONET_SHOW_BILL_INFO');
	$wholeAddress     = $userInfo['add_line1'] . ' ' . $userInfo['add_line2'];
	$wholeAddress     = trim($wholeAddress);
}

?>
<script type="text/javascript">
	function myValidate(f)
	{
		if (document.formvalidator.isValid(f))
		{
			f.check.value='<?php echo Session::getFormToken(); ?>';
			return true;
		}
		else
		{
			alert("<?php echo Text::_('PLG_PAYMENT_LINKPOINT_ALERT_MSG'); ?>");
		}
		return false;
	}

	function plg_auth_showHide()
	{
		/**Get the DOM reference*/
		var billEle = document.getElementById("tj_payGway_billInfo");

		/**Toggle*/
		var eleStatus = billEle.style.display == "block" ? 'block':'none';

		/**billEle.style.display = "none" :billEle.style.display = "block";**/
		if (eleStatus == "block")
		{
			billEle.style.display = "none";

			var showBillMsg = "<?php echo Text::_('PLG_AUTHONET_SHOW_BILL_INFO');?>";
			document.getElementById('tj_payGway_billMsg').innerHTML = showBillMsg;
		}
		else
		{
			/** If not visible then show*/
			billEle.style.display = "block";
			var hideBillMsg = "<?php echo Text::_('PLG_AUTHONET_HIDE_BILL_INFO');?>";
			document.getElementById('tj_payGway_billMsg').innerHTML = hideBillMsg;
		}
	}
</script>
<div class="tjcpg-wrapper">
	<form action="<?php echo $vars->url ?>" name="adminForm" id="adminForm" method="post" class="form-validate  form-horizontal"
		onSubmit="return myValidate(this);">
		<div>
			<div class="control-group">
				<label for="cardfname" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
					<?php echo Text::_('Credit Card Type'); ?>
				</label>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<?php
						$types = array();
						$types[] = HTMLHelper::_('select.option', 'Visa', Text::_("VISA"));
						$types[] = HTMLHelper::_('select.option', 'Mastercard', Text::_("MASTERCARD"));
						$types[] = HTMLHelper::_('select.option', 'AmericanExpress', Text::_("AMERICAN_EXPRESS"));
						$types[] = HTMLHelper::_('select.option', 'Discover', Text::_("DISCOVER"));
						$types[] = HTMLHelper::_('select.option', 'DinersClub', Text::_("DINERS_CLUB"));
						$types[] = HTMLHelper::_('select.option', 'JCB', Text::_("JCB"));

						$return = HTMLHelper::_('select.genericlist', $types, 'activated', null, 'value', 'text', 0);
						echo $return;
					?>
				</div>
			</div>
			<div class="form-group">
				<label for="creditcard_name" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
					<?php echo Text::_('Name On Card'); ?>
				</label>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<input type="text" name="creditcard_name" id="creditcard_name" size="25" class="inputbox required" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="creditcard_number" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
					<?php echo Text::_('Credit Card Number'); ?>
				</label>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<input type="text" name="creditcard_number" id="creditcard_number" maxlength="16" size="25"
						class="inputbox required" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="creditcard_code" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
					<?php echo	Text::_('Credit Card Security Code '); ?>
				</label>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12"><input type="text" name="creditcard_code"
					id="creditcard_code" size="25" class="inputbox required" value="" />
				</div>
			</div>
			<div class="form-group">
				<label for="expire_month" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
					<?php echo Text::_('Expiration Date');?>
				</label>
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<?php
					$all = array();
					$all[0] = new stdClass;

					$all[0]->value = '0';
					$all[0]->text = 'Months';

					for ($i = 1; $i < 13; $i++)
					{
						$timestamp = mktime(0, 0, 0, $i + 1, 0, date("Y"));
						$months[$i] = new stdClass;
						$months[$i]->value = $i;
						$months[$i]->text = date("M", $timestamp);
					}

					$months = array_merge($all, $months);
					echo HTMLHelper::_('select.genericlist', $months, 'expire_month', 'class="inputbox required" ', 'value', 'text', date('m'));
					echo HTMLHelper::_('select.integerlist', date('Y'), 2030, 1, 'expire_year', 'size="1" class="inputbox required" ');
					?>
				</div>
			</div>
			<hr>
			<div class="form-group">
				<div class="alert alert-success " id="">
					<span  onClick="plg_auth_showHide()"><strong>
						<a id='tj_payGway_billMsg'><?php echo $plg_billStyleMsg; ?></a></strong>
					</span>
				</div>
			</div>
			<div id="tj_payGway_billInfo" style="display:<?php echo $plg_billStyle; ?>">
				<div class="form-group">
					<label for="address" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
						<?php echo Text::_('Address');?>
					</label>
					<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
						<input type="text" name="address" id="address" class="inputbox required" value="<?php echo $wholeAddress; ?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="address" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
						<?php echo Text::_('City');?>
					</label>
					<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
						<input type="text" name="city" id="city" class="inputbox required" value="
						<?php echo !empty($userInfo['city']) ?$userInfo['city']:'';?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="state" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label"><?php echo Text::_('State');?></label>
					<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
						<input type="text" name="state" id="state" class="inputbox required" value="
						<?php echo !empty($userInfo['state_code']) ?$userInfo['state_code']:'';?>" />
					</div>
				</div>
				<div class="form-group">
					<label for="state" class="col-lg-2 col-md-2 col-sm-3 col-xs-12 control-label">
						<?php echo Text::_('Zip');?>
					</label>
					<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
						<input type="text" name="zip" id="zip" class="inputbox required" value="
						<?php echo !empty($userInfo['zipcode']) ?$userInfo['zipcode']:'';?>" />
					</div>
				</div>
			</div>
			<div class="form-actions">
				<!--<button type="button" name="submit" class="inputbox" onclick="submitbutton('ConfirmPayment');">
					<?php echo Text::_('Make Payment') ?>
				</button>	-->
				<input type="submit" name="submit"  value="<?php echo Text::_('SUBMIT');?>" class="btn btn-success"/>
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
