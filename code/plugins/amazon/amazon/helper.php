<?php
defined( '_JEXEC' ) or die( ';)' );
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentAmazonHelper
{ 	
	
	//gets the amazon URL
	function buildAmazonUrl($secure = true)
	{
		
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
	
	function validateIPN($data,$urlEndPoint)
	{
		if(JVERSION >='1.6.0')
			require_once(JPATH_SITE.'/plugins/payment/amazon/amazon/lib/IPNAndReturnURLValidation/src/SignatureUtilsForOutbound.php');
		else
			require_once(JPATH_SITE.'/plugins/payment/amazon/lib/IPNAndReturnURLValidation/src/SignatureUtilsForOutbound.php');
	
		$params["transactionId"] = $data['transactionId']; 
		$params["transactionDate"] = $data['transactionDate']; 
		$params["status"] = $data['status']; 
		$params["signatureMethod"] =$data['signatureMethod']; 
		$params["signatureVersion"] = $data['signatureVersion']; 
		$params["buyerEmail"] = $data['buyerEmail']; 
		$params["recipientEmail"] =$data['recipientEmail']; 
		$params["operation"] = $data['operation']; 
		$params["transactionAmount"] = $data['transactionAmount']; 
		$params["referenceId"] = $data['referenceId']; 
		$params["buyerName"] = $data['buyerName']; 
		$params["recipientName"] = $data['recipientName']; 
		$params["paymentMethod"] = $data['paymentMethod']; 
		$params["paymentReason"] = $data['paymentReason']; 
		$params["certificateUrl"] =$data['certificateUrl']; 
		$params["signature"] =$data['signature']; 
		
		$utils = new SignatureUtilsForOutbound(); 
        //IPN is sent as a http POST request and hence we specify POST as the http method.
        //Signature verification does not require your secret key
				try{
        $xml=$utils->validateRequest($params, $urlEndPoint, "POST") ;
        }

        catch(Exception $e){$data['error']=$error=$e;return false;}
      return  $result = (string) $xml->VerifySignatureResult->VerificationStatus;
        	
	 
	
	}
		
}
?>
