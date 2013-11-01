<?php 

/**
 * @category     Ogone
 * @package      Ogone_Form
 * @author       Jurgen Van de Moere (http://www.jvandemo.com)
 * @copyright    JobberID (http://www.jobberid.com)
 * @license      http://framework.zend.com/license/new-bsd New BSD License
 */

class Ogone_Form
{
    
    /**
     * Array of parameters (any values specified here will be used as default, but can be overridden by constructor or addParam())
     * 
     * @var array
     */
    protected $_params = array(
    	'PSPID'               => 'your PSPID', // Use your Ogone PSPID
        'orderID'             => 'your unique order id', // Supply a unique order ID 
        'amount'              => 0,
        'currency'            => 'EUR',
        'language'            => 'en',
        'CN'                  => 'name of your client',
        'EMAIL'               => 'email of your client',
        'ownerZIP'            => '',
        'owneraddress'        => '',
        'ownercty'            => '',
        'ownertown'           => '',
        'ownertelno'          => '',
        'accepturl'           => 'the url you want to go to if the transaction is accepted',
        'declineurl'          => 'the url you want to go to if the transaction is declined',
        'exceptionurl'        => 'the url you want to go to if an exception occurs',
        'cancelurl'			  => 'the url you want to go to if the transaction is cancelled',
    );    
    
    /**
     * Configuration (any values specified here will be used as default, but can be overridden by constructor)
     * 
     * @var array
     */
    protected $_config = array(
        // SHA1 details
        'sha1InPassPhrase'	        => '',
    
        // Form html details
        'formId'				    => 'ogoneForm', // ID of the form
        'formName'				    => 'ogoneForm', // name of the form  
    	'formAction'				=> self::OGONE_TEST_URL, // Action url of the form (test or production url)
        'formMethod'				=> 'POST', // Method of the form
        'formClass'				    => 'ogoneForm', // Class of the form
    
        // Form elements
    	'formElementClass'		    => 'ogoneFormElement', // Class of the form elements
    
        // Submit button details
        'formSubmitButtonValue'		=> 'Pay', // Value of the submit button
        'formSubmitButtonClass'		=> 'ogoneSubmitButton' // Class of the submit button
    );
    
    /***********************************************
     *  DO NOT CHANGE ANYTHING BELOW THIS LINE !!!
     **********************************************/
    
