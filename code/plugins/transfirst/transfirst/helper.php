<?php
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentTransfirstHelper
{ 	
	
	//gets the paypal URL
	function buildTransfirstUrl()
	{
		$plugin = JPluginHelper::getPlugin('payment', 'transfirst');
		$params=json_decode($plugin->params);
		$url = $params->sandbox ? 'https://ws.cert.processnow.com:443/portal/merchantframework/MerchantWebServices-v1?wsdl' : 'https://ws.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl';
		return $url;
	}
	
	function Storelog($name,$logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$path= dirname(__FILE__);
		$my = JFactory::getUser();     
	
		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'].'_'.$name.'.log',
				'text_entry_format' => $options ,
				'text_file_path' => $path
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user= $my->name.'('.$my->id.')';
		$logEntry->desc=json_encode($logdata['raw_data']);

		JLog::add($logEntry);
     
//		$logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
//    $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}
	
		
		
}
