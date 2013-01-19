<?php
/**
 * @package Social Ads
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	defined('_JEXEC') or die('Restricted access');
	if(JVERSION >='1.6.0')
		require_once(JPATH_SITE.'/plugins/payment/ogone/ogone/lib/Form.php');
	else
		require_once(JPATH_SITE.'/plugins/payment/ogone/lib/Form.php');
			// Define form options
			// See Ogone_Form for list of supported options
			if($this->params->get('sandbox')==1)
			$environment=Ogone_Form::OGONE_TEST_URL; 					//Valid values are "sandbox" or "prod"
			else
			$environment=Ogone_Form::OGONE_PRODUCTION_URL;				
			$options = array(
    'sha1InPassPhrase' => $this->params->get('secretkey'),			//Put  your Secret Key here'abcdefghijklmopqrs1234$',
    'formAction'       => $environment,
		);


		$amount=$vars->currency_code." ".$vars->amount; 						//Enter the amount you want to collect for the item

		$description=$vars->item_name;					 //Enter a description of the item
		$referenceId=$vars->order_id; 				 //Optionally, enter an ID that uniquely identifies this transaction for your records
		$abandonUrl=$vars->cancel_return;		 //Optionally, enter the URL where senders should be redirected if they cancel their transaction
		$returnUrl=$vars->notify_url;		 				//Optionally enter the URL where buyers should be redirected after they complete the transaction
		$immediateReturn="0"; 						 //Optionally, enter "1" if you want to skip the final status page in Amazon Payments
		$processImmediate="1"; 						 //Optionally, enter "1" if you want to settle the transaction immediately else "0". Default value is "1" 
		$ipnUrl=$vars->notify_url;				 //Optionally, type the URL of your host page to which Amazon Payments should send the IPN transaction information.
		$collectShippingAddress=0;	
		//PARAMPLUS

		
			

		

// Define form parameters (see Ogone documentation for list)
// Default parameter values can be set in Ogone_Form if required
$params = array(
    'PSPID' => $this->params->get('accesskey'),//your_ogone_pspid
    'orderID' => $vars->order_id,
    'amount' => $vars->amount,
    'CN'=>$vars->user_email,
    'CN'=>$vars->user_email,
    'currency' =>$vars->currency_code,
    'language' => 'en',
    'accepturl' => $vars->return,    
    'cancelurl' => $vars->cancel_return,

);

// Instantiate form
$form = new Ogone_Form($options, $params);

// You can also add parameters after instantiation
// with the addParam() method


// Automatically generate HTML form with all params and SHA1Sign
echo $form->render();
?>
