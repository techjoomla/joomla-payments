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
	
		$client='ogone';
		jimport('joomla.error.log');
    $options = array('format' => "{DATE}\t{TIME}\t{USER}\t{DESC}");
		if(JVERSION >='1.6.0')
		$path=JPATH_SITE.'/plugins/payment/'.$name.'/'.$name.'/';
		else
		$path=JPATH_SITE.'/plugins/payment/'.$name.'/';	  
	  $my = &JFactory::getUser();        
		$logs = &JLog::getInstance($client.'_'.$name.'.log',$options,$path);
    $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata)));

	}
	function validateIPN($data)
	{
		
			if(JVERSION >='1.6.0')
			require_once(JPATH_SITE.'/plugins/payment/ogone/ogone/lib/Response.php');
			else
			require_once(JPATH_SITE.'/plugins/payment/ogone/lib/Response.php');
			$options = array('sha1OutPassPhrase' =>$this->params->get('secretkey'),
			
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
			return true;
			}
			else
			return false;

	}
}
?>
