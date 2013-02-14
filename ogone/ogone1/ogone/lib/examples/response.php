<?php
/**
 * Example to handle a response by Ogone
 * after an Ogone_Form was submitted
 * 
 * This code should be used in the script that is run at the url
 * that you specify as accepturl parameter when submitting the Ogone_Form
 * 
 * @author       Jurgen Van de Moere (http://www.jvandemo.com)
 * @copyright    JobberID (http://www.jobberid.com) * 
 */

// Include Ogone_Response class
require_once '../Ogone/Response.php';

// Define response options
// See Ogone_Response for list of supported options
$options = array(
    'sha1OutPassPhrase' => 'your_sha1_out_password'
);

// Define array of values returned by Ogone
// Parameters are validated and filtered automatically
// so it is safe to specify a superglobal variable
// like $_POST or $_GET if you don't want to
// specify all parameters manually
$params = $_POST;

// Instantiate response
$response = new Ogone_Response($options, $params);

// Check if response by Ogone is valid
// The SHA1Sign is calculated automatically and
// verified with the SHA1Sign provided by Ogone
if(! $response->isValid()) {
    // Reponse is not valid so handle accordingly
    exit('The response is not valid');
}

// Use the dump() method to dump the whole response
// if you need to investigate the response when debugging
$response->dump();

// Use the getParam() method to retrieve
// parameters returned by Ogone
$creditCard = $response->getParam('CreditCard');
$amount = $response->getParam('amount');

// Handle further processing of your website
// such as saving payment details to database
// or sending confirmation email to client

// ...


