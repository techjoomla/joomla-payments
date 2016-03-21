<?php
/**
 * @version     1.0.0
 * @package     com_tjcpg
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      vidyasagar <vidyasagar_m@tekdi.net> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;
jimport( 'joomla.form.formvalidator' );
jimport('joomla.html.pane');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
JHtmlBehavior::framework();
jimport( 'joomla.html.parameter' );

//Load admin language file
$lang = JFactory::getLanguage();
//$lang->load('com_tjcpg', JPATH_ADMINISTRATOR);
$user=JFactory::getUser();
if(!$user->id){
?>
<div  style="background-color:#F5F5F5;padding:15px;border-radius:4px;" >
<div align="center" style="color:#FF0000;" >
		<span ><?php echo JText::_('TJCPG_LOGIN_MSG'); ?> </span>
</div>
</div>
<?php
	return false;
}
?>

<script type="text/javascript">

function myValidate(f)
{
	var msg = "<?php echo JText::_( "COM_TJCPG_ONLY_NUMERIC_VALUE_R_ACCEPTABLE")?>";
	if (document.formvalidator.isValid(f)) {
			f.check.value='<?php echo JSession::getFormToken(); ?>'; 
			
			return true; 
		}
		else {
			
			alert(msg);
		}
	
	
	return false;
}	
</script>
<h2 style="background-color:#F5F5F5;padding:15px;border-radius:4px;" ><?php echo JText::_('COM_TJCPG_COUPAN_ITEMID'); ?></h2>

    <form method="post" name="adminForm" class="form-validate" onSubmit="return myValidate(this);" >
      <table>
        <tr>
          <td><b><?php echo JText::_('COM_TJCPG_MANDATORY_PARMS'); ?></b></td>
        </tr>
        <tr>
					<td><?php echo JText::_('COM_TJCPG_AMUNT'); ?>: </td>
          <td><input name="amount" value="<?php echo (empty($posted['amount'])) ? '' : $posted['amount'] ?>" class="required validate-numeric" /></td>
        </tr>
        
        <tr>
						<td><?php echo JText::_( 'SEL_GATEWAY' ); ?>:</td>
						<td colspan="3">

									<?php
									
										$default="";
										if(empty($this->gateways)) 
											echo JText::_( 'NO_PAYMENT_GATEWAY' ); 
										else 
										{
											// SETTING FIRST AS DEFAULT 
											$default=$this->gateways[0]->id;
											$pg_list = JHtml::_('select.radiolist', $this->gateways, 'gateways', 'class="inputbox required" ', 'id', 'name',$default,false);
											echo $pg_list;
										}
										
										
									?>
									</div>
								</div>  		
						</td>
        </tr>
      </table>
      <div>
      	<input type="hidden" name="option" value="com_tjcpg" />
      	<input type="hidden" name="controller" value="payment" />
				<input type="hidden" name="task" value="save" />
				<input type="submit" value="<?php echo JText::_('COM_TJCPG_PLACE_ORDER'); ?>" />
				
			</div>
      
    </form>
