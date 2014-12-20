<?php

class GiroCheckout_SDK_Exception_helper extends Exception {
	
	public function __construct($message = null, $code = 0) {

		if(__GIROCHECKOUT_SDK_DEBUG__) GiroCheckout_SDK_Debug_helper::getInstance()->LogException($message);
		parent::__construct($message, $code);
	}
}