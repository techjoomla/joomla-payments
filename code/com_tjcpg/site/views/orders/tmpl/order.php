<?php
defined( '_JEXEC' ) or die( ';)' );
/*$post = JRequest::get('post');
print"<pre>"; print_r($post);
//// for getting current tab status one page chkout::
$session =JFactory::getSession();
$ses_var = $session->get('processpayment');
var_dump($ses_var);*/
?>


<?php
	 if(!empty($this->orderinfo))
	 {
		 $ordInfo=$this->orderinfo;
?>
<h2 style="background-color:#F5F5F5;padding:15px;border-radius:4px;" ><?php echo JText::_('TJ_ORDER_DETAIL'); ?></h2>
<table width="400" border="3" cellpadding="5" cellspacing="2">
    <tr  bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_ORDERID'); ?></td>
				<td align="left" > <?php echo $ordInfo->id;?></td>
		</tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_USER_ID'); ?></td>
				<td align="left" > <?php echo $ordInfo->user_info_id;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_NAME'); ?></td>
				<td align="left" > <?php echo $ordInfo->name;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_EMAIL'); ?></td>
				<td align="left" > <?php echo $ordInfo->email;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_CDATE'); ?></td>
				<td align="left" > <?php echo $ordInfo->cdate;?></td>
    </tr>
    
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_AMOUNT'); ?></td>
				<td align="left" > <?php echo $ordInfo->amount;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_STAUS'); ?></td>
				<td align="left" > <?php echo $ordInfo->status;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_PROCESSOR'); ?></td>
				<td align="left" > <?php echo $ordInfo->processor;?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
        <td width="150"><?php echo JText::_('TJ_ORDER_IP'); ?></td>
				<td align="left" > <?php echo $ordInfo->ip_address;?></td>
    </tr>
    


</table>
<?php
}
else
{
	echo JText::_('TJCPG_ORDERDETAIL_NOT_FOUND'); 
}
?>


