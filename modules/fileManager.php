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
		 * Move a file, and create the new folders if required
		 * @param string $source The file's current location
		 * @param string $destination The file's destined location
		 * @return void
		 */
		function move($source, $destination) {
			$sourceDir = substr($source, 0, strrpos($source, '/'));
			
			//Create the new folders if and where required
			$destinationDir = str_replace($this->rootDirectory, '', $destination);
			$destinationDir = explode('/', substr($destinationDir, 0, strrpos($destinationDir, '/')));
			$appendFolder = '';
			foreach ($destinationDir as $folder) {
				$mkDir = $this->rootDirectory.$appendFolder.$folder;
				if (file_exists($mkDir) == FALSE) {
					mkdir($mkDir);
					$this->Output->send('%3Making folder: '.str_replace($this->Config->read('sorted'), '', $mkDir).'%n');
				}
				$appendFolder .= $folder.'/';
			}
			
			//Do the move
			if (rename($source, $destination)) {
				$this->Output->send('  %2- File Moved to '.substr($appendFolder, 0, strrpos($appendFolder, '/')).'%n');
			} else {
				$this->Output->send('  %1- Failed to Move%n');
			}
		} //End of function "move"
		
	}

?>
