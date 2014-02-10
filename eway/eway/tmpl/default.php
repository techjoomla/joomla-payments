<?php 
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access'); 

?>
<div class="techjoomla-bootstrap">
<p align="center">
<form action="<?php echo htmlentities($vars->responseurl) ?>"  method="post" id="paymentForm">

	<!-- just REDIRCT TO SERVER WITH GIVEN URL
	<input type="hidden" name="merchant_id" value="<?php //echo $vars->merchant_id ?>" />-->
	<input type="submit" class="btn" />
</form>
</p>
</div>
<?php
?>
