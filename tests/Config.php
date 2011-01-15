<?php
	
	#require '../lib/Config.php';
	require 'lib/Config.php';
	$Config = new Config;
	
	$file = 'config/default';
	
	/* Read a config file in */
	echo 'Reading config file : ';
	var_dump($Config->readFile($file));
	echo PHP_EOL;
	
	/* Read a config key */
	echo 'Reading key "sorted" : '.$Config->read('sorted').PHP_EOL.PHP_EOL;
	
	/* Read a config key */
	echo 'Writing config key to array : ';
	var_dump($Config->write('test', 'Test Value ;p'));
	echo PHP_EOL;
	
	/* Write to config file in */
	echo 'Writing config file : ';
	var_dump($Config->writeFile($file));
	echo PHP_EOL;
	
	/* Print config array */
	print_r($Config->show());
	
?>
