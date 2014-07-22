<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined( '_JEXEC' ) or die( ';)' );
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentLinkpointHelper
{ 	
	
	//gets the Linkpoint URL
	function buildLinkpointUrl()
	{
		$plugin = JPluginHelper::getPlugin('payment', 'payu');
		$params=json_decode($plugin->params);
		$secure_post = $params->secure_post;
		$url = $params->sandbox ? 'staging.linkpt.net' : 'secure.linkpt.net';
		/*$secure_post = $this->params->get('secure_post');
		$url = $this->params->get('sandbox') ? 'staging.linkpt.net' : 'secure.linkpt.net';*/
		if ($secure_post) 
			$url = $url;
		else
			$url = $url;		
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
