<?php
/**
 * GiroCheckout SDK.
 *
 * Include just this file. It will load any required files to use the SDK.
 * View examples for API calls.
 *
 * @package GiroCheckout
 * @version $Revision: 89 $ / $Date: 2014-10-30 12:56:24 +0100 (Do, 30 Okt 2014) $
 */
define('__GIROCHECKOUT_SDK_VERSION__', '1.5.2');

if(defined('__GIROCHECKOUT_SDK_DEBUG__') && __GIROCHECKOUT_SDK_DEBUG__) {
    define('__GIROCHECKOUT_SDK_DEBUG_LOG_PATH__', __DIR__.'/log/');
}
else {
	define('__GIROCHECKOUT_SDK_DEBUG__', false);
}

class GiroCheckout_SDK_Autoloader {
	public static function load($classname) {
		$filename = $classname . '.php';

		$pathsArray = array ('api',
				'helper',
				'./',
				'api/giropay',
				'api/directdebit',
				'api/creditcard',
				'api/eps',
				'api/ideal',
				'api/paypal',
				'api/tools',
				'api/girocode',
		);

		foreach($pathsArray as $path) {
			if($path == './') {
				$pathToFile = __DIR__ . DIRECTORY_SEPARATOR . $filename;
			} else {
				$pathToFile = __DIR__ . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $filename;
			}

			if (file_exists($pathToFile)) {
				require_once $pathToFile;
				return true;
			} else {
				continue;
			}
		}
		return false;
	}
}

spl_autoload_register(array('GiroCheckout_SDK_Autoloader', 'load'));
