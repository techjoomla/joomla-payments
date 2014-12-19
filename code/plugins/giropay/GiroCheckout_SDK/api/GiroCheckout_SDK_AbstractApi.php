<?php
/**
 * Abstract API class for all GiroCheckout API calls.
 * Provides most of the interfaces functions. A new payment method should use this class.
 *
 * @package GiroCheckout
 * @version $Revision: 83 $ / $Date: 2014-10-24 16:21:51 +0200 (Fr, 24 Okt 2014) $
 */

class GiroCheckout_SDK_AbstractApi implements GiroCheckout_SDK_InterfaceApi{

    /*
     * for developent use only
     */
    function __construct() {
        try {
            if(function_exists('apache_getenv') && strlen(apache_getenv('GIROCHECKOUT_SERVER'))) {
                $url = parse_url($this->requestURL);
                $this->requestURL = apache_getenv('GIROCHECKOUT_SERVER').$url['path'];
            }
        }
        catch(Exception $e) {}
    }

    /**
     * Checks if the param exists. Check is case sensitive.
     *
     * @param String $param
     * @return boolean true if param exists
     */
    public function hasParam($paramName) {
        if(isset($this->paramFields[$paramName])) return true;
        elseif('sourceId' === $paramName) return true; //default field due to support issues
        return false;
    }


    /**
     * Returns all API call param fields in the correct order.
     *
     * @param mixed[] $params
     * @return mixed[] $submitParams
     * @throws Exception if one of the mandatory fields is not set
     */
    public function getSubmitParams($params) {

        foreach($this->paramFields as $k=>$mandatory) {
            if(isset($params[$k]))
                $submitParams[$k] = $params[$k];
            elseif(!isset($params[$k]) && $mandatory)
                throw new Exception('mandatory field '.$k.' is unset');
        }

        return $submitParams;
    }

    /**
     * Returns all response param fields in the correct order.
     *
     * @param mixed[] $response
     * @return mixed[] $responseParams
     * @throws Exception if one of the mandatory fields is not set
     */
    public function checkResponse($response)
    {
        if(!is_array($response)) return FALSE;

        foreach($this->responseFields as $k=>$mandatory) {
            if(isset($response[$k]))
                $responseParams[$k] = $response[$k];
            elseif(!isset($response[$k]) && $mandatory)
                throw new Exception('expected response field '.$k.' is missing');
        }

        return $responseParams;
    }

    /**
     * Returns all notify param fields in the correct order.
     *
     * @param mixed[] $notify
     * @return mixed[] $notifyParams
     * @throws Exception if one of the mandatory fields is not set
     */
    public function checkNotification($notify){
        if(!is_array($notify)) return FALSE;

        foreach($this->notifyFields as $k=>$mandatory) {

            if(isset($notify[$k]))
                $notifyParams[$k] = $notify[$k];
            elseif(!isset($notify[$k]) && $mandatory)
                throw new Exception('expected notification field '.$k.' is missing');
        }

        return $notifyParams;
    }

    /**
     * Returns true if a hash has to be added to the API call.
     *
     * @return boolean
     */
    public function needsHash() {
        return $this->needsHash;
    }

    /**
     * Returns the API request URL where the call has to be sent to.
     *
     * @return String requestURL
     */
    public function getRequestURL() {
        return $this->requestURL;
    }

    /**
     * Returns the API needs a notify URL, where the transaction result has to be sent to.
     *
     * @return String notifyURL
     */
    public function hasNotifyURL() {
        return $this->hasNotifyURL;
    }

    /**
     * Returns if the API needs a redirect URL, where the customer has to be sent to after payment.
     *
     * @return String redirectURL
     */
    public function hasRedirectURL() {
        return $this->hasRedirectURL;
    }

    /**
     * Returns the ResultCode of an successful transaction.
     *
     * @return int/null notifyURL
     */
    public function getTransactionSuccessfulCode() {
        if(isset($this->paymentSuccessfulCode))
            return $this->paymentSuccessfulCode;
        return NULL;
    }

    /**
     * Returns the ResultCode of an successful AVS check (age verification system).
     *
     * @return int/null notifyURL
     */
    public function getAVSSuccessfulCode() {
        if(isset($this->avsSuccessfulCode))
            return $this->avsSuccessfulCode;
        return NULL;
    }

    /**
     * Returns the parameter name of the hash in the notify or redirect API call from GiroConnect.
     *
     * @return int/null notifyURL
     */
    public function getNotifyHashName() {
        if(isset($this->notifyHashName))
            return $this->notifyHashName;

        return NULL;
    }
    
    /**
     * Returns true if the api is direct payment (without init and payment page)
     *
     * @return bool 
     */
    public function isDirectPayment() {
		return isset($this->responseFields['resultPayment']);
    }   
}