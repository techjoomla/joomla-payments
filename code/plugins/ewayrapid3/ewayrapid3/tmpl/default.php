<?php defined('_JEXEC') or die(); ?>

<h3><?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_HEADER') ?></h3>
<p align="center">
<form id="payment-form" action="<?php echo htmlentities($vars->FormActionURL) ?>" class="form-horizontal" method="post">
	<input type="hidden" name="EWAY_ACCESSCODE" value="<?php echo $vars->AccessCode ?>" />
	<!--<input type="hidden" name="FormActionURL" value="<?php //echo $vars->FormActionURL ?>" />
	 -->
	<div class="control-group" id="control-group-card-holder">
		<label for="card-holder" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_CARDHOLDER') ?>
		</label>
		<div class="controls">
			<input type="text" name="EWAY_CARDNAME" id="EWAY_CARDNAME" class="input-large" value="" />
		</div>
	</div>
	<div class="control-group" id="control-group-card-number">
		<label for="card-number" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_CC') ?>
		</label>
		<div class="controls">
			<input type="text" name="EWAY_CARDNUMBER" id="EWAY_CARDNUMBER" class="input-large" />
		</div>
	</div>
	<div class="control-group" id="control-group-card-expiry">
		<label for="card-expiry" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_EXPDATE') ?>
		</label>
		<div class="controls">
			<?php echo $this->selectMonth() ?><span> / </span><?php echo $this->selectYear() ?>
		</div>
	</div>
	<div class="control-group" id="control-group-card-cvc">
		<label for="card-cvc" class="control-label" style="width:190px; margin-right:20px;">
			<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_CVC') ?>
		</label>
		<div class="controls">
			<input type="text" name="EWAY_CARDCVN" id="EWAY_CARDCVN" class="input-mini" />
		</div>
	</div>
	<div class="control-group">
		<label for="pay" class="control-label" style="width:190px; margin-right:20px;">
		</label>
		<div class="controls">
			<input type="submit" id="payment-button" class="btn" value="<?php echo JText::_('PLG_PAYMENT_EWAYRAPID3_FORM_PAYBUTTON') ?>" />
		</div>
	</div>
</form>
</p>
