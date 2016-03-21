<?php
/**
 * Example to generate an Ogone payment form
 * to initiate Ogone payments from your website
 * 
 * @author       Jurgen Van de Moere (http://www.jvandemo.com)
 * @copyright    JobberID (http://www.jobberid.com) * 
 */

// Include Ogone_Form class
require_once '../Ogone/Form.php';

// Define form options
// See Ogone_Form for list of supported options
$options = array(
    'sha1InPassPhrase' => 'abcdefghijklmopqrs1234$',
    'formAction'       => Ogone_Form::OGONE_TEST_URL,
);

// Define form parameters (see Ogone documentation for list)
// Default parameter values can be set in Ogone_Form if required
$params = array(
    'PSPID' => 'sagarch',//your_ogone_pspid
    'orderID' => rand(5000,10000),
    'amount' => 1,
    'currency' => 'EUR',
    'language' => 'en',
    'accepturl' => 'http://202.88.154.166/~sagar/playground/ogone/examples/return.php',
    'component_nm' => 'com_jticketing',
    'declineurl' => 'http://202.88.154.166/~sagar/playground/ogone/examples/return.php',
    'exceptionurl' => 'http://202.88.154.166/~sagar/playground/ogone/examples/return.php',
    'posturl' => 'http://202.88.154.166/~sagar/playground/ogone/examples/ipn1.php',
     'cancelurl' => 'http://202.88.154.166/~sagar/playground/ogone/examples/return.php',
     'component_nm' => 'com_jticketing',
     'controller_nm' => 'payment',
     'task_nm' => 'processpayment',
);

// Instantiate form
$form = new Ogone_Form($options, $params);

// You can also add parameters after instantiation
// with the addParam() method


// Automatically generate HTML form with all params and SHA1Sign
echo $form->render();

