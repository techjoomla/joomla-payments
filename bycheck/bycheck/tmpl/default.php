<?php 
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access'); ?>
<?php
	if($vars->custom_email=="")
		$email = JText::_('NO_ADDRS');
	else
		$email = $vars->custom_email;
?>
<table >
	<tbody>
	<tr>
		<td >	
		
  		<form  method="post"  name="checkForm" action="<?php  echo $vars->url ?>">
				<table>
							<tr>
									<td class='ad-price-lable'><?php  echo JText::_('COMMENT');?></td>
									<td>
											<textarea id='comment' name='comment' rows='3' maxlength='135' size='28'></textarea>
									</td>
							</tr>
							<tr>
									<td class='ad-price-lable'><?php  echo JText::_('CON_PAY_PRO');?> : </td>
									<td>
								
							<?php  echo $email;?>
									</td>
							</tr>
							<tr>
							<td>
								<input type='hidden' name='order_id' value="<?php echo $vars->order_id;?>" />
								<input type='hidden' name="total" value="<?php echo sprintf('%02.2f',$vars->amount) ?>" />
								<input type="hidden" name="user_id" size="10" value="<?php echo $vars->user_id;?>" />
								<input type='hidden' name='return' value="<?php echo $vars->return;?>" >
								<input type="hidden" name="plugin_payment_method" value="onsite" />
								<input type='submit' name='btn_check' id='btn_check'  value="<?php echo JText::_('SUBMIT'); ?>">
							</td>
							</tr>
				</table>			
	</form>
		</td>
	</tr>
	</tbody>
</table>
