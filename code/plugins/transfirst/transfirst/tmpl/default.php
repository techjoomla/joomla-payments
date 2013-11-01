<?php 
/**
 * @copyright Copyright (C) 2012 -2013 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
	defined('_JEXEC') or die('Restricted access'); 

$document =JFactory::getDocument();
JHtml::_('behavior.formvalidation');

//load language
$lang =JFactory::getLanguage();
$extension = 'plg_payment_transfirst';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

?>
<script type="text/javascript">
function myValidate(f)
{
	var month=document.getElementById("card_exp_month").value;
	if(!month)
	{
		alert('Please select the month');
		return false;
	}
	if(document.formvalidator.isValid(f)) {
		f.check.value='<?php echo JSession::getFormToken(); ?>'; 
		return true; 
	}
	else 
	{
		var msg = 'Some values are not acceptable.  Please retry.';
		alert(msg);
		return false;
	}
	
}
function showcardtype(id)
{
	if(id=="creadit_card")
	{
		 if(!jQuery("#activated option[value='AmericanExpress']").length){
			jQuery("#activated").append('<option value="AmericanExpress" ><?php echo JText::_('PLG_TRANSFIRST_AMERICAN_EXPRESS') ?></option>');
		}

		if(!jQuery("#activated option[value='DinersClub']").length){
			jQuery("#activated").append('<option value="DinersClub"  ><?php echo JText::_('PLG_TRANSFIRST_DINERS_CLUB') ?></option>');
		}

		if(!jQuery("#activated option[value='JCB']").length){
			jQuery("#activated").append('<option value="JCB"  ><?php echo JText::_('PLG_TRANSFIRST_AUT_JCB') ?></option>');
		}

	}
	else if(id=="debit_card")
	{
		jQuery("#activated option[value='AmericanExpress']").remove();
		jQuery("#activated option[value='DinersClub']").remove();
		jQuery("#activated option[value='JCB']").remove();
	}
}
</script>

<div class="akeeba-bootstrap">
<form action="<?php echo $vars->url; ?>" name="adminForm" id="adminForm" onSubmit="return myValidate(this);"  enctype="multipart/form-data" class="form-validate form-horizontal"  method="post">
	<div>
		<div class="control-group">
			<label for="card_num" class="control-label"><?php echo JText::_( 'PLG_TRANSFIRST_CARD_NUMBER' ) ?></label>
			<div class="controls">
				<input type="text" class="required validate-numeric invalid" id="card_num"   value=""  name="card_num" size="35" required="required" />
			</div>
		</div>

		<div class="control-group">
			<label for="card_type" class="control-label"><?php echo JText::_( 'PLG_TRANSFIRST_CARD_TYPE' ) ?></label>
			<div class="controls">
				<input type="radio" class="" id="creadit_card" value="1" name="card_type" checked onchange="showcardtype(id)" />
				<?php echo JText::_('PLG_TRANSFIRST_CREDIT_CARD');?>&nbsp;&nbsp;
				<input type="radio" class="" id="debit_card" value="0" name="card_type" onchange="showcardtype(id)"/>
				<?php echo JText::_('PLG_TRANSFIRST_DEBIT_CARD');?>
			</div>
		</div>
		
		<div class="control-group">
			<label for="" class="control-label"><?php echo JText::_( 'PLG_TRANSFIRST_CREDIT_CARD_TYPE' ) ?></label>
			<div class="controls"><?php 
			$types = array();
			$credit_cards=$this->params->get( 'credit_cards', '' );
			$creditcardarray=array(JText::_( "PLG_TRANSFIRST_VISA" )=>'Visa', JText::_( "PLG_TRANSFIRST_MASTERCARD" )=>'Mastercard',JText::_( "PLG_TRANSFIRST_AMERICAN_EXPRESS" )=>'AmericanExpress',JText::_( "PLG_TRANSFIRST_DISCOVER" )=>'Discover',JText::_( "PLG_TRANSFIRST_DINERS_CLUB" )=>'DinersClub',JText::_( "PLG_TRANSFIRST_AUT_JCB" )=>'JCB');
			if(!empty($credit_cards))
			{
				foreach($credit_cards as $credit_card)
				{
					if(in_array($credit_card,$creditcardarray))
					{
						foreach($creditcardarray as $creditkey=>$credit_cardall)
						{
							if($credit_card==$credit_cardall)
								$types[] = JHtml::_('select.option', $credit_cardall, $creditkey );
						}
					}
				}
			}
			else 
			{
				foreach($creditcardarray as $creditkey=>$credit_cardall)
				{
					$types[] = JHtml::_('select.option', $credit_cardall, $creditkey );
				}
			}
		$return = JHtml::_('select.genericlist', $types,'activated',null, 'value','text', 0);
		echo $return; ?>
			</div>
		</div>
		<div class="control-group">
			<label for="cardlname" class="control-label"><?php echo JText::_( 'PLG_TRANSFIRST_EXPIRATION_DATE_IN_FORMAT_MMYY' ) ?></label>
			<div class="controls">
						<?php
							$all[0]=new stdClass;
							$all[0]->value = '';
							$all[0]->text = 'Months';
							for($i=1; $i<13; $i++) {
								$timestamp = mktime(0,0,0,$i+1, 0, date("Y"));
								$months[$i]=new stdClass;
								$months[$i]->value = $i;
								$months[$i]->text = date("M", $timestamp);
							}
							$months = array_merge($all, $months);
							echo JHTML::_('select.genericlist',$months, 'card_exp_month', 'class="inputbox input-small required" ', 'value', 'text', date('m'));
							echo JHTML::_('select.integerlist',date('Y'), 2030, 1, 'card_exp_year', 'size="1" class="inputbox input-small" ');
						?>
					</div>
		</div>
		<div class="control-group">
			<label for="cardfname" class="control-label"><?php echo JText::_( 'PLG_TRANSFIRST_CVV_CODE' ) ?></label>
			<div class="controls"><input class="inputbox required" id="card_code" type="text" name="card_code" size="35" value="" required="required" /></div>
		</div>
		<div class="form-actions">
			<input type="hidden" name="amount" size="10" value="<?php echo $vars->amount;?>" />
			<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
			<input type="hidden" name="return" size="10" value="<?php echo $vars->return;?>" />
			<input type="hidden" name="order_id" size="10" value="<?php echo $vars->order_id;?>" />
			<input type="hidden" name="plugin_payment_method" value="onsite" />
			<input type="submit" name="submit" class="btn btn-success btn-large" value="<?php echo JText::_('PLG_TRANSFIRST_SUBMIT');?>" />
		</div>
	</div>
</form>
</div>
