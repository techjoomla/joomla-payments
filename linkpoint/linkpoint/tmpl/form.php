<?php 
/*
  @package Social Ads
  @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
  @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
  @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access'); 
$session 	= JFactory::getSession();

?>

<form action="<?php echo $vars->url ?>" method="post">
	
	<table class="userlist">	
		<tr>
			<td><?php echo JText::_('Credit Card Type'); ?> </td>			
			<td><?php 
						$types = array();
						$types[] = JHTML::_('select.option', 'Visa', JText::_( "VISA" ) );
						$types[] = JHTML::_('select.option', 'Mastercard', JText::_( "MASTERCARD" ) );
						$types[] = JHTML::_('select.option', 'AmericanExpress', JText::_( "AMERICAN_EXPRESS" ) );
						$types[] = JHTML::_('select.option', 'Discover', JText::_( "DISCOVER" ) );
						$types[] = JHTML::_('select.option', 'DinersClub', JText::_( "DINERS_CLUB" ) );
						$types[] = JHTML::_('select.option', 'JCB', JText::_( "JCB" ) );

						$return = JHTML::_('select.genericlist', $types,'activated',null, 'value','text', 0);
						echo $return; 
				?>
			</td>
		</tr>		
		<tr>
			<td><?php echo JText::_('Name On Card'); ?> </td>
			<td><input type="text" name="creditcard_name" id="creditcard_name" size="25" class="inputbox" value="" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('Credit Card Number'); ?> </td>
			<td><input type="text" name="creditcard_number" id="creditcard_number" maxlength="16" size="25" class="inputbox" value="" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('Credit Card Security Code '); ?> </td>
			<td><input type="text" name="creditcard_code" id="creditcard_code" size="25" class="inputbox" value="" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('Expiration Date'); ?>:</td>
			<td>
				<?php
					$all[0]->value = '0';
					$all[0]->text = 'Months';
					for($i=1; $i<13; $i++) {
						$timestamp = mktime(0,0,0,$i+1, 0, date("Y"));
						$months[$i]->value = $i;
						$months[$i]->text = date("M", $timestamp);					
					}
					$months = array_merge($all, $months);
					echo JHTML::_('select.genericlist',$months, 'expire_month', 'class="inputbox" ', 'value', 'text', date('m'));
					echo JHTML::_('select.integerlist',date('Y'), 2030, 1, 'expire_year', 'size="1" class="inputbox" ');			
				?>
			</td>
		</tr>		
		<tr>
			<td><?php echo JText::_('Address'); ?></td>
			<td><input type="text" name="address" id="address" class="inputbox" value="" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('City'); ?></td>
			<td><input type="text" name="city" id="city" class="inputbox" value="" /></td>
		</tr>	
		<tr>
			<td><?php echo JText::_('State'); ?></td>
			<td><input type="text" name="state" id="state" class="inputbox" value="" /></td>
		</tr>	
		<tr>
			<td><?php echo JText::_('Zip'); ?></td>
			<td><input type="text" name="zip" id="zip" class="inputbox" value="" /></td>
		</tr>
		
	</table>	
	
	<!--<button type="button" name="submit" class="inputbox" onclick="submitbutton('ConfirmPayment');"><?php echo JText::_('Make Payment') ?></button>	-->
	<input type="submit" name="submit" class="button" value="<?php echo JText::_('Make Payment');?>" />	
	<input type="hidden" name="oid" value="<?php echo $vars->order_id;?>" />
	<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
	<input type="hidden" name="return" size="10" value="<?php echo $vars->return;?>" />
		<input type="hidden" name="chargetotal" value="<?php echo $vars->amount;?>" />
					<input type="hidden" name="plugin_payment_method" value="onsite" />
</form>
