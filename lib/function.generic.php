<?php

	/*
	* Returns the full class name as string
	*
	* @param string $str
	* @return string
	*/
	function str2class($str) {
		$str = str_replace('.', '', $str);
		return $str;
	} //End of function

	/*
	* Checks if the number is odd or not
	*
	* @param integer $num
	* @return boolean
	*/
	function isOdd($num) {
		if ($number & 1) {
			return TRUE;
		}  else {
			return FALSE;
		} //End of if
	} //End of function
	
	function recursive_array_search($needle, $haystack) {
		if (empty($needle) || empty($haystack)) {
			return false;
		}
		
		foreach ($haystack as $key => $value) {
			$exists = 0;
			foreach ($needle as $nkey => $nvalue) {
				if (!empty($value[$nkey]) && $value[$nkey] == $nvalue) {
					$exists = 1;
				} else {
					$exists = 0;
				}
			}
			if ($exists) return $key;
		}
		
		return false;
	}
	
	if (!function_exists('sys_get_temp_dir')) {
		function sys_get_temp_dir() {
			// check environment variables.
			foreach (array('TMP', 'TEMP', 'TMPDIR') as $env_var) {
				if ($temp = getenv($env_var)) {
					return $temp;
				}
			}
			// test for a temp directory by having PHP create a temporary file.
			$temp = tempnam(__FILE__, '');
			if (file_exists($temp)) {
				unlink($temp);
				return dirname($temp);
			}
			// couldn't find a temp directory.
			return FALSE;
		}
	}
?>
