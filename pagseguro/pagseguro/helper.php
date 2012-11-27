<?php
	jimport('joomla.html.html');
	jimport( 'joomla.plugin.helper' );
class plgPaymentPagseguroHelper
{ 	
	
	//gets the Pagseguro URL
	function buildPagseguroUrl($vars,$secure = true)
	{
		
	// Instantiate a new payment request
		$paymentRequest = new PagSeguroPaymentRequest();
		
		// Sets the currency
		$paymentRequest->setCurrency("BRL");
		if(empty($vars->item_code))
		$vars->item_code='0001';
		
		if(empty($vars->item_quantity))
		$vars->item_quantity='1';
		
		//format amount to proper format
		$vars->amount=number_format($vars->amount,2,".",".");
		if($vars->amount<100)
		{
			if($vars->amount<10)
			$vars->amount='00'.$vars->amount;
			else
			$vars->amount='0'.$vars->amount;
		
		}

		// Add an item for this payment request
		$paymentRequest->addItem($vars->item_code, $vars->item_name,$vars->item_quantity,$vars->amount);

		
	//	$paymentRequest->addItem('0001', 'Notebook prata', 1,002);
		
		// Add another item for this payment request
	//	$paymentRequest->addItem('0002', 'Notebook rosa',  1,002);
		
		// Sets a reference code for this payment request, it is useful to identify this payment in future notifications.
		$paymentRequest->setReference($vars->order_id);
		
		// Sets shipping information for this payment request
		//$CODIGO_SEDEX = PagSeguroShippingType::getCodeByType('SEDEX');
		//$paymentRequest->setShippingType($CODIGO_SEDEX);
		//$paymentRequest->setShippingAddress('',  'Av. Brig. Faria Lima',  '1384', 'apto. 114', 'Jardim Paulistano', 'São Paulo', 'SP', 'BRA');
		
		// Sets your customer information.
		//$paymentRequest->setSender('João Comprador', 'paramirimeventos@gmail.com', '11', '56273440');
		
		$paymentRequest->setRedirectUrl($vars->return);
		
		try {
			
			/*
			* #### Crendencials ##### 
			* Substitute the parameters below with your credentials (e-mail and token)
			* You can also get your credentails from a config file. See an example:
			* $credentials = PagSeguroConfig::getAccountCredentials();
			*/			
			//$credentials = new PagSeguroAccountCredentials("your@email.com", "your_token_here");

			
			$credentials = new PagSeguroAccountCredentials($vars->sellar_email, $vars->token);
			
			// Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
			$url = $paymentRequest->register($credentials);
			

			
		} catch (PagSeguroServiceException $e) {
			die($e->getMessage());
		
	}
	
	/*if ($secure) {
			$url = 'https://' . $url;
		}*/
		
		return $url;
	}
	
	function validateIPN($data,$vars)
	{
		$notificationCode=$code=$data->get('notificationCode');
		$type=$data->get('notificationType');

		if(empty($notificationCode))
		$code=$data['notificationCode'];

		if(empty($notificationType))
		$type=$data['notificationType'];
			
    	if ( $code && $type ) {
			
    		$notificationType = new PagSeguroNotificationType($type);
    		$strType = $notificationType->getTypeFromValue();
			
			switch($strType) {
				
				case 'TRANSACTION':
						$credentials = new PagSeguroAccountCredentials($vars->sellar_email,$vars->token);
						$credentials = PagSeguroConfig::getAccountCredentials();
						try {
							$transaction = PagSeguroNotificationService::checkTransaction($credentials, $notificationCode);
						} catch (PagSeguroServiceException $e) {
							$error=array();
							$error['code']	=''; //@TODO change these $data indexes afterwards
							$error['desc']	=$e->getMessage();
						}
					break;
				
				default:
				$error=array();
				$error['code']	=''; //@TODO change these $data indexes afterwards
				$error['desc']	="Unknown notification type [".$notificationType->getValue()."]";
				break;
					
			}


			
		} else {
		$error=array();
		$error['code']	=''; //@TODO change these $data indexes afterwards
		$error['desc']	='Unknown notification type';
			
			}
			
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
	
	function validateIPN()
	{
	
	}

}
