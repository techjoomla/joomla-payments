<?php if (!defined('ALLOW_PAGSEGURO_CONFIG')) { die('No direct script access allowed'); }
/*
************************************************************************
PagSeguro Config File
************************************************************************
*/

$PagSeguroConfig = array();

$PagSeguroConfig['environment'] = Array();
$PagSeguroConfig['environment']['environment'] = "production";

$PagSeguroConfig['credentials'] = Array();
$PagSeguroConfig['credentials']['email'] = "paramirimeventos@gmail.com";
$PagSeguroConfig['credentials']['token'] = "718E910C1A8644B784336C605659D4A1";

$PagSeguroConfig['application'] = Array();
$PagSeguroConfig['application']['charset'] = "ISO-8859-1"; // UTF-8, ISO-8859-1

$PagSeguroConfig['log'] = Array();
$PagSeguroConfig['log']['active'] = FALSE;
$PagSeguroConfig['log']['fileLocation'] = "";

?>
