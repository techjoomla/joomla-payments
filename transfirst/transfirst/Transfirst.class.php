
<?php

/**
 *
 * Generated Proxy Class : TransfirstClass (to interact with SOAP server at https://ws.cert.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl)
 * @package Transfirst
 * @version 1.50
 * @author www.ApiGenerator.com - Copyright (c) 2013. All rights reserved.
 *
 * We take no responsibility for the accuracy of this generated code. Use or edit at your own risk.
 *
 */

class Transfirst {

var $client = null;
var $options = array();
var $soapUrl = 'https://ws.cert.processnow.com/portal/merchantframework/MerchantWebServices-v1?wsdl';
/**
 *
 * Class: Transfirst - Construct Method
 *
 */

function __construct($url)
{
$this->client = new SoapClient($this->soapUrl=$url, $this->options);
//Insert Additional Constructor Code
}

/**
 *
 * Class: Transfirst - Destruct Method
 *
 */

function __destruct()
{
unset ($this->client);
//Insert Destructor Code
}



function SendTran($parameters ){
	try {
		$funcRet = $this->client->SendTran($parameters );
	} catch ( Exception $e ) {
		echo '(SendTran) SOAP Error: - ' . $e->getMessage ();
	}
	return $funcRet;
}



function SettleTran($parameters ){
	try {
		$funcRet = $this->client->SettleTran($parameters );
	} catch ( Exception $e ) {
		echo '(SettleTran) SOAP Error: - ' . $e->getMessage ();
	}
	return $funcRet;
}



function UpdtRecurrProf($parameters ){
	try {
		$funcRet = $this->client->UpdtRecurrProf($parameters );
	} catch ( Exception $e ) {
		echo '(UpdtRecurrProf) SOAP Error: - ' . $e->getMessage ();
	}
	return $funcRet;
}



function FndRecurrProf($parameters ){
	try {
		$funcRet = $this->client->FndRecurrProf($parameters );
	} catch ( Exception $e ) {
		echo '(FndRecurrProf) SOAP Error: - ' . $e->getMessage ();
	}
	return $funcRet;
}

}

?>
