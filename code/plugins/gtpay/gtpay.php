<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

jimport( 'joomla.plugin.plugin' );

if (JVERSION >= '1.6.0')
	require_once(JPATH_SITE.'/plugins/payment/gtpay/gtpay/helper.php');
else
	require_once(JPATH_SITE.'/plugins/payment/gtpay/helper.php');

$lang =  Factory::getLanguage();
$lang->load('plg_payment_gtpay', JPATH_ADMINISTRATOR);

class plgPaymentGtpay extends CMSPlugin
{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$config = Factory::getConfig();
		$jinput = Factory::getApplication()->input;
		$status_code = $jinput->get('gtpay_tranx_status_code', null, null);
		$status_msg = $jinput->get('gtpay_tranx_status_msg', null, null);
		$db = Factory::getDBO();
		if($status_code == '00')
		{
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_('SUCCESSFUL_TRANSACTION'). ' '.$status_msg);
			$sql = "UPDATE #__jg_orders SET status = 'C' WHERE status = 'P' ORDER BY id DESC LIMIT 1";
			$db->setquery($sql);
			$db->Query();
		}
	}

	/* Internal use functions */
	function buildLayoutPath($layout) {
		$app = Factory::getApplication();
		$core_file 	= dirname(__FILE__). '/' . $this->_name . '/tmpl/default.php';
		$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . 'recurring.php';
		if(File::exists($override))
		{
			return $override;
		}
		else
		{
	  	return  $core_file;
	}
	}

	//Builds the layout to be shown, along with hidden fields.
	function buildLayout($vars, $layout = 'default' )
	{
		// Load the layout & push variables
		ob_start();
        $layout = $this->buildLayoutPath($layout);
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();
		return $html;
	}

	// Used to Build List of Payment Gateway in the respective Components
	function onTP_GetInfo($config)
	{
		if(!in_array($this->_name,$config))
		return;
		$obj 		= new stdClass;
		$obj->name 	=$this->params->get( 'plugin_name' );
		$obj->id	= $this->_name;
		return $obj;
	}

	//Constructs the Payment form in case of On Site Payment gateways like Auth.net & constructs the Submit button in case of offsite ones
	function onTP_GetHTML($vars)
	{
		$plgPaymentGtpayHelper=new plgPaymentGtpayHelper();
		$vars->action_url = $plgPaymentGtpayHelper->buildGtpayUrl();
		$session = Factory::getSession();
		$session->set('amount', $vars->amount);
		$session->set('email', $vars->paypal_email);
		//Take this receiver email address from plugin if component not provided it
		//if component does not provide cmd
		if(empty($vars->cmd))
			$vars->cmd='_xclick';
			$html = $this->buildLayout($vars);
		return $html;
	}
}
