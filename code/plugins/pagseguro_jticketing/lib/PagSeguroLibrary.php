<?php
/*
************************************************************************
Copyright [2011] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/*
 * PagSeguro Library Class
 * Version: 2.1.0
 * Date: 19/01/2012
 */
define('PAGSEGURO_LIBRARY', TRUE);
require_once "loader".DIRECTORY_SEPARATOR."PagSeguroAutoLoader.class.php";
class PagSeguroLibrary {
	
	const VERSION = "2.1.1";
	private static $library;
	private static $path;
	public static $resources;
	public static $config;
	public static $log;
	
	private function __construct() {
		self::$path 	 = (dirname(__FILE__));
		PagSeguroAutoloader::init();
		self::$resources = PagSeguroResources::init();
		self::$config 	 = PagSeguroConfig::init();
		self::$log 	 	 = LogPagSeguro::init();
	}
	
	public static function init() {
		self::verifyDependencies();
		if (self::$library == null) {
			self::$library  = new PagSeguroLibrary();
		}
		return self::$library;
	}
	
	private static function verifyDependencies() {
		
		$dependencies = true;
		
		if (!function_exists('spl_autoload_register')) {
			throw new Exception("PagSeguroLibrary: Standard PHP Library (SPL) is required.");
			$dependencies = false;
		}
		
		if (!function_exists('curl_init')) {
			throw new Exception('PagSeguroLibrary: cURL library is required.');
			$dependencies = false;
		}
		
		if (!class_exists('DOMDocument')) {
			throw new Exception('PagSeguroLibrary: DOM XML extention is required.');
			$dependencies = false;
		}
		
		return $dependencies;
		
	}
	
	public final static function getVersion() {
		return self::VERSION;
	}
	
	public final static function getPath() {
		return self::$path;
	}
	
}
PagSeguroLibrary::init();
?>
