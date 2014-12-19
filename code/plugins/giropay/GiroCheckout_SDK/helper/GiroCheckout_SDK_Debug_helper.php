<?php
/**
 * Helper class which handles debug log
 *
 * @package GiroCheckout
 * @version $Revision: 24 $ / $Date: 2014-05-22 14:30:12 +0200 (Thu, 22 May 2014) $
 */

class GiroCheckout_SDK_Debug_helper {

    /*
     * static instance of debug class (singelton pattern)
     */
    private static $instance = null;

    /*
     * unique logfile name
     */
    private static $logFileName;

    /*
     * logfile resource
    */
    private static $fp;

    /*
     * log templates
     */
    private $debugStrings = array(
        'start'         => "[start @%s]\r\n\r\n",
        'php-ini'       => "[PHP ini]\r\n\r\nPHP version: %s\r\ncURL: %s\r\nssl: %s\r\n\r\n\r\n",
        'transaction'   => "[transaction @%s]\r\nZahlart: %s\r\n\r\n\r\n",
        'params set'    => "[params set @%s]\r\n%s\r\n\r\n",
        'curlRequest'   => "[cURL request @%s]\r\nparams:\r\n%s\r\nCurlInfo:\r\n%s\r\n\r\n",
        'curlReply'     => "[cURL reply @%s]\r\nresult:%s\r\ncurl_error log:%s\r\n\r\n",
        'replyParams'   => "[reply params @%s]\r\n%s\r\n\r\n",
        'notifyInput'   => "[notify input @%s]\r\n%s\r\n\r\n",
        'notifyParams'  => "[notify params @%s]\r\n%s\r\n\r\n",
    	'exception'     => "[exception @%s]\r\n%s\r\n\r\n",
    );


    private function __construct(){}
    private function __clone(){}

    /*
     * method to create the debug instance (singelton pattern)
     */
    public static function getInstance() {

        if(null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*
     * initialisation of the debug log,
     * stores information about the environment
     */
    public function init($logFilePrefix) {
        self::$logFileName = date('Y-m-d h-i-s').' - '.ucfirst($logFilePrefix).' - '.md5(time()).'.log';
        $ssl = null;
        
        $this->writeLog(sprintf($this->debugStrings['start'],date('Y-m-d h:i:s')));

        if(in_array('curl',get_loaded_extensions())) {
            $curl_version = curl_version();
            $curl = $curl_version['version'];
            $ssl = $curl_version['ssl_version'];
        }
        else {
            $curl = 'no';
        }

        if(!$ssl && in_array('openssl',get_loaded_extensions())) {
        	$ssl = 'yes';
        }
        
        if(!$ssl) {
        	$ssl = 'no';
        }
        
        $this->writeLog(sprintf($this->debugStrings['php-ini'], PHP_VERSION, $curl, $ssl));
    }

    /*
     * logs transaction information
     */
    public function logTransaction($apiCallName) {
        $this->writeLog(sprintf($this->debugStrings['transaction'], date('Y-m-d h:i:s'), $apiCallName));
    }

    /*
     * logs parameters which were set before sending
     */
    public function logParamsSet($paramsArray) {
        $paramsString = '';

        foreach($paramsArray as $k=>$v) {
            $paramsString .= "$k=$v\r\n";
        }

        $this->writeLog(sprintf($this->debugStrings['params set'], date('Y-m-d h:i:s'), $paramsString));
    }

    /*
     * stores request data (parameters, curl info)
     */
    public function logRequest($curlInfo, $params) {
        $paramsString = '';
        $curlInfoString = '';

        foreach($params as $k=>$v) {
            $paramsString .= "$k=$v\r\n";
        }

        foreach($curlInfo as $k=>$v) {
            if(!is_array($v))
                $curlInfoString .= "$k=$v\r\n";
            else {
                $curlInfoString .= "$k {\r\n";

                foreach($v as $k2=>$v2) {
                    $curlInfoString .= "$k2=$v2\r\n";
                }

                $curlInfoString .= "}\r\n";
            }
        }

        $this->writeLog(sprintf($this->debugStrings['curlRequest'], date('Y-m-d h:i:s'), $paramsString, $curlInfoString));
    }

    /*
     * logs server reply data (header and body)
     */
    public function logReply($result, $curlError) {
        $this->writeLog(sprintf($this->debugStrings['curlReply'], date('Y-m-d h:i:s'), $result, $curlError));
    }

    /*
     * logs processed reply params from json array
     */
    public function logReplyParams($params) {
        $paramsString = '';

        foreach($params as $k=>$v) {
            $paramsString .= "$k=$v\r\n";
        }

        $this->writeLog(sprintf($this->debugStrings['replyParams'], date('Y-m-d h:i:s'), $paramsString));
    }

    /*
     * logs parameters which were used for Notification
     */
    public function logNotificationInput($paramsArray) {
        $paramsString = '';

        foreach($paramsArray as $k=>$v) {
            $paramsString .= "$k=$v\r\n";
        }

        $this->writeLog(sprintf($this->debugStrings['notifyInput'], date('Y-m-d h:i:s'), $paramsString));
    }

    /*
     * logs parameters which were used for Notification
     */
    public function logNotificationParams($paramsArray) {
        $paramsString = '';

        foreach($paramsArray as $k=>$v) {
            $paramsString .= "$k=$v\r\n";
        }

        $this->writeLog(sprintf($this->debugStrings['notifyParams'], date('Y-m-d h:i:s'), $paramsString));
    }

    /*
     * logs parameters which were used for Notification
     */
    public function logNotificationOutput($outputType) {
        $this->writeLog(sprintf($this->debugStrings['notifyOutput'], date('Y-m-d h:i:s'), $outputType));
    }

    /*
     * logs parameters which were used for Notification
    */
    public function logException($message) {
    	$this->writeLog(sprintf($this->debugStrings['exception'], date('Y-m-d h:i:s'), $message));
    }
    
    /*
     * writes log into log file
     */
    public function writeLog($string)
    {
    	$path = str_replace('\\','/',__GIROCHECKOUT_SDK_DEBUG_LOG_PATH__);

        if(!is_dir($path)) {
        	if(!mkdir($path)) {        		
            	throw new Exception('Log directory does not exist. Please create directory: '.$path.'.');
        	}
        	$htfp = fopen($path.'/.htaccess', 'w');
        	fwrite($htfp, "Order allow,deny\nDeny from all");
        	fclose($htfp);        	 
        }
        
        if(!self::$fp) {
        	self::$fp = fopen($path.self::$logFileName, 'a');
	        if(!self::$fp) throw new Exception('Log File ('.$path.self::$logFileName.') is not writeable.');
        }
        
        fwrite(self::$fp, $string);
    }
}