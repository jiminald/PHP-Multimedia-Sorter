<?php
	
	/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

	/**
	 * The Command line start
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
	
	ini_set('display_errors', 'on');
	error_reporting(E_ALL & ~E_DEPRECATED);
	
	/* Set the current working directory, this helps when in subfolders */
	$script_name = $_SERVER['argv'][0];
	define('FILENAME', $script_name);
	define('EXT', '.php');
	define('BASEDIR', str_replace("\\", "/", getcwd().'/'));
	
	/* Include core structure */
	if ((include ('lib/include.manager'.EXT)) <> 1) { die('FATAL ERROR: Could not load lib/include.manager'.EXT); }
	$include_options = array('level' => 'critical', 'path' => 'lib/');
	include_file('lib/function.generic'.EXT, $include_options);
	include_file('lib/class.manager'.EXT, $include_options);
	include_file('lib/Console_Color'.EXT, $include_options);
	$Config = include_class('Config', $include_options);
	$Output = include_class('Output', $include_options);
	
	/* Load the modules */
	include_file('lib/getID3/getid3'.EXT, $include_options);
	$include_options = array('level' => 'critical', 'path' => 'modules/');
	$module_config_manager = include_class('configManager'.EXT, $include_options);
	$module_fileManager = include_class('fileManager'.EXT, $include_options);
	$module_music = include_class('music'.EXT, $include_options);
	
	/* If we provide a config file, we are being automated so just run */
	if (in_array('@config', $_SERVER['argv'])) {
		$id = array_search('@config', $_SERVER['argv']) + 1;
		scan_run($_SERVER['argv'][$id]);
	} elseif (in_array('@help', $_SERVER['argv'])) {
		help_run();
	} else {
		/* This keeps the menu looping until we wish to exit */
		$menuLoop = TRUE;
		while ($menuLoop) {
			/* Send menu */
			$menuSelection = strtolower($Output->menu('Multimedia Sorting Menu', array('Run Scan', 'Configuration Manager', 'Command Line Help')));
			$menuOptions = array('eol' => TRUE, 'date' => FALSE);
			switch ($menuSelection) {
				case 'a': //Run Scan
					/* Get the file menu */
					$ret = $module_config_manager->file_menu(FALSE);
					$menu = $ret['menu'];
					$menuLinks = $ret['links'];
					/* Send menu */
					$menuSelection = strtolower($Output->menu('Select Configuration File', $menu));
					if ($menuSelection <> '0') {
						scan_run($menuLinks[$menuSelection]);
					} else {
						$Output->send('No config file selected.', $menuOptions);
					}
					
					$Output->send('', $menuOptions);
					/* Now we finished all the scanning and sorting, we quit */
					$menuLoop = FALSE;
				break;
				
				case 'b': //Config manager
					$module_config_manager->run();
					$Output->send('', $menuOptions);
				break;
				
				case 'c': //Command line help
					help_run();
					$Output->send('', $menuOptions);
					$menuLoop = FALSE;
				break;
				
				case '0':
					$Output->send('Exiting.', $menuOptions);
					$menuLoop = FALSE;
				break;
			}
		}
	}
	
	/**
	 * Do the scans
	 * @param string $configFile The config file to load
	 * @return void 
	 */
	function scan_run($configFile) {
		/* Connect to classes required */
		$Config = include_class('Config');
		$Output = include_class('Output');
		
		$Output->title('Starting Multimedia Scan');
		$Output->send('%5Reading Config file: '.$configFile.'%n');
		/* Read in the config file */
		if ($Config->readFile('config/'.$configFile)) {		
			/* Are we scanning music files? */
			if ($Config->read('music') == 'y') {
				$module_music = include_class('music'.EXT);
				$musicStats = $module_music->initalScan();
				$Output->statistics('Music Files', $musicStats);
			}
		} else {
			$Output->send('Missing/Invalid Config file or path', array('eol' => TRUE, 'date' => FALSE));
		}
	}
	
	/**
	 * Show commnad line help arguments
	 * @return void 
	 */
	function help_run() {
		/* Connect to classes required */
		$Output = include_class('Output');
		
		/* Set configs for the output */
		$sendOptions = array('eol' => TRUE, 'date' => FALSE);
		
		/* Generate the title and help data */
		$Output->title('Command Line Help');
		$Output->send('@help'."\t\t\t\t\t".'Shows this list', $sendOptions);
		$Output->send('@config [filename in config/]'."\t\t".'Use the specified config file', $sendOptions);
	}
	
	
?>
