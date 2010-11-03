<?php
	
	/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

	/**
	 * The Command line start
	 *
	 * PHP version 5
	 *
	 * @package    PHP-Multimedia-Sorter
	 * @author     Jiminald <code@jiminald.co.uk>
	 * @copyright  2010 Jiminald
	 * @license    See LICENCE file
	 * @version    1.0beta1
	 * @link       http://jiminald.co.uk
	 */
	
	ini_set('display_errors', 'off');
	#error_reporting(E_ALL & ~E_DEPRECATED);

	//Global Variables
	global $stats, $options;
	$stats = array('started' => time(), 'files' => 0);

	//Load required classes
	require_once 'file_manager.php';
	require_once 'codecs/getid3.php';

	//Global functions
	function _userinput($message, $default = '', $supported = array()) {
		if ($default <> '') {
			$message .= ' [Default: '.$default.']';
		}
		fwrite(STDOUT, $message.': ');
		$input = trim(fgets(STDIN));
		if ($input <> '') {
			if (count($supported) > 0) {
				if (in_array($input, $supported)) {
					return $input;
				} else {
					exit('Invalid Input.');
				}
			} else {
				return $input;
			}
		} else {
			return $default;
		}
	}

	$options = array();
	$stamp = null;

	if (in_array('@use-config', $_SERVER['argv'])) {
		$position = array_search('@use-config', $_SERVER['argv']) + 1;
		if ((isset($_SERVER['argv'][$position])) && (substr($_SERVER['argv'][$position], 0, 1) <> '@')) {
			$stats['configfile'] = realpath('./'.$_SERVER['argv'][$position]);
			$options = json_decode(file_get_contents(realpath('./'.$_SERVER['argv'][$position])), TRUE);
		} else {
			$stats['configfile'] = realpath('./config');
			$options = json_decode(file_get_contents(realpath('./config')), TRUE);
		}
	} else {
		$options['unsorted'] = realpath(_userinput('Unsorted Directory', './unsorted/')).'/';
		$options['sorted'] = realpath(_userinput('Sorted Directory', './sorted/')).'/';
		$options['music'] = _userinput('Scan for Music', 'y', array('y', 'n'));
	}

	if (in_array('@show-config', $_SERVER['argv'])) {
		print_r($options);
		sleep(5);
	}

	//Check what file types we are doing
	if ($options['music'] == 'y') {
		$stats['scan_type'] = 'Music';
		$options['ext'] = array('mp3', 'wma', 'mp4', 'm4a', 'wav', 'flac', 'ogg', 'aac', 'midi', 'mac');
	}

	echo PHP_EOL.'--------------- Starting Scan of '.$stats['scan_type'].'-------------------------'.PHP_EOL.PHP_EOL;
	/* Start the scan in the base unsorted directory */
	_scan($options['unsorted']);

	echo PHP_EOL.PHP_EOL.'--------------- STATISTICS -------------------------'.PHP_EOL;
	if (isset($stats['configfile'])) {
		echo '| Config File: '.$stats['configfile'].PHP_EOL;
	}
	echo '| Started: '.date('d/M/Y H:i:s', $stats['started']).PHP_EOL
		.'| Finished: '.date('d/M/Y H:i:s').PHP_EOL
		.'| Duration: '.(time() - $stats['started']).' seconds'.PHP_EOL
		.'| Processed: '.$stats['files'].PHP_EOL
		.'--------------- STATISTICS -------------------------'.PHP_EOL;

	if (in_array('@show-config', $_SERVER['argv'])) {
		unset($options['ext']);
		echo PHP_EOL.PHP_EOL.'--------------- CONFIG -------------------------'.PHP_EOL
			.'| '.json_encode($options).PHP_EOL
			.'--------------- CONFIG -------------------------'.PHP_EOL;
	}
?>
