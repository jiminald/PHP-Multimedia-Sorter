<?php
	
	/**
	 * Output to command line and user input
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
	
	class Output {
		
		/**
		 * Send output to the console/terminal/screen
		 * @param string $message The content of the message to send to the screen
		 * @param array $options Options of the output, "eol" for a new line at the end of the message. "date" to show a timstamp of the message
		 * @return void
		 */
		function send($message, $options = array('eol' => TRUE, 'date' => TRUE)) {
			//Check we want a new line
			if ($options['eol'] == TRUE) {
				$message .= PHP_EOL;
			}
			
			//Check we want a date prefixing the message
			if ($options['date'] == TRUE) {
				$message = '%K['.date('d/M/Y H:i:s').']%n '.$message;
			}
			
			//Color parse
			$message = Console_Color::convert($message.'%n');
			//Send to screen
			fwrite(STDOUT, $message);
		}
		
		/**
		 * Send a message to the screen and await user input
		 * @param string $message The content of the message to send to the screen
		 * @param array $options Options of the output, "eol" for a new line at the end of the message. "date" to show a timstamp of the message
		 * @return string|boolean 
		 */
		function askForInput($message, $options = array('eol' => TRUE, 'date' => TRUE)) {
			$this->send($message, $options);
			$input = trim(fgets(STDIN));
			if ($input <> '') {
				return $input;
			} else {
				return FALSE;
			}
		}
		
		/**
		 * Title of a section sent to screen
		 * @param string $text Title Text
		 * @return void
		 */
		function title($text) {
			//Dynamically work out the dashes in the title
			$titleLen = strlen($text);
			$titleDash = '';
			for ($i = 0; $i < $titleLen; $i++) {
				$titleDash .= '-';
			}

			$this->send(PHP_EOL.'%B'.$text.PHP_EOL.$titleDash, array('eol' => TRUE, 'date' => FALSE));
		}
		
		/**
		 * Generate a terminal menu and return the selected result
		 * @param string $title Menu title
		 * @param array $data Menu data
		 * @return string|boolean
		 */
		function menu($title, $data = array()) {
			//Generate the title
			$this->title($title);
			
			//Screen settings
			$menuOptions = array('eol' => TRUE, 'date' => FALSE);
			
			//Start at letter "A", Hard limit at "Z"
			$letter = 65;
			foreach ($data as $item) {
				if ($letter <= 90) {
					$this->send(' %9'.chr($letter).')%n '.$item, $menuOptions);
					$letter++;
				}
			}
			//Send the exit menu item
			$this->send(' %90)%n Exit', $menuOptions);
			
			//Find the selection and run the appropriate case
			return $this->askForInput('Make your selection : ', array('eol' => FALSE, 'date' => FALSE));
		}
		
		/**
		 * Statistics on how long a task took
		 * @param string $title Statistics Title
		 * @param array $data Statistics data
		 * @return void
		 */
		function statistics($title, $data) {
			//Show the stats
			$statsOptions = array('eol' => TRUE, 'date' => FALSE);
			$this->title(PHP_EOL.$title.' Statistics');
			$this->send('%KStarted:%n   '.date('d/M/Y H:i:s', $data['started']), $statsOptions);
			$this->send('%KFinished:%n  '.date('d/M/Y H:i:s', $data['finished']), $statsOptions);
			$this->send('%KDuration:%n  '.($data['finished'] - $data['started']).' seconds', $statsOptions);
			if (isset($data['files'])) {
				$this->send('%KProcessed:%n '.$data['files'].' files', $statsOptions);
			}
		}
		
	} //End of class
?>
