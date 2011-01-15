<?php
	
	require 'lib/Output.php';
	$Output = new Output;
	
	/* Send a message to the screen.
	 * Sample Output: [13/Jan/2011 16:48:38] Test Message */
	$Output->send('Test Message');
	
	/* Send a message to the screen and await input.
	 * Sample Output: [13/Jan/2011 16:48:38] What is your name?: */
	$Output->askForInput('What is your name?: ');
?>