    /**
     * Array of valid parameter names
     * 
     * @var array
     */
    protected $_validParamNames = array(
        'ACCEPTANCE',
        'ACCEPTURL',
        'ADDMATCH',
        'ADDRMATCH',
        'AIAGIATA',
        'AIAIRNAME',
        'AIAIRTAX',
        'AIBOOKIND*XX*',
        'AICARRIER*XX*',
        'AICHDET',
        'AICLASS*XX*',
        'AICONJTIv',
        'AIDEPTCODE',
        'AIDESTCITY*XX*',
        'AIDESTCITYL*XX*',
        'AIEXTRAPASNAME*XX*',
        'AIEYCD',
        'AIFLDATE*XX*',
        'AIFLNUM*XX*',
        'AIGLNUM',
        'AIINVOICE',
        'AIIRST',
        'AIORCITY*XX*',
        'AIORCITYL*XX*',
        'AIPASNAME',
        'AIPROJNUM',
        'AISTOPOV*XX*',
        'AITIDATE',
        'AITINUM',
        'AITINUML*XX*',
        'AITYPCH',
        'AIVATAMNT',
        'AIVATAPPL',
        'ALIAS',
        'ALIASOPERATION',
        'ALIASUSAGE',
        'ALLOWCORRECTION',
        'AMOUNT',
        'AMOUNT*XX*',
        'AMOUNTHTVA',
        'AMOUNTTVA',
        'BACKURL',
        'BATCHID',
        'BGCOLOR',
        'BLVERNUM',
        'BRAND',
        'BRANDVISUAL',
        'BUTTONBGCOLOR',
        'BUTTONTXTCOLOR',
        'CANCELURL',
        'CARDNO',
        'CATALOGURL',
        'CAVV_3D',
        'CAVVALGORITHM_3D',
        'CERTID',
        'CHECK_AAV',
        'CIVILITY',
        'CN',
        'COM',
        'COMPLUS',
        'COSTCENTER',
        'COSTCODE',
        'CREDITCODE',
        'CUID',
        'CURRENCY',
        'CVC',
        'CVCFLAG',
        'DATA',
        'DATATYPE',
        'DATEIN',
        'DATEOUT',
        'DECLINEURL',
        'DEVICE',
        'DISCOUNTRATE',
        'DISPLAYMODE',
        'ECI',
        'ECI_3D',
        'ECOM_BILLTO_POSTAL_CITY',
        'ECOM_BILLTO_POSTAL_COUNTRYCODE',
        'ECOM_BILLTO_POSTAL_NAME_FIRST',
        'ECOM_BILLTO_POSTAL_NAME_LAST',
        'ECOM_BILLTO_POSTAL_POSTALCODE',
        'ECOM_BILLTO_POSTAL_STREET_LINE1',
        'ECOM_BILLTO_POSTAL_STREET_LINE2',
        'ECOM_BILLTO_POSTAL_STREET_NUMBER',
        'ECOM_CONSUMERID',
        'ECOM_CONSUMER_GENDER',
        'ECOM_CONSUMEROGID',
        'ECOM_CONSUMERORDERID',
        'ECOM_CONSUMERUSERALIAS',
        'ECOM_CONSUMERUSERPWD',
        'ECOM_CONSUMERUSERID',
        'ECOM_PAYMENT_CARD_EXPDATE_MONTH',
        'ECOM_PAYMENT_CARD_EXPDATE_YEAR',
        'ECOM_PAYMENT_CARD_NAME',
        'ECOM_PAYMENT_CARD_VERIFICATION',
        'ECOM_SHIPTO_COMPANY',
        'ECOM_SHIPTO_DOB',
        'ECOM_SHIPTO_ONLINE_EMAIL',
        'ECOM_SHIPTO_POSTAL_CITY',
        'ECOM_SHIPTO_POSTAL_COUNTRYCODE',
        'ECOM_SHIPTO_POSTAL_NAME_FIRST',
        'ECOM_SHIPTO_POSTAL_NAME_LAST',
        'ECOM_SHIPTO_POSTAL_NAME_PREFIX',
        'ECOM_SHIPTO_POSTAL_POSTALCODE',
        'ECOM_SHIPTO_POSTAL_STREET_LINE1',
        'ECOM_SHIPTO_POSTAL_STREET_LINE2',
        'ECOM_SHIPTO_POSTAL_STREET_NUMBER',
        'ECOM_SHIPTO_TELECOM_FAX_NUMBER',
        'ECOM_SHIPTO_TELECOM_PHONE_NUMBER',
        'ECOM_SHIPTO_TVA',
        'ED',
        'EMAIL',
        'EXCEPTIONURL',
        'EXCLPMLIST',
        'EXECUTIONDATE*XX*',
        'FACEXCL*XX*',
        'FACTOTAL*XX*',
        'FIRSTCALL',
        'FLAG3D',
        'FONTTYPE',
        'FORCECODE1',
        'FORCECODE2',
        'FORCECODEHASH',
        'FORCEPROCESS',
        'FORCETP',
        'GENERIC_BL',
        'GIROPAY_ACCOUNT_NUMBER',
        'GIROPAY_BLZ',
        'GIROPAY_OWNER_NAME',
        'GLOBORDERID',
        'GUID',
        'HDFONTTYPE',
        'HDTBLBGCOLOR',
        'HDTBLTXTCOLOR',
        'HEIGHTFRAME',
        'HOMEURL',
        'HTTP_ACCEPT',
        'HTTP_USER_AGENT',
        'INCLUDE_BIN',
        'INCLUDE_COUNTRIES',
        'INVDATE',
        'INVDISCOUNT',
        'INVLEVEL',
        'INVORDERID',
        'ISSUERID',
        'IST_MOBILE',
        'ITEM_COUNT',
        'ITEMATTRIBUTES*XX*',
        'ITEMCATEGORY*XX*',
        'ITEMCOMMENTS*XX*',
        'ITEMDESC*XX*',
        'ITEMDISCOUNT*XX*',
        'ITEMID*XX*',
        'ITEMNAME*XX*',
        'ITEMPRICE*XX*',
        'ITEMQUANT*XX*',
        'ITEMQUANTORIG*XX*',
        'ITEMUNITOFMEASURE*XX*',
        'ITEMVAT*XX*',
        'ITEMVATCODE*XX*',
        'ITEMWEIGHT*XX*',
        'LANGUAGE',
        'LEVEL1AUTHCPC',
        'LIDEXCL*XX*',
        'LIMITCLIENTSCRIPTUSAGE',
        'LINE_REF',
        'LINE_REF1',
        'LINE_REF2',
        'LINE_REF3',
        'LINE_REF4',
        'LINE_REF5',
        'LINE_REF6',
        'LIST_BIN',
        'LIST_COUNTRIES',
        'LOGO',
        'MAXITEMQUANT*XX*',
        'MERCHANTID',
        'MODE',
        'MTIME',
        'MVER',
        'NETAMOUNT',
        'OPERATION',
        'ORDERID',
        'ORDERSHIPCOST',
        'ORDERSHIPMETH',
        'ORDERSHIPTAX',
        'ORDERSHIPTAXCODE',
        'ORIG',
        'OR_INVORDERID',
        'OR_ORDERID',
        'OWNERADDRESS',
        'OWNERADDRESS2',
        'OWNERCTY',
        'OWNERTELNO',
        'OWNERTELNO2',
        'OWNERTOWN',
        'OWNERZIP',
        'PAIDAMOUNT',
        'PARAMPLUS',
        'PARAMVAR',
        'PAYID',
        'PAYMETHOD',
        'PM',
        'PMLIST',
        'PMLISTPMLISTTYPE',
        'PMLISTTYPE',
        'PMLISTTYPEPMLIST',
        'PMTYPE',
        'POPUP',
        'POST',
        'PSPID',
        'PSWD',
        'REF',
        'REFER',
        'REFID',
        'REFKIND',
        'REF_CUSTOMERID',
        'REF_CUSTOMERREF',
        'REGISTRED',
        'REMOTE_ADDR',
        'REQGENFIELDS',
        'RTIMEOUT',
        'RTIMEOUTREQUESTEDTIMEOUT',
        'SCORINGCLIENT',
        'SETT_BATCH',
        'SID',
        'STATUS_3D',
        'SUBSCRIPTION_ID',
        'SUB_AM',
        'SUB_AMOUNT',
        'SUB_COM',
        'SUB_COMMENT',
        'SUB_CUR',
        'SUB_ENDDATE',
        'SUB_ORDERID',
        'SUB_PERIOD_MOMENT',
        'SUB_PERIOD_MOMENT_M',
        'SUB_PERIOD_MOMENT_WW',
        'SUB_PERIOD_NUMBER',
        'SUB_PERIOD_NUMBER_D',
        'SUB_PERIOD_NUMBER_M',
        'SUB_PERIOD_NUMBER_WW',
        'SUB_PERIOD_UNIT',
        'SUB_STARTDATE',
        'SUB_STATUS',
        'TAAL',
        'TAXINCLUDED*XX*',
        'TBLBGCOLOR',
        'TBLTXTCOLOR',
        'TID',
        'TITLE',
        'TOTALAMOUNT',
        'TP',
        'TRACK2',
        'TXTBADDR2',
        'TXTCOLOR',
        'TXTOKEN',
        'TXTOKENTXTOKENPAYPAL',
        'TYPE_COUNTRY',
        'UCAF_AUTHENTICATION_DATA',
        'UCAF_PAYMENT_CARD_CVC2',
        'UCAF_PAYMENT_CARD_EXPDATE_MONTH',
        'UCAF_PAYMENT_CARD_EXPDATE_YEAR',
        'UCAF_PAYMENT_CARD_NUMBER',
        'USERID',
        'USERTYPE',
        'VERSION',
        'WBTU_MSISDN',
        'WBTU_ORDERID',
        'WEIGHTUNIT',
        'WIN3DS',
        'WITHROOT',
    );
    // Class wide constants
    
