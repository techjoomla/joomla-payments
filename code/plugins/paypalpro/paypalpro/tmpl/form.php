<?php
/**
 * @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

$document = Factory::getDocument();
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
	function myValidate(f)
	{
		var parentDiv = jQuery('#paypalProDiv');
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
		}

		return false;
	}
</script>
<div class="tjcpg-wrapper">
	<form action="<?php echo $vars->url;?>" class="form-validate form-horizontal" onSubmit="return myValidate(this);" method="post" >
		<div id="paypalProDiv">
			<div class="control-group">
				<label for="cardtype" class="control-label">
					<?php echo Text::_('CREDIT_CARD_TYPE'); ?>
				</label>
				<div class="controls">
					<?php $types = array();
							$types[] = HTMLHelper::_('select.option', 'Visa', Text::_("VISA"));
							$types[] = HTMLHelper::_('select.option', 'Mastercard', Text::_("MASTERCARD"));
							$types[] = HTMLHelper::_('select.option', 'AmericanExpress', Text::_("AMERICAN_EXPRESS"));
							$types[] = HTMLHelper::_('select.option', 'Discover', Text::_("DISCOVER"));
							$types[] = HTMLHelper::_('select.option', 'DinersClub', Text::_("DINERS_CLUB"));
							$types[] = HTMLHelper::_('select.option', 'JCB', Text::_("AUT_JCB"));

							$return = HTMLHelper::_('select.genericlist', $types, 'credit_card_type', null, 'value', 'text', 0);
							echo $return; ?>
				</div>
			</div>
			<div class="control-group">
				<label for="cardnum" class="control-label">
					<?php echo Text::_('CARD_NUMBER'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardnum" type="text" name="cardnum" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardlname" class="control-label">
					<?php echo Text::_('EXPIRATION_DATE_IN_FORMAT_MMYY'); ?>
				</label>
				<div class="controls">
					<?php
						$all[0] = new stdClass;
						$all[0]->value = '0';
						$all[0]->text = 'Months';

						for ($i = 1; $i < 13; $i++)
						{
							$timestamp = mktime(0, 0, 0, ($i + 1), 0, date("Y"));
							$months[$i] = new stdClass;
							$months[$i]->value = $i;
							$months[$i]->text = date("M", $timestamp);
						}

						$months = array_merge($all, $months);
						echo HTMLHelper::_('select.genericlist', $months, 'expire_month', 'class="inputbox input-medium" ', 'value', 'text', date('m'));
						echo HTMLHelper::_('select.integerlist', date('Y'), 2030, 1, 'expire_year', 'size="1" class="inputbox input-medium" ');?>
				</div>
			</div>
			<div class="control-group">
				<label for="cardcsc" class="control-label">
					<?php echo Text::_('CARD_CSC_NUMBER'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardcsc" type="text" name="cardcsc" size="10" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardfname" class="control-label">
					<?php echo Text::_('FIRST_NAME'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardfname" type="text" name="cardfname" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardlname" class="control-label">
					<?php echo Text::_('LAST_NAME'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardlname" type="text" name="cardlname" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardaddress1" class="control-label">
					<?php echo Text::_('STREET_ADDRESS'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardaddress1" type="text" name="cardaddress1" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardaddress2" class="control-label">
					<?php echo Text::_('STREET_ADDRESS_CONTINUED'); ?>
				</label>
				<div class="controls">
					<input class="inputbox" id="cardaddress2" type="text" name="cardaddress2" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardzip" class="control-label">
					<?php echo Text::_('POSTAL_CODE'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardzip" type="text" name="cardzip" size="10" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardcity" class="control-label">
					<?php echo Text::_('CITY'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardcity" type="text" name="cardcity" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardstate" class="control-label">
					<?php echo Text::_('STATE'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardstate" type="text" name="cardstate" size="20" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="cardcountry" class="control-label">
					<?php echo Text::_('COUNTRY'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="cardcountry" type="text" name="cardcountry" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<label for="email" class="control-label">
					<?php echo Text::_('EMAIL_ADDRESS'); ?>
				</label>
				<div class="controls">
					<input class="inputbox required" id="email" type="text" name="email" size="35" value="" />
				</div>
			</div>
			<div class="control-group">
				<hr/>
			</div>
			<div class="form-actions">
				<input type="hidden" name="is_recurring" value="<?php echo $vars->is_recurring; ?>" />
				<input type="hidden" name="recurring_count" value="<?php echo $vars->recurring_count; ?>" />
				<input type="hidden" name="recurring_frequency" value="<?php echo $vars->recurring_frequency; ?>" />
				<input type="hidden" name="recurring_amount" value="<?php echo $vars->amount; ?>" />
				<input type="submit" name="submit" class="btn btn-success btn-large"  value="<?php echo Text::_('SUBMIT'); ?>" />
				<input type="hidden" name="order_id" value="<?php echo $vars->order_id; ?>" />
				<input type="hidden" name="check" value="" />
				<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id; ?>" />
				<input type="hidden" name="chargetotal" value="<?php echo $vars->amount; ?>" />
				<input type="hidden" name="return" size="10" value="<?php echo $vars->return; ?>" />
				<input type="hidden" name="plugin_payment_method" value="onsite" />
			</div>
		</div>
	</form>
</div>
