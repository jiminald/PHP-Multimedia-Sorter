<?php
	
	class configManager {
		
		/**
		 * Send the menu options to the user
		 * @return void
		 */
		function run() {
			//Connect to classes required
			$Config = include_class('Config');
			$Output = include_class('Output');
			
			//Set configs for the output
			$menuOptions = array('eol' => TRUE, 'date' => FALSE);
			
			//Get the file menu
			$ret = $this->file_menu(TRUE);
			$menu = $ret['menu'];
			$menuLinks = $ret['links'];
			
			//Send menu
			$menuSelection = strtolower($Output->menu('Configuration Manager', $menu));
			if ($menuSelection == FALSE) {
				$Output->send('No selection made.', $menuOptions);
			} elseif ($menuSelection == '0') { 
				//Do Nothing, but we need this to stop
			} else {
				//If this is a new file, then ask for the name
				if ($menuLinks[$menuSelection] == 'newfile') {
					$file = $Output->askForInput('Filename : ', array('eol' => FALSE, 'date' => FALSE));
					if ((strpos($file, '.') == 0) || ((substr($file, strpos($file, '.'))) <> '.conf')) {
						$file .= '.conf';
					}
				} else {
					//If its neither of the above, then we have a file
					$file = $menuLinks[$menuSelection];
					//Read in the config file
					$Config->readFile('config/'.$file);
				}
				
				//Run through the options
				$inputOptions = array('eol' => FALSE, 'date' => FALSE);
				
				//Unsorted folder
				$unsorted = '';
				while ($unsorted == '') {
					$unsorted = $Output->askForInput('Unsorted Directory ['.$Config->read('unsorted').']: ', $inputOptions);
					if (($unsorted == '') && ($Config->read('unsorted') <> FALSE)) {
						$unsorted = $Config->read('unsorted');
					}
					$Config->write('unsorted', $unsorted);
				}
				
				//Sorted folder
				$sorted = '';
				while ($sorted == '') {
					$sorted = $Output->askForInput('Sorted Directory ['.$Config->read('sorted').']: ', $inputOptions);
					if (($sorted == '') && ($Config->read('sorted') <> FALSE)) {
						$sorted = $Config->read('sorted');
					}
					$Config->write('sorted', $sorted);
				}
				
				//Scan for music
				$music = '';
				while ($music == '') {
					$music = $Output->askForInput('Scan for Music? ['.$Config->read('music').']: ', $inputOptions);
					if (($music == '') && ($Config->read('music') <> FALSE)) {
						$music = $Config->read('music');
					}
					$Config->write('music', $music);
				}
				
				$Config->writeFile('config/'.$file);
				$Output->send('File Saved as "'.$file.'".', $menuOptions);
			}
		} //End of function "run"
		
		/**
		 * Generate a list of the configuration files
		 * @param boolean $newFile Show the option of a new configuration file
		 * @return array
		 */
		function file_menu($newFile = FALSE) {
			//Menu
			if ($newFile == TRUE) {
				$menu = array('New File');
				$links = array('a' => 'newfile');
				$letter = 66;
			} else {
				$menu = array();
				$links = array();
				$letter = 65;
			}
			//Work out the menu items using the config folder
			$configDir = scandir(getcwd().'/config/');
			foreach ($configDir as $item) {
				if (($item <> '.') && ($item <> '..') && ($letter <= 90)) {
					$menu[] = $item;
					$links[strtolower(chr($letter))] = $item;
					$letter++;
				}
			}
			
			return array('menu' => $menu, 'links' => $links);
		}
	} //End of class
	
?>
