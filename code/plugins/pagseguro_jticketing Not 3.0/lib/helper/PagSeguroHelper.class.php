<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }
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

/**
 * Helper functions
 */
class PagSeguroHelper {
	
	public static function formatDate($date) {
		$format = "Y-m-d\TH:i:sP";
		if ($date instanceof  DateTime) {
			$d = $date->format($format);
		} elseif (is_numeric($date)) {
			$d = date($format, $date);
		} else {
			$d = (String)"$date";
		}
		return $d;
	}
	
	public static function decimalFormat($numeric) {
		if (is_float($numeric)) {
			$numeric = (float)$numeric;
			$numeric = (string)number_format($numeric, 2, '.', '');
		}
		return $numeric;
	}
	
	public static function subDays($date, $days) {
		$d = self::formatDate($date);
		$d = date_parse($d);
		$d = mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'] - $days, $d['year']);
		return self::formatDate($d);
	}
	
	public static function print_rr($var, $dump = null){
		if (is_array($var) || is_object($var)) {
			echo "<pre>";
			if ($dump) {
				var_dump($var);
			} else {
				print_r($var);
			}
			echo "</pre>";
		}
	}
	
}

?>