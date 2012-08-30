<?php
defined( '_JEXEC' ) or die( ';)' );
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentCcavenueHelper
{ 	
	function buildCcavenueUrl($secure = true)
	{
		//$url = $this->params->get('sandbox') ? 'test.payu.in/_payment' : 'secure.payu.in/_payment';
		if ($secure) {
			$url = 'https://www.ccavenue.com/shopzone/cc_details.jsp';
		}
		return $url;
	}
		
}
