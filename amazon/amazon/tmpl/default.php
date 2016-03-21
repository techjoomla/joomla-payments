<?php
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');
if(JVERSION >='1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/amazon/amazon/lib/ButtonGenerationWithSignature/ButtonGenerator.php');
else
	require_once(JPATH_SITE.'/plugins/payment/amazon/lib/ButtonGenerationWithSignature/ButtonGenerator.php');

	// Put your Access Key here
	$accessKey = $this->params->get('accesskey');

	// Put  your Secret Key here
	$secretKey = $this->params->get('secretkey');

	// Enter the amount you want to collect for the item
	$amount = $vars->currency_code." ".$vars->amount;

	// Valid values  are  HmacSHA256 and HmacSHA1.
	$signatureMethod = "HmacSHA256";

	// Enter a description of the item
	$description = $vars->item_name;

	// Optionally, enter an ID that uniquely identifies this transaction for your records
	$referenceId = $vars->order_id;

	// Optionally, enter the URL where senders should be redirected if they cancel their transaction
	$abandonUrl = $vars->cancel_return;

	// Optionally enter the URL where buyers should be redirected after they complete the transaction
	$returnUrl = $vars->return;

	// Optionally, enter "1" if you want to skip the final status page in Amazon Payments
	$immediateReturn = "0";

	// Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1"
		$processImmediate = "1";

	// Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
	$ipnUrl = $vars->notify_url;

	// Optionally, enter "1" if you want Amazon Payments to return the buyer's shipping address as part of the transaction information
	$collectShippingAddress = 0;

		if ($this->params->get('sandbox') == 1)
		{
			// Valid values are "sandbox" or "prod"
			$environment = "sandbox";
		}
		else
		{
			$environment = "prod";

			$formdata = ButtonGenerator::GenerateForm(
			$accessKey, $secretKey, $amount, $description,
			$referenceId, $immediateReturn, $returnUrl, $abandonUrl, $processImmediate,
			$ipnUrl, $collectShippingAddress, $signatureMethod, $environment
			);
		}
?>

<div class="akeeba-bootstrap">
	<form name = "amazon_payment" class = "form-horizontal"
	action = "<?php echo $formdata['endPoint'];?>"
	method = "<?php echo $formdata['httpmethod'];?>">

			<?php
			$form = '';

			foreach ($formdata['params']  as $name => $value)
			{
				$form .= "<input type=\"hidden\" name=\"$name";
				$form .= "\" value=\"$value";
				$form .= "\" >\n";
			}

			echo $form;
			?>
		<div align="center">	<input type="submit" class="btn btn-success" value="<?php echo JText::_('SUBMIT');?>" ></div>
	</form>
</div>

