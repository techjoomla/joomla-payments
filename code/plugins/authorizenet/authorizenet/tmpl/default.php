<?php
/**
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$document = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
$userInfo = array();
$plg_billStyle = "block";
$plg_billStyleMsg = Text::_('PLG_AUTHONET_HIDE_BILL_INFO');

if (!empty($vars->userInfo))
{
	$plg_billStyle = "none";
	$userInfo = $vars->userInfo;
	$plg_billStyleMsg = Text::_('PLG_AUTHONET_SHOW_BILL_INFO');
}
?>

<script type="text/javascript">
	function myValidate(f)
	{
		var parentDiv = jQuery('#authorizenetDiv');
		parentDiv.addClass('isloading');
		jQuery("input[name='submit']").attr('disabled', true);

		if (document.formvalidator.isValid(f))
		{
			f.check.value='<?php echo Session::getFormToken(); ?>';
			return true;
		}
		else
		{
			parentDiv.removeClass('isloading');
			jQuery("input[name='submit']").attr('disabled', false);
			var msg = 'Some values are not acceptable.  Please retry.';
			alert(msg);
			plg_auth_showHide();
		}
		return false;
	}

	function plg_auth_showHide()
	{
		// Get the DOM reference
		var billEle = document.getElementById("tj_payGway_billInfo");
		// Toggle
		var eleStatus = billEle.style.display == "block" ? 'block':'none';// billEle.style.display = "none" :billEle.style.display = "block";

		if (eleStatus == "block")
		{
			billEle.style.display = "none";

			var showBillMsg = "<?php echo Text::_('PLG_AUTHONET_SHOW_BILL_INFO');?>";
			document.getElementById('tj_payGway_billMsg').innerHTML = showBillMsg;

		}
		else
		{
			// if not visible then show
			billEle.style.display = "block";

			var hideBillMsg = "<?php echo Text::_('PLG_AUTHONET_HIDE_BILL_INFO');?>";
			document.getElementById('tj_payGway_billMsg').innerHTML = hideBillMsg;
		}
	}
</script>

<div class="tjcpg-wrapper">
	<form action="<?php echo $vars->url; /* $vars->submiturl; // rollded back */  ?>" name="adminForm" id="adminForm" onSubmit="return myValidate(this);"  class="form-validate form-horizontal"  method="post">
		<div id='authorizenetDiv'>
			<?php
			if (!empty($vars->userInfo))
			{
			?>
				<div class="form-group row">
					<div class="alert alert-success " id="">
						<span  onClick="plg_auth_showHide()">
							<strong>
								<a id='tj_payGway_billMsg'>
									<?php echo $plg_billStyleMsg; ?>
								</a>
							</strong>
						</span>
					</div>
				</div>
			<?php
			}
			?>
			<div id="tj_payGway_billInfo" style="display:<?php echo $plg_billStyle; ?>">
				<div class="form-group row">
					<label for="cardfname" class="form-label col-md-4">
						<?php echo Text::_('FIRST_NAME') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardfname" type="text" name="cardfname"  value="<?php echo !empty($userInfo['firstname']) ?$userInfo['firstname']:'';?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardlname" class="form-label col-md-4">
						<?php echo Text::_('LAST_NAME') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8"><input class="inputbox form-control required" id="cardlname" type="text" name="cardlname"  value="<?php echo !empty($userInfo['lastname']) ?$userInfo['lastname']:''; ?>" /></div>
				</div>

				<div class="form-group row">
					<label for="cardaddress1" class="form-label col-md-4">
						<?php echo Text::_('STREET_ADDRESS') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardaddress1" type="text" name="cardaddress1" size="" value="<?php echo !empty($userInfo['add_line1']) ?$userInfo['add_line1']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardaddress2" class="form-label col-md-4">
						<?php echo Text::_('STREET_ADDRESS_CONTINUED'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control" id="cardaddress2"  type="text" name="cardaddress2" size="45" value="<?php echo !empty($userInfo['add_line2']) ?$userInfo['add_line2']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardcity" class="form-label col-md-4">
						<?php echo Text::_('CITY') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardcity" type="text" name="cardcity"  value="<?php echo !empty($userInfo['city']) ?$userInfo['city']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardstate" class="form-label col-md-4">
						<?php echo Text::_('STATE') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardstate" type="text" name="cardstate" value="<?php echo !empty($userInfo['state_code']) ?$userInfo['state_code']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardzip" class="form-label col-md-4">
						<?php echo Text::_('POSTAL_CODE') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardzip" type="text" name="cardzip" value="<?php echo !empty($userInfo['zipcode']) ?$userInfo['zipcode']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="cardcountry" class="form-label col-md-4">
						<?php echo Text::_('COUNTRY') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="cardcountry" type="text" name="cardcountry"  value="<?php echo !empty($userInfo['country_code']) ?$userInfo['country_code']:''; ?>" />
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="form-label col-md-4">
						<?php echo Text::_('EMAIL_ADDRESS') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
					</label>
					<div class="col-md-8">
						<input class="inputbox form-control required" id="email" type="text" name="email"  value="<?php echo $vars->user_email; ?>" />
					</div>
				</div>
			</div> <!-- end of bill info-->

			<hr/>

			<div class="form-group row">
				<label for="" class="form-label col-md-4">
					<?php echo Text::_('CREDIT_CARD_TYPE'); ?>
				</label>
				<div class="col-md-8">
					<?php
						$types = array();
						$credit_cards = $this->params->get('credit_cards', '');

						// Make string to array
						// After installing the plugin it return  credit_cards parmas value as string
						if (!is_array($credit_cards))
						{
							$array[] = $credit_cards;
							$credit_cards = $array;
						}

						$creditcardarray = array(Text::_("VISA") => 'Visa', Text::_("MASTERCARD") => 'Mastercard', Text::_("AMERICAN_EXPRESS") => 'AmericanExpress',
												Text::_("DISCOVER") => 'Discover', Text::_("DINERS_CLUB") => 'DinersClub', Text::_("AUT_JCB") => 'JCB');

						if (!empty($credit_cards))
						{
							foreach ($credit_cards as $credit_card)
							{
								if (in_array($credit_card, $creditcardarray))
								{
									foreach ($creditcardarray as $creditkey => $credit_cardall)
									{
										if ($credit_card == $credit_cardall)
										{
											$types[] = HTMLHelper::_('select.option', $credit_cardall, $creditkey);
										}
									}
								}
							}
						}
						else
						{
							foreach ($creditcardarray as $creditkey => $credit_cardall)
							{
								$types[] = HTMLHelper::_('select.option', $credit_cardall, $creditkey);
							}
						}

						$return = HTMLHelper::_('select.genericlist', $types, 'activated', 'class="form-select"', 'value', 'text', 0);
						echo $return; ?>
				</div>
			</div>

			<div class="form-group row">
				<label for="cardnum" class="form-label col-md-4">
					<?php echo Text::_('CARD_NUMBER') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
				</label>
				<div class="col-md-8">
					<input class="inputbox form-control required" id="cardnum" type="text" name="cardnum"  value="" />
				</div>
			</div>

			<div class="form-group row">
				<label for="cardexp" class="form-label col-md-4">
					<?php echo Text::_('EXPIRATION_DATE_IN_FORMAT_MMYY') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
				</label>
				<div class="col-md-8">
					<input class="inputbox form-control required" id="cardexp" type="text" name="cardexp" value="" />
				</div>
			</div>

			<div class="form-group row">
				<label for="cardcvv" class="form-label col-md-4">
					<?php echo Text::_('CARD_CVV_NUMBER') . Text::_('PLG_PAYMENT_AUTHORIZENET_REQUIRED_MARK'); ?>
				</label>
				<div class="col-md-8">
					<input class="inputbox form-control required" id="cardcvv" type="text" name="cardcvv" value="" />
				</div>
			</div>

			<div class="form-actions">
				<input type="hidden" name="amount" value="<?php echo $vars->amount; ?>" />
				<input type="hidden" name="user_id" value="<?php echo $vars->user_id; ?>" />
				<input type="hidden" name="return" value="<?php echo $vars->return; ?>" />
				<input type="hidden" name="order_id" value="<?php echo $vars->order_id; ?>" />
				<input type="hidden" name="plugin_payment_method" value="onsite" />
				<input type="submit" name="submit" class="btn btn-success btn-large" value="<?php echo Text::_('SUBMIT');?>" />
			</div>
		</div>
	</form>
</div>
