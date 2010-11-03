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
	 * @copyright  2010 Jiminald
	 * @license    See LICENCE file
	 * @version    1.00beta1
	 * @link       http://jiminald.co.uk
	 */
	
	function _scan($directory) {
		global $stats, $options;
		$stamp = 0;
		$getID3 = new getID3;

		$unsorted_dir = scandir(realpath($directory));
		foreach ($unsorted_dir as $item) {
			if (substr($item, 0, 1) <> '.') {

				//Date/Time check
				if (date('d/M/Y H:i') <> $stamp) {
					$stamp = date('d/M/Y H:i');
					echo PHP_EOL.'--------------- '.$stamp.' -------------------------'.PHP_EOL.PHP_EOL;
				}

				//What are we looking at?
				echo $item;

				if (is_dir(realpath($directory.$item))) {
					echo PHP_EOL.' - Scanning Directory'.PHP_EOL;
					_scan($directory.$item.'/');
				} else {
					$info = pathinfo($item);
					if ((isset($info['extension'])) && (in_array($info['extension'], $options['ext']))) {
						//Get Tag information
						$tag = $getID3->analyze($directory.$item);
						/*echo '  - ';
						var_dump(isset($tag['tags_html']));
						sleep(2);*/
						if (isset($tag['tags_html'])) {
							$keys = array_keys($tag['tags_html']);
							$tag = $tag['tags_html'][$keys[0]];

							//Check the Artist, Album and Title tags are set
							$info = array();
							if (isset($tag['artist'])) {
								$info['artist'] = implode(' ', $tag['artist']);
								if (stristr($info['artist'], 'unknown')) {
									$info['artist'] = 'Unknown Artist';
								}
							}
							if (isset($tag['album'])) {
								$info['album'] = implode(' ', $tag['album']);
								if (stristr($info['album'], 'unknown')) {
									$info['album'] = 'Unknown Album';
								}
							}
							if (isset($tag['title'])) {
								$info['title'] = implode(' ', $tag['title']);
								if (stristr($info['title'], 'unknown')) {
									$info['title'] = 'Unknown Title';
								}
							}
						} else {
							$info = array('artist' => 'unknown', 'album' => 'unknown', 'title' => 'unknown');
						}
						//Show what we know
						echo PHP_EOL.' - ID3 Tags Found:'.PHP_EOL.'      Artist: '.$info['artist'].PHP_EOL.'      Album: '.$info['album'].PHP_EOL.'      Track: '.$info['title'];

						//Move said file to its new location
						#$result = movefile($info, $options['unsorted'].$item, $options['sorted']);
						$result = movefile($info, $directory.$item, $options['sorted']);
						if ($result['result'] == TRUE) {
							echo $result['message'].PHP_EOL;
							$stats['files']++;
						} else {
							echo 'Failed to move.'.PHP_EOL;
						}

						//Clean up
						unset($tag, $info, $result);
					}
				}
			}
		}

	}

	function movefile($info, $source, $dest) {
		$result = FALSE;
		$cmd = array('filecmd' => 'copy', 'folderPermission' => 0755, 'filePermission' => 0755);
		if (($result == FALSE) && (isset($info['artist'])) && (isset($info['album']))) {
			$result = smartCopy($source, $dest.$info['artist'].'/'.$info['album'].'/', $cmd);
			#echo '+ARTIST +ALBUM = '.(string)$result.PHP_EOL;
			$return = array('message' => PHP_EOL.' - Sorted using Artist and Album');
		}

		//If theres no artist but album, put in unknown artist
		if (($result == FALSE) && (!isset($info['artist'])) && (isset($info['album']))) {
			$result = smartCopy($source, $dest.'unknown/artist/'.$info['album'].'/', $cmd);
			#echo '-ARTIST +ALBUM = '.(string)$result.PHP_EOL;
			$return = array('message' => PHP_EOL.' - Sorted using Album, Unknown Artist');
		}

		//If theres no album but artist, put in unknown artist
		if (($result == FALSE) && (isset($info['artist'])) && (!isset($info['album']))) {
			$result = smartCopy($source,$dest.'unknown/'.$info['artist'].'/album/', $cmd);
			#echo '+ARTIST -ALBUM = '.(string)$result.PHP_EOL;
			$return = array('message' => PHP_EOL.' - Sorted using Artist, Unknown Album');
		}

		//If theres no artist and album, put in unknown artist
		if (($result == FALSE) && (!isset($info['artist'])) && (!isset($info['album']))) {
			$result = smartCopy($source, $dest.'unknown/artist/album/', $cmd);
			#echo '-ARTIST -ALBUM = '.(string)$result.PHP_EOL;
			$return = array('message' => PHP_EOL.' - Unknown Song');
		}

		$return['result'] = $result;
		return $return;
	}

	/**
	* Create a new directory, and the whole path.
	*
	* If  the  parent  directory  does  not exists, we will create it,
	* etc.
	* @todo
	*     - PHP5 mkdir functoin supports recursive, it should be used
	* @author baldurien at club-internet dot fr
	* @param string the directory to create
	* @param int the mode to apply on the directory
	* @return bool return true on success, false else
	* @previousNames mkdirs
	*/

	function makeAll($dir, $mode = 0777, $recursive = true) {
		if( is_null($dir) || $dir === "" ){
			return FALSE;
		}

		if( is_dir($dir) || $dir === "/" ){
			return TRUE;
		}
		if( makeAll(dirname($dir), $mode, $recursive) ){
			return mkdir($dir, $mode);
		}
		return FALSE;
	}

	/**
	 * Copies file or folder from source to destination, it can also do
	 * recursive copy by recursively creating the dest file or directory path if it wasn't exist
	 * Use cases:
	 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
	 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
	 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest
	 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
	 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
	 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
	 * @todo
	 *  - Should have rollback so it can undo the copy when it wasn't completely successful
	 *  - It should be possible to turn off auto path creation feature f
	 *  - Supporting callback function
	 *  - May prevent some issues on shared enviroments : <a href="http://us3.php.net/umask" title="http://us3.php.net/umask">http://us3.php.net/umask</a>
	 * @param $source //file or folder
	 * @param $dest ///file or folder
	 * @param $options //folderPermission,filePermission
	 * @return boolean
	 */
	function smartCopy($source, $dest, $options=array('filecmd' => 'copy', 'folderPermission'=>0755,'filePermission'=>0755)) {
		$result=false;

		//For Cross Platform Compatibility
		if (!isset($options['noTheFirstRun'])) {
			$source=str_replace('\\','/',$source);
			$dest=str_replace('\\','/',$dest);
			$options['noTheFirstRun']=true;
		}

		if (is_file($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if (!file_exists($dest)) {
					makeAll($dest,$options['folderPermission'],true);
				}
				$__dest=$dest."/".basename($source);
			} else {
				$__dest=$dest;
			}
			if ($options['filecmd'] == 'mv') {
				$result=rename($source, $__dest);
			} else {
				$result=copy($source, $__dest);
			}
			chmod($__dest,$options['filePermission']);

		} elseif(is_dir($source)) {
			if ($dest[strlen($dest)-1]=='/') {
				if ($source[strlen($source)-1]=='/') {
					//Copy only contents
				} else {
					//Change parent itself and its contents
					$dest=$dest.basename($source);
					@mkdir($dest);
					chmod($dest,$options['filePermission']);
				}
			} else {
				if ($source[strlen($source)-1]=='/') {
					//Copy parent directory with new name and all its content
					@mkdir($dest,$options['folderPermission']);
					chmod($dest,$options['filePermission']);
				} else {
					//Copy parent directory with new name and all its content
					@mkdir($dest,$options['folderPermission']);
					chmod($dest,$options['filePermission']);
				}
			}

			$dirHandle=opendir($source);
			while($file=readdir($dirHandle))
			{
				if($file!="." && $file!="..")
				{
					$__dest=$dest."/".$file;
					$__source=$source."/".$file;
					//echo "$__source ||| $__dest<br />";
					if ($__source!=$dest) {
						$result=smartCopy($__source, $__dest, $options);
					}
				}
			}
			closedir($dirHandle);

		} else {
			$result=false;
		}
		return $result;
	}

?>
