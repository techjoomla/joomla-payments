<?php
defined( '_JEXEC' ) or die( ';)' );
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentOgoneHelper
{ 	
	
	//gets the ogone URL
	function buildOgoneUrl($secure = true)
	{
	
		
	}
	
	function Storelog($name,$logdata)
	{
		jimport('joomla.error.log');
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
	
	function validateIPN($data)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'ogone');
		$params=json_decode($plugin->params);
		
			if(JVERSION >='1.6.0')
			require_once(JPATH_SITE.'/plugins/payment/ogone/ogone/lib/Response.php');
			else
			require_once(JPATH_SITE.'/plugins/payment/ogone/lib/Response.php');
			$options = array('sha1OutPassPhrase' =>$params->secretkey,
			
			);  
			
			// Define array of values returned by Ogone
			// Parameters are validated and filtered automatically
			// so it is safe to specify a superglobal variable
			// like $_POST or $_GET if you don't want to
			// specify all parameters manually
			$params =$data;


			// Instantiate response
			$response = new Ogone_Response($options, $params);
			


			// Check if response by Ogone is valid
			// The SHA1Sign is calculated automatically and
			// verified with the SHA1Sign provided by Ogone
			if(!$response->isValid()) {
			return false;
			}
			return true;

	}
}
?>
