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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if(version_compare(JVERSION, '3.0', 'ge'))
{
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
}
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_tjcpg/assets/css/tjcpg.css');

?>

<?php
//Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar)) {
    $this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_tjcpg&view=tjcpgpayment'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
    <?php
    //8cacbb
    ?>
<div  style="background-color:#F5F5F5;padding:15px;border-radius:4px;" >
<div align="center" style="color:#FF0000;" >
		<span ><?php echo JText::_('TJCPG_MAKE_BASIC_SETUPS'); ?> </span>
		
		<div class="well well-small" style="color:black;">
				<div class="module-title nav-header">
					<i class="icon-comments-2"></i> <strong><?php echo JText::_('COM_TJCPG').' - '.JText::_('Tjcpg'); ?> </strong>
				</div>
				<hr class="hr-condensed">

				<div class="row-fluid">
					<div class="span12 alert alert-success"><?php echo JText::_('COM_TJCPG_SAMPLE_COMPONET_DES'); ?></div>
				</div>

				
</div>
</div>   

<?php
if(!empty($this->sidebar))
{
		echo "</div>"; // end of sidebar div
}
?>
 </form>    
