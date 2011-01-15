<?php
	
	/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

	/**
	 * The music managing class
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
	
	class music {
		
		//Global Variables

		//Public Variables
		/**
		 * Music Extentions
		 * @access public
		 * @var array
		 */
		public $extentions = array('mp3', 'wma', 'mp4', 'm4a', 'wav', 'flac', 'ogg', 'aac', 'midi');
		/**
		 * Default tag data
		 * @access public
		 * @var array
		 */
		public $defaultTagData = array('artist' => 'Unknown Artist', 'album' => 'Unknown Album', 'title' => 'Unknown Title');
		/**
		 * Original music files found, for duplicate checking
		 * @access private
		 * @var array
		 */
		public $musicDuplicates = array();
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
		 * fileManager Class
		 * @access private
		 * @var resource
		 */
		private $fileManager = NULL;
		/**
		 * getID3 Class
		 * @access private
		 * @var resource
		 */
		private $getID3 = NULL;
		
		/**
		 * Includes and connects this class to classes required
		 */
		function __construct() {
			$this->Config = include_class('Config');
			$this->Output = include_class('Output');
			$this->fileManager = include_class('fileManager');
		}
		
		/**
		 * Do the inital scan for music files and inital sorting
		 * @return array
		 */
		function initalScan() {
			//Stats
			$stats = array('started' => time(), 'files' => 0);
			
			//Check the sorted directory exists, if not, create
			$this->Output->send('%5Checking for Sorted folder%n');
			$sortedDir = $this->Config->read('sorted');
			if (file_exists($sortedDir) == FALSE) {
				$this->Output->send('%1Sorted Folder does not exist, Creating%n');
				$this->fileManager->recursive_mkDir($sortedDir);
			} else {
				$this->Output->send('%2Sorted Folder Found%n');
			}
			
			//Start on the unsorted folder
			$this->Output->send('%5Scanning unsorted folder: '.$this->Config->read('unsorted').'%n');
			sleep(1);
			
			//Set the rootDirectory in fileManager
			$this->fileManager->rootDirectory = $this->Config->read('unsorted');
			//Start the scan
			$structure = $this->fileManager->scan($this->Config->read('unsorted'), true);
			//now we are done, clear the root directory
			$this->fileManager->rootDirectory = '';
			
			//Get the folders
			$folderNames = array_keys($structure);
			$folderCount = count($structure);
			
			//Set the rootDirectory in fileManager to the sorted Directory
			$this->fileManager->rootDirectory = $this->Config->read('sorted');
			//Now move onto processing
			foreach ($structure as $item) {
				if (strstr($item, ':') <> '') {
					#$this->Output->send('%5%9Entering Folder:%n '.substr($item, 1));
				} else {
					$file = $this->Config->read('unsorted').$item;
					$info = pathinfo($item);
					if ((isset($info['extension'])) && (in_array($info['extension'], $this->extentions))) {
						$this->Output->send('%6'.$item.'%n');
						
						//Use the extention to find the parser, if nothing matches, load default
						switch ($info['extension']) {
							case 'mp3':
								$tag = $this->parse_mp3_tags($file);
							break;
							
							default:
								$tag = $this->defaultTagData;
							break;
						}
						
						//Find out where we are putting this file
						$destination = $this->sorting_message($item, $tag);
						
						//Move it
						$this->fileManager->move($file, $destination);
						
						//Stat counter
						$stats['files']++;
					}
				}
			}
			
			//now we are done, clear the root directory
			$this->fileManager->rootDirectory = '';
			
			//Finish the stats and return it
			$stats['finished'] = time();
			return $stats;
		}
		
		/**
		 * Find out where this file is going and let the user know where
		 * @param string $item The relative location of the music file in the unsorted directory
		 * @param array $info The music file's tag data
		 * @return string 
		 */
		function sorting_message($item, $info) {
			$duplicate = TRUE;
			//Both artist and album have data
			if (($info['artist'] <> 'Unknown Artist') && ($info['album'] <> 'Unknown Album'))  {
				//Send a message that we found data
				$this->Output->send('  %2- ID3 Tags Found%n');
				$this->Output->send('  %2- Sorted by Artist and Album%n');
			}
			
			//Artist has data, but album does not
			if (($info['artist'] <> 'Unknown Artist') && ($info['album'] == 'Unknown Album'))  {
				//Send a message that we found data
				$this->Output->send('  %2- ID3 Tags Found%n');
				$this->Output->send('  %2- Sorted by Artist%n');
			}
			
			//Artist has no data, but album does
			if (($info['artist'] == 'Unknown Artist') && ($info['album'] <> 'Unknown Album'))  {
				//Send a message that we found data
				$this->Output->send('  %2- ID3 Tags Found%n');
				$this->Output->send('  %2- Sorted by Album%n');
			}
			
			//Artist and album have no data
			if (($info['artist'] == 'Unknown Artist') && ($info['album'] == 'Unknown Album'))  {
				$this->Output->send('  %1- Unknown Song Data%n');
				$duplicate = FALSE;
			}
			
			//Work out the destination
			$file = substr($item, strrpos($item, '/'));
			//Check if this a duplicate
			if (($this->duplicate_check($info)) && ($duplicate == TRUE)) {
				return $this->Config->read('sorted').'_duplicateMusic/'.ucwords($info['artist']).'/'.ucwords($info['album']).'/'.$file;
			} else {
				return $this->Config->read('sorted').ucwords($info['artist']).'/'.ucwords($info['album']).'/'.$file;
			}
		}
		
		/**
		 * Check that the file hasn't already been seen
		 * @param array $info The music files tag data
		 * @return boolean
		 */
		function duplicate_check($info) {
			$query = strtolower($info['artist'].'/'.$info['album'].'/'.$info['title']);
			if (in_array($query, $this->musicDuplicates)) {
				$this->Output->send('  %5- Duplicate Song File%n');
				return TRUE;
			} else {
				$this->musicDuplicates[] = $query;
				return FALSE;
			}
		}
		
		/**
		 * Parse MP3 files ID3 tags
		 * @param string $file The full path to the MP3 file
		 * @return array
		 */
		function parse_mp3_tags($file) {
			//Set default
			$data = $this->defaultTagData;
			
			//Get tag data
			$tag = GetAllMP3info($file);
			$tagData = '';
			if (isset($tag['id3']['id3v2'])) {
				$tagData = $tag['id3']['id3v2'];
			} elseif (isset($tag['id3']['id3v1'])) {
				$tagData = $tag['id3']['id3v1'];
			}
			
			//If we have valid tag data
			if ($tagData <> '') {
				//Artist Data
				if ((isset($tag['artist'])) && (stristr($tag['artist'], 'unknown') == FALSE)) {
					$data['artist'] = $tag['artist'];
				}
				
				//Album Data
				if ((isset($tag['album'])) && (stristr($tag['album'], 'unknown') == FALSE)) {
					$data['album'] = $tag['album'];
				}
				
				//Title Data
				if ((isset($tag['title'])) && (stristr($tag['title'], 'unknown') == FALSE)) {
					$data['title'] = $tag['title'];
				}
			}
			
			return $data;
		}
	}

?>
