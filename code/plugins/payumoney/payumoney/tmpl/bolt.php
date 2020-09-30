<?php
/**
 * @package     Joomla_Payments
 * @subpackage  PayuMoney
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$sandbox = $this->params->get('sandbox', 0);

if ($sandbox)
{
?>
	<script id="bolt" src="https://sboxcheckout-static.citruspay.com/bolt/run/bolt.min.js" bolt-color="e34524"></script>
<?php
}
else
{
?>
	<script id="bolt" src="https://checkout-static.citruspay.com/bolt/run/bolt.min.js" bolt-color="e34524"></script>
<?php
}

/** @var $vars stdClass */
$vars->item_name = Text::sprintf('PAYUMONEY_PINFO', $vars->order_id);
$txnid = $vars->order_id;

$posted = array();
$posted['key'] = trim($vars->key);
$posted['txnid'] = $txnid;
$posted['amount'] = $vars->amount;
$posted['productinfo'] = trim($vars->item_name);
$posted['firstname'] = isset($vars->user_firstname) ? trim($vars->user_firstname) : 'First_name';
$posted['email'] = $vars->user_email;
$posted['phone'] = $vars->phone;
$posted['curl'] = $vars->cancel_return;
$posted['surl'] = $vars->url;
$posted['furl'] = $vars->url;

$posted['udf1'] = $vars->order_id;
$posted['udf2'] = isset($vars->udf2) ? trim($vars->udf2) : '';
$posted['udf3'] = isset($vars->udf3) ? trim($vars->udf3) : '';
$posted['udf4'] = isset($vars->udf4) ? trim($vars->udf4) : '';

$posted['udf5'] = isset($vars->udf5) ? trim($vars->udf5) : '';
$posted['udf6'] = isset($vars->udf6) ? trim($vars->udf6) : '';
$posted['udf7'] = isset($vars->udf7) ? trim($vars->udf7) : '';
$posted['udf8'] = isset($vars->udf8) ? trim($vars->udf8) : '';
$posted['udf9'] = isset($vars->udf9) ? trim($vars->udf9) : '';
$posted['udf10'] = isset($vars->udf10) ? trim($vars->udf10) : '';

$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
$hashVarsSeq = explode('|', $hashSequence);
$hash_string = '';

foreach ($hashVarsSeq as $hash_var)
{
	$hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
	$hash_string .= '|';
}

$hash_string .= $vars->salt;
$hash = strtolower(hash('sha512', $hash_string));

?>
<div class="tjcpg-wrapper">
<form action="#" class="form-horizontal" method="post" id="payment_form">

	<!-- Mandatory Parameters -->
	<input type="hidden" name="key" id="key" value="<?php echo trim($posted['key']); ?>" />
	<input type="hidden" name="txnid" id="txnid" value="<?php echo $txnid; ?>" />
	<input type="hidden" name="amount" id="amount" value="<?php echo $posted['amount']; ?>" />
	<input type="hidden" name="productinfo" id="pinfo" value="<?php echo $posted['productinfo']; ?>" />
	<input type="hidden" name="Firstname" id="fname" value="<?php echo $posted['firstname']; ?>" />
	<input type="hidden" name="Email" id="email" value="<?php echo $posted['email']; ?>" />
	<input type="hidden" name="phone" id="mobile" value="<?php echo $posted['phone']; ?>" />
	<input type="hidden" name="surl" id="surl" value="<?php echo $posted['surl']; ?>" />
	<input type="hidden" name="furl" id="furl" value="<?php echo $posted['furl']; ?>" />

	<!-- Optional Parameters -->
	<input type="hidden" name="curl" id="curl" value="<?php echo $posted['curl']; ?>" />

	<!-- Mandatory Parameters -->
	<input type="hidden" name="udf1" id="udf1" value="<?php echo $posted['udf1']; ?>" />

	<!-- Optional Parameters -->
	<input type="hidden" name="udf2" id="udf2" value="<?php echo $posted['udf2']; ?>" />
	<input type="hidden" name="udf3" id="udf3" value="<?php echo $posted['udf3']; ?>" />
	<input type="hidden" name="udf4" id="udf4" value="<?php echo $posted['udf4']; ?>" />
	<input type="hidden" name="udf5" id="udf5" value="<?php echo $posted['udf5']; ?>" />
	<input type="hidden" name="udf6" id="udf6" value="<?php echo $posted['udf6']; ?>" />
	<input type="hidden" name="udf7" id="udf7" value="<?php echo $posted['udf7']; ?>" />
	<input type="hidden" name="udf8" id="udf8" value="<?php echo $posted['udf8']; ?>" />
	<input type="hidden" name="udf9" id="udf9" value="<?php echo $posted['udf9']; ?>" />
	<input type="hidden" name="udf10" id="udf10" value="<?php echo $posted['udf10']; ?>" />
	<input type="hidden" name="service_provider" id="service_provider" value="payu_paisa" size="64" />

	<input type="hidden" name="hash" id="hash" value="<?php echo $hash; ?>" />
	<div class="form-actions">
		<input type="submit" onclick="launchBOLT(); return false;" class="btn btn-success btn-large" border="0"  value="<?php echo Text::_('PAYUMONEY_SUBMIT'); ?>" alt="PayU India" />
	</div>
