<?php
/**
 * @package    Joomla-Payments
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       10.12.14
 *
 * @copyright  Copyright (C) 2008 - 2014 Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$merchant_id = $this->params->get("merchant_id", "");
$project_id = $this->params->get("project_id", "");
$projectPassword = $this->params->get("project_password", "");
$merchant_TxId = $this->params->get("merchant_TxId", "");
$field1_label = $this->params->get("field1_label", "");
$field1_text = $this->params->get("field1_text", "");

// After BIC was entered - we post to the current request URL to do the redirect then
if (isset($_POST['submit']))
{
	$bic = Factory::getApplication()->input->getCmd("bic", "");

	// Get the Giropay SDK
	$request = new GiroCheckout_SDK_Request('giropayTransaction');
	$request->setSecret($projectPassword);

	// Clean purpose
	$vars->item_name = substr(preg_replace('/[^a-z 0-9]/i', '', $vars->item_name), 0, 20);

	// Giropay wants a int value where the last two decimals are the float val!
	$vars->amount = str_replace(".", "", number_format($vars->amount, 2));

	// Set the giropay request values
	$request->addParam('merchantId', $merchant_id)
		->addParam('projectId', $project_id)
		->addParam('merchantTxId', $project_id)
		->addParam('amount', $vars->amount)
		->addParam('currency', $vars->currency_code)
		->addParam('purpose', $vars->order_id . " " . (!empty($vars->item_name)? $vars->item_name : ''))
		->addParam('bic', $bic)
		->addParam('info1Label', $field1_label)
		->addParam('info1Text', $field1_text)
		->addParam('urlRedirect', $vars->return)
		->addParam('urlNotify', $vars->notify_url)
		->submit();

	// Get the result (array)
	$result = $request->getResponseParams();

	// Throw exception if status is not go! :-)
	if ($result['rc'] != '0')
	{
		throw new Exception(Text::_("AN_ERROR_OCCURED") . " " . $result['rc'] . " " . $result['msg']);
	}

	// Redirect to giropay :-)
	$request->redirectCustomerToPaymentProvider();

	// No html ouput - just the redirect header
	jexit();
}
?>
<div class="akeeba-bootstrap compojoom-bootstrap">
	<form name="tj_Plug_PaypalForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="form-horizontal" method="post">
		<div class="row">
			<div class="col-sm-12 margin-bottom-15 span12">
			<?php echo Text::_("GIROPAY_INTRO"); ?>
			<img src="<?php echo Uri::root(); ?>plugins/payment/giropay/GiroCheckout_SDK/logo/Logo_giropay_60_px.jpg" alt="Giropay.de"
				class="img-responsive" style="float: right" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 span3">
				<label for="bic"><?php echo Text::_("YOUR_BIC"); ?></label>
			</div>
			<div class="col-md-9 span9">
				<input type="text" id="bic" name="bic" class="form-control required" required="required" value="" />
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<input type="submit" name="submit" class="btn btn-primary" value="<?php echo Text::_("PAY_NOW");?>" />
			</div>
		</div>
	</form>
</div>
