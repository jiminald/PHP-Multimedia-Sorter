<?php
	/**
	 * Manages files for the framework
	 *
	 * Lets the framework & developer include and check files, also can be used
	 * to see if it has been included.
	 *
	 * @access       public
	 * @author       Jiminald <code@jiminald.co.uk>
	 * @copyright    Jiminald 18/May/2010
	 * @package      3Core
	 * @version      1.0
	 */
	 
	/**
	 * Include file
	 * 
	 * @param string $file File to include
	 * @param array $options options about the file to include. Options like, critical
	 * @return boolean
	 */
	function include_file($file, $options = array()) {
		//Check if file is already loaded, if it is, return TRUE and do no more
		if (check_file_loaded($file, TRUE)) {
			return TRUE;
		} //End of if
		
		$return = FALSE;
		//If not, check the file exists
		if (file_exists($file)) {
			//If file exists, attempt inclusion and log if its a success
			if ((include $file) == 1) {
				//$files[] = $file;
				$return = TRUE;
			} //End of if
		} //End of if
		
		if (!$return) {
			if (isset($options['level'])) {
				switch ($options['level']) {
					default:
					case 'critical':
						trigger_error('"'.$file.'" failed to load because the file could not be found.', E_USER_ERROR);
						exit(1);
					break;
				}
			} else {
				return FALSE;
			} //End of if
		} else {
			//Log the file loading
			check_file_loaded($file, FALSE);
			return TRUE;
		} //End of if
	} //End of function
	
	/**
	 * Keeps track of included classes for debug and validation of inclusion
	 * 
	 * @staticvar array $included_files List of included files
	 * @param string $file File to check
	 * @return mixed
	 */
	function check_file_loaded($file, $verify = TRUE) {
		static $included_files = array();
		//If the file is a dir, then return FALSE
		if (($file == '') || ($file == '.') || ($file == '..')) { return FALSE; }
		
/*echo '"'.$file.'" : ';
var_dump(in_array($file, $included_files));
echo ' = ';
var_dump($verify);
echo '<br /><br />';*/

		if (($file == '%') || ($file == '*')) { //If these characters are mentioned, return the list of included files
			return $included_files;
		} else if (in_array($file, $included_files)) {//Check for the file, see if its already been included. If it has, return TRUE.
			return TRUE;
		} else if ($verify == FALSE) {//If is neither, add the file to the list.			
			$included_files[] = $file;
			return TRUE;
		}
		return FALSE;
	} //End of function
	 
?>
