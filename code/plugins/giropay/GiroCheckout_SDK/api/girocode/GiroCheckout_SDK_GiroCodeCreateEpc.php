<?php
/**
 * Provides configuration for an direct debit API call.
 *
 * @package GiroCheckout
 * @version $Revision: 46 $ / $Date: 2014-07-04 16:24:28 +0200 (Fr, 04 Jul 2014) $
 */

class GiroCheckout_SDK_GiroCodeCreateEpc extends GiroCheckout_SDK_AbstractApi implements GiroCheckout_SDK_InterfaceApi {

  /*
   * Includes any parameter field of the API call. True parameter are mandatory, false parameter are optional.
   * For further information use the API documentation.
   */
  protected $paramFields = array(
    'merchantId'=> TRUE,
    'projectId' => TRUE,
    'amount' => FALSE,
    'currency' => FALSE,
    'purposetext' => FALSE,
    'paymentreference' => FALSE,
    'purposecode' => FALSE,
    'infotext' => FALSE,
    'receivername' => FALSE,
    'receiveriban' => FALSE,
    'receiverbic' => FALSE,
    'format' => TRUE,
    'resolution' => FALSE,
  );


  /*
   * Includes any response field parameter of the API.
   */
  protected $responseFields = array(
    'rc'=> TRUE,
    'msg' => TRUE,
    'girocodereference' => FALSE,
    'image' => FALSE,
  );

  /*
   * True if a hash is needed. It will be automatically added to the post data.
   */
  protected $needsHash = TRUE;

  /*
   * The request url of the GiroCheckout API for this request.
   */
  protected $requestURL = "https://payment.girosolution.de/girocheckout/api/v2/girocode/createepc";

  /*
   * If true the request method needs a notify page to receive the transactions result.
   */
  protected $hasNotifyURL = FALSE;

  /*
   * If true the request method needs a redirect page where the customer is sent back to the merchant.
   */
  protected $hasRedirectURL = FALSE;
}