    const OGONE_PRODUCTION_URL = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
    const OGONE_TEST_URL = 'https://secure.ogone.com/ncol/test/orderstandard.asp';

    /**
     * Constructor
     * 
     * @param  array config Configuration
     * @param  array params Parameters
     * @return Ogone_Form
     */
    public function __construct ($config = array(), $params = array())
    {
        $this->_config = array_merge($this->_config, $config);
        
        foreach ($params as $key => $value) {
            $this->addParam($key, $value);
        }
    }

    /**
     * Add parameter
     * 
     * @param  string      $key   Parameter key
     * @param  string      $value Parameter value
     * @return Ogone_Form
     */
    public function addParam ($key = null, $value = null)
    {
        if ($key !== null) {
            $this->_params[$key] = $value;
        }
        return $this;
    }

    /**
     * Get parameter value
     * 
     * @param  string $key Parameter key
     * @return string Parameter value
     */
    public function getParam ($key = null)
    {
        if ($key === null || (! array_key_exists($key, $this->_params))) {
            return '';
        }
        return $this->_params[$key];
    }

    /**
     * Get the Sha1 Sign
     * 
     * @return string Sha1 Sign
     */
    public function getSha1Sign ()
    {
        $arrayToHash = array();
        foreach ($this->_params as $key => $value) {
            if ($value != '' && $this->isValidParam($key)) {
                $arrayToHash[] = strtoupper($key) . '=' . $value .
                     $this->_config['sha1InPassPhrase'];
            }
        }
        asort($arrayToHash);
        $stringToHash = implode('', $arrayToHash);
        return sha1($stringToHash);
    }

