<?php
	
	/**
	 * Configuration Management
	 *
	 * PHP version 5
	 *
	 * @package    PHP-Multimedia-Sorter
	 * @author     Jiminald <code@jiminald.co.uk>
	 * @copyright  2011 Jiminald
	 * @license    See LICENCE file
	 * @version    1.1
	 * @link       http://jiminald.co.uk
	 */
	
	class Config {
		
		//Global Variables

		//Public Variables
		
		//Private Variables
		/**
		 * Configuration Data
		 * @access private
		 * @var array
		 */
		private $config = array();
		/**
		 * Keys of the Configuration Data
		 * @access private
		 * @var array
		 */
		private $keys = array();
		
		/**
		 * Read a config value from the stored array
		 * @param string $key Array key name
		 * @return string|boolean
		 */
		function read($key) {
			//If the config exists, return it, otherwise return false
			if (in_array($key, $this->keys)) {
				return $this->config[$key];
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Write a value to the config array
		 * @param string $key Name of the contents
		 * @param string $value Contents to store in array
		 * @return boolean 
		 */
		function write($key, $value) {
			$this->config[$key] = $value;
			//Update the keys
			$this->keys = array_keys($this->config);
			//Return that we did it
			return TRUE;
		}
		
		/**
		 * Read in a configuration file and decode
		 * @param string $file Path from CWD and Filename of the config file
		 * @return boolean
		 */
		function readFile($file = 'default.conf') {
			//Find the file and check it exists.
			$file = getcwd().'/'.$file;
			if (file_exists($file)) {
				//Open the file, decode and work out the array keys
				$config = file_get_contents($file);
				$this->config = json_decode($config, TRUE);
				$this->keys = array_keys($this->config);
				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Encode and write a configuration file
		 * @param string $file Path from CWD and Filename of the config file
		 * @return boolean
		 */
		function writeFile($file = 'default.conf') {
			$config = json_encode($this->config);
			$file = getcwd().'/'.$file;
			return file_put_contents($file, $config);
		}
		
		/**
		 * Show the config or the config keys
		 * @param string $array Name of the array we want to see
		 * @return array
		 */
		function show($array = 'config') {
			if ($array == 'keys') {
				return $this->keys;
			} else {
				return $this->config;
			}
		}
		
	} //End of class
?>
