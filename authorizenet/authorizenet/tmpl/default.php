<?php 
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access'); ?>

<table >
	<tbody>
	<tr>
		<td >		
			<form action="<?php echo $vars->url; ?>" method="post">			
				
			<table>
				<tr>
					<td><?php echo JText::_( 'FIRST_NAME' ) ?></td>
					<td><input type="text" name="cardfname" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'LAST_NAME' ) ?></td>
					<td><input type="text" name="cardlname" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STREET_ADDRESS' ) ?></td>
					<td><input type="text" name="cardaddress1" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STREET_ADDRESS_CONTINUED' ) ?></td>
					<td><input type="text" name="cardaddress2" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CITY' ) ?></td>
					<td><input type="text" name="cardcity" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'STATE' ) ?></td>
					<td><input type="text" name="cardstate" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'POSTAL_CODE' ) ?></td>
					<td><input type="text" name="cardzip" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'COUNTRY' ) ?></td>
					<td><input type="text" name="cardcountry" size="35" value="" /></td>
				</tr>							
					<tr>
						<td><?php echo JText::_( 'EMAIL_ADDRESS' ) ?></td>
						<td><input type="text" name="email" size="35" value="<?php echo $vars->user_email;?>" /></td>
					</tr>					
				<tr>
					<td colspan="2"><hr/></td>
				</tr>				
				<tr>
					<td><?php echo JText::_( 'CREDIT_CARD_TYPE' ) ?></td>
					<td><?php $types = array();
		$types[] = JHTML::_('select.option', 'Visa', JText::_( "VISA" ) );
		$types[] = JHTML::_('select.option', 'Mastercard', JText::_( "MASTERCARD" ) );
		$types[] = JHTML::_('select.option', 'AmericanExpress', JText::_( "AMERICAN_EXPRESS" ) );
		$types[] = JHTML::_('select.option', 'Discover', JText::_( "DISCOVER" ) );
		$types[] = JHTML::_('select.option', 'DinersClub', JText::_( "DINERS_CLUB" ) );
		$types[] = JHTML::_('select.option', 'JCB', JText::_( "AUT_JCB" ) );
		
		$return = JHTML::_('select.genericlist', $types,'activated',null, 'value','text', 0);
		echo $return; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CARD_NUMBER' ) ?></td>
					<td><input type="text" name="cardnum" size="35" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'EXPIRATION_DATE_IN_FORMAT_MMYY' ) ?></td>
					<td><input type="text" name="cardexp" size="10" value="" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_( 'CARD_CVV_NUMBER' ) ?></td>
					<td><input type="text" name="cardcvv" size="10" value="" /></td>

				</tr>
			</table>
			<input type="hidden" name="amount" size="10" value="<?php echo $vars->amount;?>" />
			<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
			<input type="hidden" name="return" size="10" value="<?php echo $vars->return;?>" />
			<input type="hidden" name="order_id" size="10" value="<?php echo $vars->order_id;?>" />
			<input type="hidden" name="plugin_payment_method" value="onsite" />
						<input type="submit" name="submit" value="<?php echo JText::_('MAKE_PAYMENT');?>" />
			</form>
		</td>
	</tr>
	</tbody>
</table>