</form>
</div>

<script type="text/javascript">

function launchBOLT() {
    bolt.launch({
        key: jQuery('#key').val(),
        txnid: jQuery('#txnid').val(),
        amount: jQuery('#amount').val(),
        productinfo: jQuery('#pinfo').val(),
        firstname: jQuery('#fname').val(),
        email: jQuery('#email').val(),
        phone: jQuery('#mobile').val(),
        surl: jQuery('#surl').val(),
        furl: jQuery('#surl').val(),

        udf1: jQuery('#udf1').val(),
        udf2: jQuery('#udf2').val(),
        udf3: jQuery('#udf3').val(),
        udf4: jQuery('#udf4').val(),
        udf5: jQuery('#udf5').val(),
        udf6: jQuery('#udf6').val(),
        udf7: jQuery('#udf7').val(),
        udf8: jQuery('#udf8').val(),
        udf9: jQuery('#udf9').val(),
        udf10: jQuery('#udf10').val(),

        hash: jQuery('#hash').val(),
        mode: 'dropout'
    }, {
        responseHandler: function(BOLT) {

            console.log(BOLT);
            if (BOLT.response.txnStatus != 'CANCEL') {
                var fr = '<form action=\"' + jQuery('#surl').val() + '\" method=\"post\">' +
                    '<input type=\"hidden\" name=\"key\" value=\"' + BOLT.response.key + '\" />' +
                    '<input type=\"hidden\" name=\"txnid\" value=\"' + BOLT.response.txnid + '\" />' +
                    '<input type=\"hidden\" name=\"amount\" value=\"' + BOLT.response.amount + '\" />' +
                    '<input type=\"hidden\" name=\"productinfo\" value=\"' + BOLT.response.productinfo + '\" />' +
                    '<input type=\"hidden\" name=\"firstname\" value=\"' + BOLT.response.firstname + '\" />' +
                    '<input type=\"hidden\" name=\"email\" value=\"' + BOLT.response.email + '\" />' +
                    '<input type=\"hidden\" name=\"phone\" value=\"' + BOLT.response.phone + '\" />' +

                    '<input type=\"hidden\" name=\"udf1\" value=\"' + BOLT.response.udf1 + '\" />' +
                    '<input type=\"hidden\" name=\"udf2\" value=\"' + BOLT.response.udf2 + '\" />' +
                    '<input type=\"hidden\" name=\"udf3\" value=\"' + BOLT.response.udf3 + '\" />' +
                    '<input type=\"hidden\" name=\"udf4\" value=\"' + BOLT.response.udf4 + '\" />' +
                    '<input type=\"hidden\" name=\"udf5\" value=\"' + BOLT.response.udf5 + '\" />' +
                    '<input type=\"hidden\" name=\"udf6\" value=\"' + BOLT.response.udf6 + '\" />' +
                    '<input type=\"hidden\" name=\"udf7\" value=\"' + BOLT.response.udf7 + '\" />' +
                    '<input type=\"hidden\" name=\"udf8\" value=\"' + BOLT.response.udf8 + '\" />' +
                    '<input type=\"hidden\" name=\"udf9\" value=\"' + BOLT.response.udf9 + '\" />' +
                    '<input type=\"hidden\" name=\"udf10\" value=\"' + BOLT.response.udf10 + '\" />' +

                    '<input type=\"hidden\" name=\"mihpayid\" value=\"' + BOLT.response.mihpayid + '\" />' +
                    '<input type=\"hidden\" name=\"status\" value=\"' + BOLT.response.status + '\" />' +
                    '<input type=\"hidden\" name=\"hash\" value=\"' + BOLT.response.hash + '\" />' +
                    '</form>';
                var form = jQuery(fr);
                jQuery('body').append(form);
                form.submit();
            }
        },
        catchException: function(BOLT) {
            console.log(BOLT);
            alert(BOLT.message);
        }
    });
}
</script>
