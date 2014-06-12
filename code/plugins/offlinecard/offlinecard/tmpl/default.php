<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
	
// no direct access
defined('_JEXEC') or die('Restricted access'); 

$document =JFactory::getDocument();
JHTML::_('behavior.formvalidation');

?>
<script type="text/javascript">
function myValidate()
{
  var cardno = document.getElementById('cardnum').value;
  var cardtype = document.getElementById('activated').value;
  var visaregex = /^(?:4[0-9]{12}(?:[0-9]{3})?)$/;  
  if(!cardno.match(visaregex) && cardtype == 'Visa') 
  { alert('Invalid Visa Card No.');   return false; }
  var aeregex = /^(?:3[47][0-9]{13})$/;  
  if(!cardno.match(aeregex) && cardtype == 'AmericanExpress') 
  { alert('Invalid American Express Card No.');   return false; } 
  var discoverregex = /^(?:6(?:011|5[0-9][0-9])[0-9]{12})$/;  
  if(!cardno.match(discoverregex) && cardtype == 'Discover') 
  { alert('Invalid Discover Card No.');   return false; } 
  var dinersregex = /^(?:3(?:0[0-5]|[68][0-9])[0-9]{11})$/;  
  if(!cardno.match(dinersregex) && cardtype == 'DinersClub') 
  { alert('Invalid Diners Club Card No.');   return false; } 
  var masterregex = /^(?:5[1-5][0-9]{14})$/;  
  if(!cardno.match(masterregex) && cardtype == 'Mastercard') 
  { alert('Invalid Masterard No.');   return false; } 
  var jcbregex = /^(?:(?:2131|1800|35\d{3})\d{11})$/;  
  if(!cardno.match(jcbregex) && cardtype == 'JCB') 
  { alert('Invalid JCB Card No.');   return false; } 
  var cardexp = document.getElementById('cardexp').value;
  var valid = cardexp.indexOf("/");
  if(valid > -1) {  var data = cardexp.split('/'); } else {  var data = cardexp.split('-'); } 
  if(data[0] > 12 || data[0] < 1) { alert('Invalid Expiry Date1'); return false; }
  var d = new Date();
  var n = d.getFullYear();
  if(data[1] < n) { alert('Invalid Expiry Date'); return false; } else { return true;  }
}	
</script> 

<div class="akeeba-bootstrap">
<form action="<?php echo $vars->url; ?>" name="adminForm" id="adminForm" onSubmit="return myValidate();"  class="form-validate form-horizontal"  method="post">			
	<div>
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
			<div class="controls"><input class="inputbox required" id="cardcvv" type="text" name="cardcvv" size="10" value="" onclick=""/></div>
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
