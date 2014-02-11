<?php

/**
 * @category     Ogone
 * @package      Ogone_Response
 * @author       Jurgen Van de Moere (http://www.jvandemo.com)
 * @copyright    JobberID (http://www.jobberid.com)
 * @license      http://framework.zend.com/license/new-bsd New BSD License
 */
class Ogone_Response
{

    /**
     * Array of parameters (any values specified here will be used as default, 
     * but can be overridden by constructor or addParam())
     * 
     * @var array
     */
    protected $_params = array();

    /**
     * Configuration (any values specified here will be used as default, 
     * but can be overridden by constructor)
     * 
     * @var array
     */
    protected $_config = array(
        // SHA1 details
        'sha1OutPassPhrase' => ''
    );

    /***********************************************
     *  DO NOT CHANGE ANYTHING BELOW THIS LINE !!!
     **********************************************/
    
    /**
     * Sha1 sign returned by Ogone for verification
     * 
     * @var string
     */
    protected $_sha1Sign = '';

    /**
     * Array of valid parameter names
     * 
     * @var array
     */
    protected $_validParamNames = array(
        'AAVADDRESS',
        'AAVCHECK',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COMPLUS',
        'CREATION_STATUS',
        'CURRENCY',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'ENCCARDNO',
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'ORDERID',
        'PAYID',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'STATUS',
        'SUBBRAND',
        'SUBSCRIPTION_ID',
        'TRXDATE',
        'VC'
    );

    /**
     * Constructor
     * 
     * @param  array config Configuration
     * @param  array params Parameters
     * @return Ogone_Response
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
     * @return Ogone_Response
     */
    public function addParam ($key = null, $value = null)
    {
        if ($key !== null) {
            // Store all parameters provided by Ogone
            if (in_array(strtoupper($key), $this->_validParamNames)) {
                $this->_params[$key] = $value;
            }
            
            // Store the SHASIGN returned by Ogone
            if (strtoupper($key) === 'SHASIGN') {
                $this->_sha1Sign = $value;
            }
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
        if ($key === null) {
            return '';
        }
        
        $key = strtoupper($key);
        
        if (! array_key_exists($key, $this->_params)) {
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
                     $this->_config['sha1OutPassPhrase'];
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
     * Check if the response by Ogone is valid
     * 
     * @return boolean
     */
    public function isValid ()
    {
        if ($this->_sha1Sign == '') {
            return false;
        }
        if (strtoupper($this->_sha1Sign) === strtoupper($this->getSha1Sign())) {
            return true;
        }
        return false;
    }

    /**
     * Dump the parameters, for debugging purposes only
     * 
     * @return Ogone_Response
     */
    public function dump ()
    {
        var_dump($this->_params);
        return $this;
    }
}
