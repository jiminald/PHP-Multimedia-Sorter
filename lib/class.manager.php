<?php
	/**
	 * Manages the classes for the framework
	 *
	 * Lets the framework & developer include and check classes/objects, also can be used
	 * to see if it has been included.
	 *
	 * @access       public
	 * @author       Jiminald <code@jiminald.co.uk>
	 * @copyright    Jiminald 18/May/2010
	 * @package      3CoreFrame
	 * @subpackage   libraries
	 * @version      1.0
	 */

	 /*
	  * Includes a class
	  *
	  * @staticvar array $objects This holds a list of included classes
	  * @param string $class
	  * @param array $options options about the file to include. Options like, critical
	  * @return resource
	  */
	function &include_class($file, $options = array()) {
	 	static $objects = array();
	 	#$file = $class;
	 	if (strrpos($file, '.')) {
	 	  $file = substr($file, 0, strrpos($file, '.'));
	 	}

	 	if (isset($options['class'])) {
	 	  $class = $options['class'];
	 	} else {
		  $class = $file;
		}

		//If theres another prefix, substitue lib_ with it. Otherwise use lib_
		if (isset($options['class_prefix'])) {
			$class = $options['class_prefix'].$class;
		} elseif ((isset($options['path'])) && (preg_match("/\bmodel\b/i", $options['path']))) {
			$class = 'model_'.$class;
		} elseif ((isset($options['path'])) && (preg_match("/\bpage\b/i", $options['path']))) {
			$class = 'page_'.$class;
		}

		//Work out the class name and check if it already exists
		$class = str2class(str_replace('.', '', $class));
		$classCheck = check_class_loaded($class, TRUE);
		if ($classCheck) {
			return $objects[$class];
		}

		//Load the class
		if (isset($options['path'])) {
			include_file($options['path'].$file.EXT, $options);
		} else {
			include_file(BASEDIR.$file.EXT, $options);
		}

		//Load the class
		$objects[$class] = new $class();
		//Log the class loading
		check_class_loaded($class, FALSE);
		//Return the class value
		return $objects[$class];
	} //End of function*/

	/**
	 * Keeps track of included classes for debug and validation of inclusion
	 *
	 * @staticvar array $included_classes List of included classes
	 * @param string $class Class to check
	 * @return mixed
	 */
	function check_class_loaded($class = '', $verify = TRUE) {
		static $included_classes = array();

		if (($class == '%') || ($class == '*')) { //If these characters are mentioned, return the list of included files
			return $included_classes;
		} else if (in_array($class, $included_classes)) {//Check for the file, see if its already been included. If it has, return TRUE.
			return TRUE;
		} else {//If is neither, add the file to the list.
			if ($verify == FALSE) {
				$included_classes[] = $class;
				return TRUE;
			}
		}
		return FALSE;
	} //End of function
?>