    /**
     * Check if parameter is valid
     * 
     * @param string $key Parameter name
     * @return boolean
     */
    public function isValidParam ($key)
    {
        if (in_array(strtoupper($key), $this->_validParamNames)) {
            return true;
        }
        return false;
    }

    /**
     * Render the form
     * 
     * @return string HTML for the form
     */
    public function render ()
    {
        $html = '';
        $html .= '<form method="' . $this->_config['formMethod'] . '" id="' .
             $this->_config['formId'] . '" action="' .
             $this->_config['formAction'] . '" name="' .
             $this->_config['formName'] . '" class="' .
             $this->_config['formClass'] . '">' . PHP_EOL;
        
        foreach ($this->_params as $key => $value) {
            if ($value != '') {
                $html .= '<input type="hidden" name="' . $key . '" value="' .
                     $value . '" class="' . $this->_config['formElementClass'] .
                     '" />' . PHP_EOL;
            }
        }
        
        $html .= '<input type="hidden" name="SHASign" value="' .
             $this->getSha1Sign() . '" class="' .
             $this->_config['formElementClass'] . '" />' . PHP_EOL;
        $html .= '<div align="center"><input type="submit" name="doSubmit" value="' .
             $this->_config['formSubmitButtonValue'] . '" class="' .
             $this->_config['formSubmitButtonClass'] . '" /></div>' . PHP_EOL;
        $html .= '</form>' . PHP_EOL;
        return $html;
    }

    /**
     * Generates the url in case you want to redirect immediately
     * 
     * @return string url
     */
    public function getUrl ()
    {
        $url = $this->_config['formAction'] . '?';
        
        foreach ($this->_params as $key => $value) {
            if ($value != '') {
                $url .= $key . '=' . $value . '&';
            }
        }
        
        $url .= 'SHASign=' . $this->getSha1Sign();
        
        return $url;
    }
    
}
