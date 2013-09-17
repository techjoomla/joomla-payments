<?php
/**
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die('Restricted access');

	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
	jimport('joomla.html.parameter');
class plgPaymentPayfastHelper
{ 	
	
	//gets the Payfast URL
	function buildPayfastUrl($secure = true)
	{
		$plugin = JPluginHelper::getPlugin('payment', 'payfast');
		$params=json_decode($plugin->params);
		$sandbox=$params->sandbox;
		if(!empty($sandbox)) {
			// SANDBOX MODE == ON
			$url =  'sandbox.payfast.co.za/eng/process';
		} else {
			$url =  'www.payfast.co.za/eng/process';
		}
		if ($secure) {
			$url = 'https://' . $url;
		}
		return $url;
	}
	
	
	function Storelog($name,$logdata)
	{
		$person=json_encode($logdata);
		file_put_contents('payfast_storelog.txt'," \n inside helper Storelog() \n ". $person, FILE_APPEND | LOCK_EX);
			
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		//if(JVERSION >='1.6.0')
		if(version_compare(JVERSION, '1.6', 'ge'))
			$path=JPATH_SITE.'/plugins/payment/'.$name.'/'.$name.'/';
		else
			$path=JPATH_SITE.'/plugins/payment/'.$name.'/';	  
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


		$person=json_encode($logEntry);
		file_put_contents('payfast_storelog.txt'," \n BEFORE LOGGING  \n ". $person, FILE_APPEND | LOCK_EX);
		JLog::add($logEntry);
//		$logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);
//    $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));

	}	

}
