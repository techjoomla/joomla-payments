<?php
/**
 * Helper class which manages api call instances
 *
 * @package GiroCheckout
 * @version $Revision: 86 $ / $Date: 2014-10-30 12:25:32 +0100 (Do, 30 Okt 2014) $
 */
class GiroCheckout_SDK_TransactionType_helper {

    /*
     * returns api call instance
     *
     * @param String api call name
     * @return interfaceAPI
     */
    public static function getTransactionTypeByName($transType) {
        switch($transType) {
            //credit card apis
            case 'creditCardTransaction':           return new GiroCheckout_SDK_CreditCardTransaction();
            case 'creditCardGetPKN':                return new GiroCheckout_SDK_CreditCardGetPKN();
            case 'creditCardRecurringTransaction':  return new GiroCheckout_SDK_CreditCardRecurringTransaction();

            //direct debit apis
            case 'directDebitTransaction':                  return new GiroCheckout_SDK_DirectDebitTransaction();
            case 'directDebitTransactionWithPaymentPage':   return new GiroCheckout_SDK_DirectDebitTransactionWithPaymentPage();

            //giropay apis
            case 'giropayBankstatus':               return new GiroCheckout_SDK_GiropayBankstatus();
            case 'giropayIDCheck':                  return new GiroCheckout_SDK_GiropayIDCheck();
            case 'giropayTransaction':              return new GiroCheckout_SDK_GiropayTransaction();
            case 'giropayTransactionWithGiropayID': return new GiroCheckout_SDK_GiropayTransactionWithGiropayID();
            case 'giropayIssuerList':               return new GiroCheckout_SDK_GiropayIssuerList();
            
            //iDEAL apis
            case 'idealIssuerList': return new GiroCheckout_SDK_IdealIssuerList();
            case 'idealPayment':    return new GiroCheckout_SDK_IdealPayment();

            //PayPal apis
            case 'paypalTransaction': return new GiroCheckout_SDK_PaypalTransaction();

            //eps apis
            case 'epsBankstatus':	return new GiroCheckout_SDK_EpsBankstatus();
            case 'epsTransaction':	return new GiroCheckout_SDK_EpsTransaction();
            case 'epsIssuerList':	return new GiroCheckout_SDK_EpsIssuerList();
            
            //tools apis
            case 'getTransactionTool': return new GiroCheckout_SDK_Tools_GetTransaction();

            //GiroCode apis
            case 'giroCodeCreatePayment': return new GiroCheckout_SDK_GiroCodeCreatePayment();
            case 'giroCodeCreateEpc': 	return new GiroCheckout_SDK_GiroCodeCreateEpc();
            case 'giroCodeGetEpc': 	return new GiroCheckout_SDK_GiroCodeGetEpc();
        }

        return null;
    }
}