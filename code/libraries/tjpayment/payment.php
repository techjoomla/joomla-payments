<?php
/**
 * @version     SVN: <svn_id>
 * @package     Techjoomla.Libraries
 * @subpackage  Payment
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

require_once JPATH_LIBRARIES . '/tjpayment/payment/vendor/autoload.php';

use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;

jimport('joomla.plugin.helper');
jimport('joomla.error.log');

/**
 * Techjoomla Payment class which use Omnipay payment for processing payment
 *
 * @since  1.0
 */
abstract class TjOmniPayPayment
{
	/**
	 * Name of the Omnipay payment package used for payment
	 *
	 */
	protected $omniPayGateWayName;

	/**
	 * Omnipay gateway object
	 *
	 */
	protected $gateway;

	/**
	 * Success payment page URL
	 *
	 */
	protected $paymentSuccessUrl;

	/**
	 * Failure payment page URL
	 *
	 */
	protected $paymentFailureUrl;

	/**
	 * URL to post IPN for the payment
	 *
	 */
	protected $paymentNotifyUrl;

	/**
	 * payment plugin parameters
	 *
	 */
	protected $paymentPluginParams = array();

	/**
	 * Constructor to initialise payment params
	 *
	 * @param   ARRAY  $data    Payment adapter data
	 * @param   ARRAY  $config  Default config
	 *
	 * @since  1.0
	 */
	public function __construct($data, $config)
	{
		$this->omniPayGateWayName = $data['omniPayGateWayName'];

		$this->paymentPluginParams = $this->setOmniPaymentParameter($data['paymentPluginName']); // create new fuctions so can be overridden
	}

	/**
	 * Set the Omnipay gateway used for the payment
	 *
	 * @param   STRING  $value  OmniPay payment gateway name
	 *
	 * @return  null
	 */
	protected function setOmniPayGateWayName($value)
	{
		$this->omniPayGateWayName = $value;
	}

	/**
	 * Set the Omnipay gateway used for the payment
	 *
	 * @param   STRING  $value  OmniPay payment gateway name
	 *
	 * @return  null
	 */
	protected function setOmniPaymentParameter($paymentPluginName)
	{
		$plugin = JPluginHelper::getPlugin('payment', $paymentPluginName);
		$params = new JRegistry($plugin->params);
		$config = $params->toArray();

		return $config;
	}

	/**
	 * Method to get Omnipay payment gateway object
	 *
	 * @return omnipay gateway object
	 */
	protected function getGateway()
	{
		if (is_null($this->gateway))
		{
			$gateway = Omnipay::create($this->omniPayGateWayName);

			$parameters = $gateway->getDefaultParameters();

			foreach ($parameters as $name => $value)
			{
				if ($name == 'developerMode')
				{
					$parameters[$name] = !(bool) $this->paymentPluginParams[$name];
				}

				if (isset($this->paymentPluginParams[$name]))
				{
					$parameters[$name] = $this->paymentPluginParams[$name];
				}
			}

			$gateway->initialize($parameters);

			$this->gateway = $gateway;
		}

		return $this->gateway;
	}

	/**
	 * Method to process the payment using OmniPay library
	 *
	 * @param   ARRAY   $post  post data
	 * @param   OBJECT  $vars  payment vars
	 *
	 * @return void
	 */
	public function processPayment($post, $vars)
	{
		$app = JFactory::getApplication();
		$gateway = $this->getGateway();

		$card = array();
		$card['amount'] = $post['amount'];
		$card['firstName'] = $post['cardfname'];
		$card['lastName'] = $post['cardlname'];

		$exp = explode("-", $post['cardexp']);

		$card['startMonth'] = "02";
		$card['startYear'] = "2010";
		$card['address1'] = $post['cardaddress1'];
		$card['address2'] = $post['cardaddress2'];
		$card['city'] = $post['cardcity'];
		$card['postcode'] = $post['cardzip'];
		$card['state'] = "maharashtra";
		$card['country'] = "india";
		$card['phone'] = $vars->userInfo['phone'];
		$card['email'] = $vars->userInfo['email'];
		$card['number'] = $post['cardnum'];
		$card['expiryMonth'] = $exp[0];
		$card['expiryYear'] = $exp[1];
		$card['cvv'] = $post['cardcvv'];

		$cardData = new CreditCard($card);

		// Register payment success and payment failure URL
		$this->paymentSuccessUrl = $vars->return;
		$this->paymentFailureUrl = $vars->cancel_return;

		try
		{
			$request = $gateway->purchase(
			array(
			'amount' => $post['amount'], 'currency' => $vars->currency_code,
			'card' => $cardData, 'returnUrl' => $vars->return, 'cancelUrl' => $vars->cancel_return,
			'notifyUrl' => $vars->notify_url, 'description' => 'description')
			);

			$request->setTransactionId($vars->order_id);
			$request->setTransactionReference($vars->order_id);

			$response = $request->send();
		}
		catch (\Exception $e)
		{
			$app->redirect($this->paymentFailureUrl);
		}

		if ($response->isSuccessful())
		{
			// Payment success - for onsite payment gateways
			$response = $this->translateResponse($response, $post);

			return $response;
		}
		elseif ($response->isRedirect())
		{
			// For off-site payment gateways
			if ($response->getRedirectMethod() == 'GET')
			{
				$app->redirect($response->getRedirectUrl());
			}
			else
			{
				$redirectUrl = $response->getRedirectUrl();
				$data        = $response->getRedirectData();
				$this->renderRedirectForm($redirectUrl, $data);
				jexit();
			}
		}
		else
		{
			// Payment failure
			$app->redirect($this->paymentFailureUrl);
		}
	}

	/**
	 * Method used for offsite payment gateways which accepts data from post
	 * Render form which will redirect users to payment gateway for processing payment
	 *
	 * @param   string  $url   The payment gateway URL which users will be redirected to
	 * @param   array   $data  data to be posted
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected function renderRedirectForm($url = null, $data = array())
	{
	?>
		<div class="payment-heading"><?php echo "Processing your payment"; ?></div>
		<form method="post" action="<?php echo $url; ?>" name="payment_form" id="payment_form">
			<?php
			foreach ($data as $key => $val)
			{
				echo '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
				echo "\n";
			}
			?>
			<script type="text/javascript">
				function redirect() {
					document.payment_form.submit();
				}
				setTimeout('redirect()', 3000);
			</script>
		</form>
	<?php
	}
}
