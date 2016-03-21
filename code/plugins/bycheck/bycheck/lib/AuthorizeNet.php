<?php
/**
 * The AuthorizeNet PHP SDK. Include this file in your project.
 *
 * @package AuthorizeNet
 */

require dirname(__FILE__) . '/shared/AuthorizeNetRequest.php';
require dirname(__FILE__) . '/shared/AuthorizeNetTypes.php';
require dirname(__FILE__) . '/shared/AuthorizeNetXMLResponse.php';
require dirname(__FILE__) . '/shared/AuthorizeNetResponse.php';
require dirname(__FILE__) . '/AuthorizeNetAIM.php';
require dirname(__FILE__) . '/AuthorizeNetARB.php';
require dirname(__FILE__) . '/AuthorizeNetCIM.php';
require dirname(__FILE__) . '/AuthorizeNetSIM.php';
require dirname(__FILE__) . '/AuthorizeNetDPM.php';
require dirname(__FILE__) . '/AuthorizeNetTD.php';
require dirname(__FILE__) . '/AuthorizeNetCP.php';

if (class_exists("SoapClient")) {
    require dirname(__FILE__) . '/AuthorizeNetSOAP.php';
}
/**
 * Exception class for AuthorizeNet PHP SDK.
 *
 * @package AuthorizeNet
 */
class AuthorizeNetException extends Exception
{
}
