<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');

$document =JFactory::getDocument();
JHTML::_('behavior.formvalidation');
$userInfo = array();
$plg_billStyle = "block";
$plg_billStyleMsg = JText::_('PLG_AUTHONET_HIDE_BILL_INFO');
if(!empty($vars->userInfo))
{
	$plg_billStyle="none";
	$userInfo = $vars->userInfo;
	$plg_billStyleMsg = JText::_('PLG_AUTHONET_SHOW_BILL_INFO');
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
<form action="<?php echo $vars->url; //$vars->submiturl; // rollded back  ?>" name="adminForm" id="adminForm" onSubmit="return myValidate(this);"  class="form-validate form-horizontal"  method="post">
	<div>
	<?php
	if(!empty($vars->userInfo))
	{
	?>
	<div class="control-group">

			<div class="alert alert-success " id="">
				 <span  onClick="plg_auth_showHide()"><strong>
					<a id='tj_payGway_billMsg'><?php echo $plg_billStyleMsg ; ?></a></strong>
				 </span>
			</div>

	</div>
	<?
	} ?>
	<div id="tj_payGway_billInfo" style="display:<?php echo $plg_billStyle; ?>">
		<div class="control-group">
			<label for="cardfname" class="control-label"><?php echo JText::_( 'FIRST_NAME' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardfname" type="text" name="cardfname" size="35" value="<?php echo !empty($userInfo['firstname']) ?$userInfo['firstname']:'' ;?>" /></div>
		</div>

		<div class="control-group">
			<label for="cardlname" class="control-label"><?php echo JText::_( 'LAST_NAME' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardlname" type="text" name="cardlname" size="35" value="<?php echo !empty($userInfo['lastname']) ?$userInfo['lastname']:'' ;?>" /></div>
		</div>

		<div class="control-group">
			<label for="cardaddress1" class="control-label"><?php echo JText::_( 'STREET_ADDRESS' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardaddress1" type="text" name="cardaddress1" size="" value="<?php echo !empty($userInfo['add_line1']) ?$userInfo['add_line1']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="cardaddress2" class="control-label"><?php echo JText::_( 'STREET_ADDRESS_CONTINUED' ) ?></label>
			<div class="controls"><input class="inputbox" id="cardaddress2"  type="text" name="cardaddress2" size="45" value="<?php echo !empty($userInfo['add_line2']) ?$userInfo['add_line2']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="cardcity" class="control-label"><?php echo JText::_( 'CITY' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardcity" type="text" name="cardcity" size="35" value="<?php echo !empty($userInfo['city']) ?$userInfo['city']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="cardstate" class="control-label"><?php echo JText::_( 'STATE' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardstate" type="text" name="cardstate" size="10" value="<?php echo !empty($userInfo['state_code']) ?$userInfo['state_code']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="cardzip" class="control-label"><?php echo JText::_( 'POSTAL_CODE' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardzip" type="text" name="cardzip" size="10" value="<?php echo !empty($userInfo['zipcode']) ?$userInfo['zipcode']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="cardcountry" class="control-label"><?php echo JText::_( 'COUNTRY' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardcountry" type="text" name="cardcountry" size="35" value="<?php echo !empty($userInfo['country_code']) ?$userInfo['country_code']:'' ;?>" /></div>
		</div>
		<div class="control-group">
			<label for="email" class="control-label"><?php echo JText::_( 'EMAIL_ADDRESS' ) ?></label>
			<div class="controls"><input class="inputbox required" id="email" type="text" name="email" size="35" value="<?php echo $vars->user_email;?>" /></div>
		</div>
	</div> <!-- end of bill info-->

			<hr/>

		<div class="control-group">
			<label for="" class="control-label"><?php echo JText::_( 'CREDIT_CARD_TYPE' ) ?></label>
			<div class="controls"><?php
			$types = array();
			$credit_cards=$this->params->get( 'credit_cards', '' );
			$creditcardarray=array(JText::_( "VISA" )=>'Visa', JText::_( "MASTERCARD" )=>'Mastercard',JText::_( "AMERICAN_EXPRESS" )=>'AmericanExpress',
									JText::_( "DISCOVER" )=>'Discover',JText::_( "DINERS_CLUB" )=>'DinersClub',JText::_( "AUT_JCB" )=>'JCB');
			if(!empty($credit_cards))
			{
				foreach($credit_cards as $credit_card)
				{
					if(in_array($credit_card,$creditcardarray))
					{
						foreach($creditcardarray as $creditkey=>$credit_cardall)
						{
							if($credit_card==$credit_cardall)
								$types[] = JHTML::_('select.option', $credit_cardall, $creditkey );
						}


					}

				}


			}
			else
			{
				foreach($creditcardarray as $creditkey=>$credit_cardall)
				{
						$types[] = JHTML::_('select.option', $credit_cardall, $creditkey );
				}
			}
		$return = JHTML::_('select.genericlist', $types,'activated',null, 'value','text', 0);
		echo $return; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="cardnum" class="control-label"><?php echo JText::_( 'CARD_NUMBER' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardnum" type="text" name="cardnum" size="35" value="" /></div>
		</div>
		<div class="control-group">
			<label for="cardexp" class="control-label"><?php echo JText::_( 'EXPIRATION_DATE_IN_FORMAT_MMYY' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardexp" type="text" name="cardexp" size="10" value="" /></div>
		</div>
		<div class="control-group">
			<label for="cardcvv" class="control-label"><?php echo JText::_( 'CARD_CVV_NUMBER' ) ?></label>
			<div class="controls"><input class="inputbox required" id="cardcvv" type="text" name="cardcvv" size="10" value="" /></div>
		</div>

		<div class="form-actions">
			<input type="hidden" name="amount" size="10" value="<?php echo $vars->amount;?>" />
			<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
			<input type="hidden" name="return" size="10" value="<?php echo $vars->return;?>" />
			<input type="hidden" name="order_id" size="10" value="<?php echo $vars->order_id;?>" />
			<input type="hidden" name="plugin_payment_method" value="onsite" />
			<input type="submit" name="submit" class="btn btn-success btn-large" value="<?php echo JText::_('SUBMIT');?>" />
		</div>
	</div>
</form>
</div>
