<?php
namespace MauticPlugin\GautitBackupBundle\Helper;
use MauticPlugin\GautitBackupBundle\Helper\HelperFunctions;
class FlxZipArchive extends \ziparchive {

		public  $ignoreDir = '';
		/**
		 * Add a Dir with Files and Subdirs to the archive
		 *
		 * @param string $location Real Location
		 * @param string $name Name in Archive
		 * @access private
		 **/
		public function addDir($location,$name,$logFile='') {
			$this->addEmptyDir($name);
			$this->addDirDo($location, $name,$logFile);
		 } // EO addDir;
		/**
		 * Add Files & Dirs to archive.
		 *
		 * @param string $location Real Location
		 * @param string $name Name in Archive
		 * @access private
		 **/
		private function addDirDo($location, $name,$logFile='') {
			global $filename;
			global $count;
			global $storeDir;
		
			$name .= '/';
			$location .= '/';
			// Read all Files in Dir
			$dir = opendir ($location);
			if ( @$_SERVER["SESSIONNAME"] === "Console" or substr( php_sapi_name(), 0, 3 ) == "cli" ) {
			} elseif($count < 1) {
				$count++;
				//echo "<p>Read Folder for .ZIP File&hellip;</p>";
			}
			$z =0;
			while ($file = readdir($dir))
			{
					
				 if(basename($this->ignoreDir) == $file){
					continue;
				 }
	
        if ( $file == basename($storeDir) ) continue; // DON'T BACKUP THE ZIP/TAR FOLDER
				if ( $file == '.' || $file == '..' || $file == $filename.'.zip' ) continue;
				// Rekursiv, If dir: FlxZipArchive::addDir(), else ::File();
				$do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
			
				
				if($do == 'addDir'){
				
					HelperFunctions::logToFile($logFile,'Adding '.str_replace("/","\\",$location).$file.' to zip');
					$this->$do($location . $file, $name . $file,$logFile);
				}else{
					$this->$do($location . $file, $name . $file);
				}
				if ( @$_SERVER["SESSIONNAME"] === "Console" or substr( php_sapi_name(), 0, 3 ) == "cli" ) {
					$z++;
					if ( $z === 10 ) print "[/]\r";
					if ( $z === 20) print "[-]\r";
					if ( $z === 30 ) {
						print "[\]\r";
						$z = 0;
					}
				}
			}

		} // EO addDirDo();

	}
