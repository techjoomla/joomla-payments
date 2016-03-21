<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined( '_JEXEC' ) or die( ';)' );
jimport('joomla.html.html');
jimport( 'joomla.plugin.helper' );
class plgPaymentGtpayHelper
{ 	
	//gets the GTPay URL
	function buildGtpayUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'gtpay');
		$params=json_decode($plugin->params);
		$url = 'https://ibank.gtbank.com/GTPay/Tranx.aspx';
		return $url;
	}
	
	//Convert Currency Symbol to Currency Code
	function currencyConvert()
	{
		$params=JComponentHelper::getParams('com_jgive');
		$currency = $params->get('currency_symbol');
		if($currency == '$') { $val = '844'; } 
		if($currency == '₦') { $val = '566'; }
		return $val;
	}	
	
	//generate SHA512 hash to send to GTPay
	function generateHash($tranx_id, $tranx_amt)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'gtpay');
		$params=json_decode($plugin->params);
		$tranx_amt = $tranx_amt * 100;
		$string = $tranx_id.$tranx_amt.$params->gtpay_tranx_noti_url.$params->gtpay_tranx_hash; 
		$shahash = hash('sha512', $string);
		return $shahash;
	}	
}
