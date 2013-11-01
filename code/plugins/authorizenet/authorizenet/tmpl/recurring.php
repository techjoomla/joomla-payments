<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
		defined('_JEXEC') or die('Restricted access');
		$document =& JFactory::getDocument();
		JHTML::_('behavior.formvalidation');
		
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
</script>

<table class="userlist">
	<tbody>
	<tr>
		<td class="title">		
			<form name="adminForm" id="adminForm" class="form-validate form-horizontal" name="recurrform" onSubmit="return myValidate(this);" action="<?php echo $vars->url; ?>"  method="post">			
				
			<table>
							<tr>
                    <td colspan="2">
                        <b><?php echo JText::_('CREATE_AUTH_SUBSCR'); ?><br>
                        <br></b>
                    </td>
                    
                </tr>
               <tr>
                    <td>
                        <?php echo JText::_('AUTH_SUBSCR_NAME'); ?>
                    </td>
                    <td>

										<?php echo $vars->item_name; ?>
										 <input type="hidden" class="inputbox required" name="sub_name" id="sub_name" value="<?php echo $vars->item_name; ?>">
                       
                    </td>
                </tr>
                 <tr>
                    <td>
                    <?php echo JText::_('AUTH_SUBSCR_LENGTH');      
                   
                    ?>
                    
                    [7 -365]                        
                    </td>
                    <td>
												<?php echo $vars->recurring_payment_interval_length;   ?>
												
                    </td>
                </tr>
                <tr>
                    <td>
                       <?php echo JText::_('AUTH_SUBSCR_UNIT'); ?> 
                    </td>
                    <td>
                    <?php echo $vars->recurring_payment_interval_unit;   ?>

                   </td>
                </tr>
                <tr>
                    <td>
                       <?php echo JText::_('AUTH_SUBSCR_START_DATE'); ?>[YYYY-MM-DD]
                    </td>
                    <td>
										<?php 
											echo  $vars->recurring_startdate;										?>
												
                    </td>
                </tr>
                
                <tr>
                    <td>
                         <?php echo JText::_('AUTH_SUBSCR_AMT'); ?>
                    </td>
                    <td>
											<?php  echo $vars->amount; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                         <?php echo JText::_('AUTH_SUBSCR_TOTAL_OCCR'); ?>
                    </td>
                    <td>
											<?php  echo $vars->recurring_payment_interval_totaloccurances; ?>
                    </td>
                </tr>
                <tr>
					<td><?php echo JText::_( 'FIRST_NAME' ) ?></td>
					<td><input type="text" name="firstName" class="inputbox required" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'LAST_NAME' ) ?></td>
					<td><input type="text" name="lastName" class="inputbox required" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STREET_ADDRESS' ) ?></td>
					<td><input type="text" name="cardaddress1" class="inputbox required" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STREET_ADDRESS_CONTINUED' ) ?></td>
					<td><input type="text" name="cardaddress2" class="inputbox" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CITY' ) ?></td>
					<td><input type="text" name="cardcity" class="inputbox required" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STATE' ) ?></td>
					<td><input type="text" name="cardstate" class="inputbox required" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'POSTAL_CODE' ) ?></td>
					<td><input type="text" name="cardzip" class="inputbox required" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COUNTRY' ) ?></td>
					<td><input type="text" name="cardcountry" class="inputbox required" size="35" value="" /></td>
				</tr>							
					<tr>
						<td><?php echo JText::_( 'EMAIL_ADDRESS' ) ?></td>
						<td><input type="text" name="email" class="inputbox required" size="35" value="" /></td>
					</tr>					
				<tr>
					<td colspan="2"><hr/></td>
				</tr>				
				<tr>
					<td><?php echo JText::_( 'CREDIT_CARD_TYPE' ) ?></td>
					<td><?php $types = array();
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
				echo $return; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CARD_NUMBER' ) ?></td>
					<td><input type="text" name="cardNumber" class="inputbox required" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'EXPIRATION_DATE_IN_FORMAT_MMYY' ) ?></td>
					<td><input type="text" name="expirationDate" class="inputbox required" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CARD_CVV_NUMBER' ) ?></td>
					<td><input type="text" name="cardcode" class="inputbox required" size="10" value="" /></td>

				</tr>
		<div class="form-actions">
								 <td colspan="2" align="center">
								 </br>								 </br>								 </br>
									<input type="hidden" name="check" value="post"/>
									<input type="hidden" class="inputbox required" name="amount" size="10" value="<?php echo $vars->amount;?>" />
									<input type="hidden" class="inputbox required" name="startDate" size="10" value="<?php echo $vars->recurring_startdate;?>" />
									<input type="hidden" class="inputbox required" name="totalOccurrences" size="10" value="<?php echo $vars->recurring_payment_interval_totaloccurances;?>" />
									<input type="hidden" class="inputbox required" name="intervalLength" size="10" value="<?php echo $vars->recurring_payment_interval_length;?>" />
									<input type="hidden" class="inputbox required" name="intervalUnit" size="10" value="<?php echo $vars->recurring_payment_interval_unit;?>" />
									<input type="hidden"  name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
									<input type="hidden"   name="return" size="10" value="<?php echo $vars->return;?>" />
									<input type="hidden" class="inputbox required" name="order_id" size="10" value="<?php echo $vars->order_id;?>" />
								 	<input type="hidden"  name="payment_type" value="recurring" />
									<input type="hidden" name="plugin_payment_method" value="onsite" />
									<input type="submit" 	name="submit" id="submit"   value="<?php echo JText::_('MAKE_PAYMENT');?>" />
								</div>
								</td>
                </tr>

			</table>

			
			
			</form>
		</td>
	</tr>
	</tbody>
</table>
