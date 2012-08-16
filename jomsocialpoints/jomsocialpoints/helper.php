<?php
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentJomsocialpointsHelper
{ 	
	
	//gets the paypal URL
	function buildPaypalUrl($secure = true)
	{
		$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		if ($secure_post) 
			$url = 'https://' . $url . '/cgi-bin/webscr';
		else
			$url = 'http://' . $url . '/cgi-bin/webscr';
		
		return $url;
	}
	
	function Storelog($name,$data)
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
		function validateIPN($data,$secret)
	{

	  $incoming_md5 = strtoupper($data['md5_hash']);
		$calculated_md5 = md5(
			$data['sale_id'].
			$data['vendor_id'].
			$data['invoice_id'].
			$secret
		);
		$calculated_md5 = strtoupper($calculated_md5);
		
		return ($calculated_md5 == $incoming_md5);
	
	}
		function log_ipn_results($success) {
       
      if (!$this->ipn_log) return; 
      
      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '; 
      
      // Success or failure being logged?
      if ($success) $text .= "SUCCESS!\n";
      else $text .= 'FAIL: '.$this->last_error."\n";
      
      // Log the POST variables
      $text .= "IPN POST Vars from Paypal:\n";
      foreach ($this->ipn_data as $key=>$value) {
         $text .= "$key=$value, ";
      }
 
      // Log the response from the paypal server
      $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;
      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n");
      fclose($fp);  // close file
   }

}
