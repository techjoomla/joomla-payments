<?php
/**
 * Used for notification and redirect calls from GiroCheckout to the merchant.
 *
 * - notify means that GiroCheckout sends the final result of an initiated transaction.
 * - redirect means that the customer is sent back to the merchant, if he was redirected to somewhere outside
 *   the merchants website.
 *
 * how to use (see example section):
 * 1. Instantiate a new Notify class and pass an api method to the constructor.
 * 2. Parse the notification by given array including the GET Params.
 * 3. Check the success of the transaction.
 *
 * @package GiroCheckout
 * @version $Revision: 80 $ / $Date: 2014-10-21 18:49:14 +0200 (Di, 21 Okt 2014) $
 */

class GiroCheckout_SDK_Notify {

    /*
     * stores any committed notify parameter which was sent to GiroConnect
     */
    private $notifyParams = Array();

    /*
     * stores given secret
     */
    private $secret = '';

    /*
     * stores the api request method object
     */
    private $requestMethod;


    /**
     * instantiates notification
     *
     * a request method instance has to be passed (see examples section)
     *
     * @param InterfaceApi/String $apiCallMethod
     * @throws Exception if notification is not possible
     */
    function __construct($apiCallMethod) {

        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->init('notify');

        if(is_object($apiCallMethod)) {
            $this->requestMethod = $apiCallMethod;

            if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logTransaction(get_class($apiCallMethod), $apiCallMethod);
        }
        elseif (is_string($apiCallMethod)) {
            $this->requestMethod = GiroCheckout_SDK_TransactionType_helper::getTransactionTypeByName($apiCallMethod);

            if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logTransaction($apiCallMethod, $this->requestMethod);

            if(is_null($this->requestMethod))
                throw new GiroCheckout_SDK_Exception_helper('Failure: API call method unknown');
        }

        if (!$this->requestMethod->hasNotifyURL() && !$this->requestMethod->hasRedirectURL()) {
             throw new GiroCheckout_SDK_Exception_helper('Failure: notify or redirect not possible with this api call');
        }
    }

    /**
     * returns the data from the given parameter
     *
     * @param String $param response parameter key
     * @return String data of the given response key
     */
    public function getResponseParam($param) {
        if(isset($this->notifyParams[$param]))
            return $this->notifyParams[$param];
        return null;
    }


    /**
     * returns the whole notification param data 
     * 
     * @return Mixed[] array of data
     */
    public function getResponseParams() {
    	if(isset($this->notifyParams))
    		return $this->notifyParams;
    	return null;
    }
    
    /*
     * Sets the secret which is used for hash generation or hash comparison.
     *
     * @param String $secret
     * @return String $this own instance
     */
    public function setSecret($secret) {
        $this->secret = $secret;
        return $this;
    }

    /*
     * parses the given notification array
     *
     * @param mixed[] $params pas the $_GET array or validated input
     * @return boolean if no error occurred
     * @throws Exception if an error occurs
     */
    public function parseNotification($params) {
        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logNotificationInput($params);

        if(!is_array($params) || empty($params)) throw new GiroCheckout_SDK_Exception_helper('no data given');
        try {
            $this->notifyParams = $this->requestMethod->checkNotification($params);
            
            if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logNotificationParams($this->notifyParams);

            if (!$this->checkHash()) {
              throw new GiroCheckout_SDK_Exception_helper('hash mismatch');
            }
        }
        catch (Exception $e) {
            throw new GiroCheckout_SDK_Exception_helper('Failure: '.  $e->getMessage()."\n");
        }

        return TRUE;
    }

    /*
     * validates the submitted hash by comparing to a self generated Hash
     *
     * @return boolean true if hash test passed
     */
    public function checkHash() {
        $string = '';
        $hashFieldName = $this->requestMethod->getNotifyHashName();

        foreach ($this->notifyParams as $k => $v) {
            if ($k !== $hashFieldName)
                $string .= $v;
        }

        if ($this->notifyParams[$hashFieldName] === hash_hmac('md5', $string, $this->secret)) {
            return true;
        }

        return false;
    }

    /*
     * returns true if the payment transaction was successful
     *
     * @return boolean result of payment
     */
    public function paymentSuccessful() {
        if ($this->requestMethod->getTransactionSuccessfulCode() != null) {
          return $this->requestMethod->getTransactionSuccessfulCode() == $this->notifyParams['gcResultPayment'];
        }

        return false;
    }

    /*
     * returns true if the age verification was successful
     *
     * @return boolean result of age verification
     */
    public function avsSuccessful() {
        if ($this->requestMethod->getAVSSuccessfulCode() != null) {
          return $this->requestMethod->getAVSSuccessfulCode() == $this->notifyParams['gcResultAVS'];
        }

        return false;
    }

    /*
     * sends header with 200 OK status
     */
    public function sendOkStatus() {
        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logNotificationInput('sendOkStatus');
        header('HTTP/1.1 200 OK');
    }

    /*
     * sends header with 400 Bad Request status
     */
    public function sendBadRequestStatus() {
        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logNotificationInput('sendBadRequestStatus');
        header('HTTP/1.1 400 Bad Request');
    }

    /*
     * sends header with 404 Not Found status
     */
    public function sendOtherStatus() {
        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logNotificationInput('sendOtherStatus');
        header('HTTP/1.1 404 Not Found');
    }
    
    /*
     * Gives response message to given code number in the given language.
    *
    * @param integer code
    * @param String language
    * @return String thee codes description in given language
    */
    public function getResponseMessage($responseCode,$lang = 'DE') {
    	return GiroCheckout_SDK_ResponseCode_helper::getMessage($responseCode,$lang);
    }
}