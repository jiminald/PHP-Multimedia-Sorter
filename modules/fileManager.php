<?php
	
	/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

	/**
	 * The file managing class
	 *
	 * PHP version 5
	 *
	 *
	 * @package    PHP-Multimedia-Sorter
	 * @author     Jiminald <code@jiminald.co.uk>
	 * @copyright  2011 Jiminald
	 * @license    See LICENCE file
	 * @version    1.1
	 * @link       http://jiminald.co.uk
	 */
	
	class fileManager {
		
		//Global Variables

		//Public Variables
		/**
		 * Root directory of the scanning folder
		 * @access public
		 * @var array
		 */
		public $rootDirectory = '';
		/**
		 * Illegal Characters in file and folder names
		 * @access public
		 * @var array
		 */
		public $illegalChars = array(':', ';', '|', '`', '>', '<', '~', '*', '?', '"');
		//Private Variables
		/**
		 * Config Class
		 * @access private
		 * @var resource
		 */
		private $Config = NULL;
		/**
		 * Output Class
		 * @access private
		 * @var resource
		 */
		private $Output = NULL;
		
		/**
		 * Includes and connects this class to classes required
		 */
		function __construct() {
			//Connect to classes required
			$this->Config = include_class('Config');
			$this->Output = include_class('Output');
		}
		
		/**
		 * Scan the selected folder
		 * @param string $directory The full path of the folder to be scanned
		 * @return array 
		 */
		function scan($directory) {
			$return = array();
			if ($handle = opendir($directory)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if (is_dir($directory. "/" . $file)) {
							//Say what folder it is
							$return[] = ':'.str_replace($this->rootDirectory, '', realpath($directory . "/" . $file));
							//Get folder contents
							$return = array_merge($return, $this->scan($directory. "/" . $file));
						} else {
							//Add File
							$return[] = str_replace($this->rootDirectory, '', realpath($directory . "/" . $file));
						}
					}
				}
				closedir($handle);
			}
			
			return $return;
		} //End of function "scan"
		
		/**
		 * Move a file to a new destion
		 * @param string $source The file's current location
		 * @param string $destination The file's destined location
		 * @return void
		 */
		function move($source, $destination) {
			$sourceDir = substr($source, 0, strrpos($source, '/'));
			
			//Create the new folders if and where required
			$appendFolder = $this->recursive_mkDir($destination, $this->rootDirectory);
			
			//Remove Illegal characters
			$destination = str_replace($this->illegalChars, '', $destination);
			
			//Do the move
			if (rename($source, $destination)) {
				$this->Output->send('  %2- File Moved to '.str_replace($this->rootDirectory, '', substr($appendFolder, 0, strrpos($appendFolder, '/'))).'%n');
			} else {
				$this->Output->send('  %1- Failed to Move%n');
			}
		} //End of function "move"
		
		/**
		 * Create folders recursively
		 * @param string $finalFolder The files destined location
		 * @param string $niceFolderName The string to remove to shorten the folder name when sent to screen
		 * @return string
		 */
		function recursive_mkDir($finalFolder, $niceFolderName = '') {
			//Create the new folders if and where required
			$finalFolder = str_replace($this->illegalChars, '', $finalFolder);
			$finalFolder = explode('/', substr($finalFolder, 0, strrpos($finalFolder, '/')));
			$appendFolder = '';
			foreach ($finalFolder as $folder) {
				$mkDir = $appendFolder.$folder;
				if (($mkDir <> '') && (file_exists($mkDir) == FALSE)) {
					mkdir($mkDir);
					$this->Output->send('%3Making folder: '.str_replace($niceFolderName, '', $mkDir).'%n');
				}
				$appendFolder .= $folder.'/';
			}
			
			return $appendFolder;
		}
		
	}

?>
