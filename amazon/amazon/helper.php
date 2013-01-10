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
	
		$client='amazon';
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
