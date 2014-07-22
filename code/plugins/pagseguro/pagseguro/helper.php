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
		$paymentRequest->setCurrency($vars->currency_code);
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
		if(is_array($data))
		{
			$code=$data['notificationCode'];
			$type=$data['notificationType'];
		}
		else if(is_object($data))
		{
			$code=$data->get('notificationCode');
			$type=$data->get('notificationType');
		
		}
			
    	
    	if ( $code && $type ) {
			
    		$notificationType = new PagSeguroNotificationType($type);
    		$strType = $notificationType->getTypeFromValue();
			
			switch($strType) {
				
				case 'TRANSACTION':
						$credentials = new PagSeguroAccountCredentials($vars->sellar_email,$vars->token);

						try {
							$transaction = PagSeguroNotificationService::checkTransaction($credentials, $code);
							$returndata['transaction_id']=$transaction->getCode(); 
							$returndata['payment_status']=$transaction->getStatus()->getTypeFromValue();
							$returndata['payment_statuscode']=$transaction->getStatus()->getValue();
							$returndata['order_id']=$transaction->getReference();
							$returndata['buyer_email']=$transaction->getSender()->getEmail();
							$returndata['payment_method']=$transaction->getpaymentMethod()->gettype()->getTypeFromValue();
							$returndata['total_paid_amt']=$transaction->getgrossAmount();
							$returndata['raw_data']=json_encode($returndata);							
							return $returndata;
						} catch (PagSeguroServiceException $e) {
							$error=array();
							$error['code']	=''; //@TODO change these $data indexes afterwards
							$error['desc']	=$e->getMessage();
							return $error;
						}
					break;
				
				default:
				$error=array();
				$error['code']	=''; //@TODO change these $data indexes afterwards
				$error['desc']	="Unknown notification type [".$notificationType->getValue()."]";
				
				return $error;
				break;
					
			}


			
		} else {
		$error=array();
		$error['code']	=''; //@TODO change these $data indexes afterwards
		$error['desc']	='Unknown notification type';
						return $error;
			
			}
			
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
