<?php
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentLinkpointHelper
{ 	
	
	//gets the Linkpoint URL
	function buildLinkpointUrl()
	{
			$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'staging.linkpt.net' : 'secure.linkpt.net';
		if ($secure_post) 
			$url = $url;
		else
			$url = $url;		
		return $url;	
	}
	
	function Storelog($name,$logdata)
	{

			jimport('joomla.error.log');
    $options = array('format' => "{DATE}\t{TIME}\t{USER}\t{DESC}");
		if(JVERSION >='1.6.0')
		$path=JPATH_SITE.'/plugins/payment/'.$name.'/'.$name.'/';
		else
		$path=JPATH_SITE.'/plugins/payment/'.$name.'/';	  
	  $my = &JFactory::getUser();        
		$logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
    $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
    

	}
	
	function log_ipn_results($success) {
       
      if (!$this->ipn_log) return; 
      
      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '; 
      
      // Success or failure being logged?
      if ($success) $text .= "SUCCESS!\n";
      else $text .= 'FAIL: '.$this->last_error."\n";
      
      // Log the POST variables
      $text .= "IPN POST Vars from Linkpoint:\n";
      foreach ($this->ipn_data as $key=>$value) {
         $text .= "$key=$value, ";
      }
 
      // Log the response from the Linkpoint server
      $text .= "\nIPN Response from Linkpoint Server:\n ".$this->ipn_response;
      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n");
      fclose($fp);  // close file
   }

}
