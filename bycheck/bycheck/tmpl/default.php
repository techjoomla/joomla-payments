<?php 
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access'); ?>
<?php
JHTML::_('behavior.formvalidation');	


?>
<script type="text/javascript">
function myValidate(f)
{
	if (document.formvalidator.isValid(f)) {
		f.check.value='<?php echo JUtility::getToken(); ?>'; 
		return true; 
	}
	else {
		var msg = 'Some values are not acceptable.  Please retry.';
		alert(msg);
	}
	return false;
}		
</script>
<table >
	<tbody>
	<tr>
		<td >	
		
  		<form  method="post"  name="checkForm" class="form-validate" onSubmit="return myValidate(this);" action="<?php  echo $vars->url ?>">
				<table>
					<tr>
						<td class='ad-price-lable' colspan="2"><?php  echo JText::sprintf( 'ORDER_INFO', $vars->custom_name);?></td>
					</tr>
					<tr>
							<td class='ad-price-lable'><?php  echo JText::_('COMMENT');?></td>
							<td>
									<textarea id='comment' name='comment' class="inputbox required" rows='3' maxlength='135' size='28'></textarea>
							</td>
					</tr>
					<tr>
							<td class='ad-price-lable'><?php  echo JText::_('CON_PAY_PRO');?> </td>
							<td>						
								<?php
								if($vars->custom_email=="")
									$email = JText::_('NO_ADDRS');
								else
									$email = $vars->custom_email;
								?>
								<input type='hidden' name='mail_addr' value="<?php echo $email;?>" />
								<?php  echo $email;?>
							</td>
					</tr>
					<tr>
					<td>
																				<input type='hidden' name='check' value="" />
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
