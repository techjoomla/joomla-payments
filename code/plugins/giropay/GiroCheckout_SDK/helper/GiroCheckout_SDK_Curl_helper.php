<?php
/**
 * Helper class which manages sending data
 *
 * @package GiroCheckout
 * @version $Revision: 80 $ / $Date: 2014-10-21 18:49:14 +0200 (Di, 21 Okt 2014) $
 */

class GiroCheckout_SDK_Curl_helper {

    /*
     * submits data by using curl to a given url
     *
     * @param String url where data has to be sent to
     * @param mixed[] array data which has to be sent
     * @return String body of the response
     */
    public static function submit($url, $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if(defined('__GIROSOLUTION_SDK_CERT__')) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        	curl_setopt($ch, CURLOPT_CAINFO, str_replace('\\', '/', __GIROSOLUTION_SDK_CERT__));
        }
        
       // if(defined('__GIROSOLUTION_SDK_SSL_VERIFY_OFF__') && __GIROSOLUTION_SDK_SSL_VERIFY_OFF__) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
       // }
        
        if(__GIROCHECKOUT_SDK_DEBUG__) {
        	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        $result = curl_exec($ch);

        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logRequest(curl_getinfo($ch),$params);
        if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->logReply($result, curl_error($ch));

        if($result === false) {
            throw new Exception('cURL: submit failed.');
        }

        curl_close($ch);

        return self::getHeaderAndBody($result);
    }

    /*
     * decodes a json string and returns an array
     *
     * @param String json string
     * @return mixed[] array of an parsed json string
     * @throws Exception if string is not a valid json string
     */
    public static function getJSONResponseToArray($string)
    {
        $json = json_decode($string,true);
        if(json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }
        else {
            throw new Exception('Response is not a valid json string.');
        }
    }

    /*
     * strip header content
     *
     * @param String server response
     * @return mixed[] header, body of the server response. The header is parsed as an array.
     */
    private static function getHeaderAndBody($response) {

        $header = self::http_parse_headers(substr($response, 0, strrpos($response,"\r\n\r\n")));
        $body = substr($response, strrpos($response,"\r\n\r\n")+4);

        return array($header,$body);
    }

    /*
     * parses http header
     *
     * @param String header
     * @return mixed[] parsed header
     */
    private static function http_parse_headers($header)
    {
        $headers = array();
        $key = '';

        foreach(explode("\n", $header) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                }
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                }
                elseif (!$key) {
                    $headers[0] = trim($h[0]);trim($h[0]);
                }
            }
        }

        return $headers;
    }
} 