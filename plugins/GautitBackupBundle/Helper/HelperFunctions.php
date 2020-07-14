<?php
namespace MauticPlugin\GautitBackupBundle\Helper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\GautitBackupBundle\Integration\GautitBackupIntegration;
use MauticPlugin\GautitBackupBundle\Helper\DropboxClient;
use MauticPlugin\GautitBackupBundle\Helper\Mysqldump;
use MauticPlugin\GautitBackupBundle\Helper\Archiv;
use MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive;
use MauticPlugin\GautitBackupBundle\Helper\HelperFunctions;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Symfony\Component\DependencyInjection\Container;

Class HelperFunctions{

 /**
     * @var IntegrationHelper
     */
    private  static $container;


	private static $fileName;

    /**
     * @param IntegrationHelper $helper
     * @param LoggerInterface   $logger
     */
    public static function init(Container $helper)
    {
        self::$container = $helper;
    }
	public static function dbox_init() {
		//error_reporting( E_ALL );
		self::enable_implicit_flush();
		#echo "<pre>";
	}

	public static function dbox_store_token( $token, $name ) {
		is_dir( 'dropbox/tokens' ) || mkdir( 'dropbox/tokens' );
		if ( ! file_put_contents( "dropbox/tokens/$name.token", serialize( $token ) ) ) {
			die( '<p>Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b></p>' );
		}
	}

	public static function dbox_token_load( $name ) {
		if ( ! file_exists( "dropbox/tokens/$name.token" ) ) {
			return null;
		}

		return @unserialize( @file_get_contents( "dropbox/tokens/$name.token" ) );
	}

	public static function dbox_token_delete( $name ) {
		@unlink( "dropbox/tokens/$name.token" );
	}

	public static function enable_implicit_flush() {
		if ( function_exists( 'apache_setenv' ) )
			@apache_setenv( 'no-gzip', 1 );
		@ini_set( 'zlib.output_compression', 0 );
		@ini_set( 'implicit_flush', 1 );
		for ( $i = 0; $i < ob_get_level(); $i ++ )
			ob_end_flush();
		ob_implicit_flush( 1 );
		echo "<!-- " . str_repeat( ' ', 2000 ) . " -->";
	}

	public static function setMemoryTimeLimit(){
		set_time_limit(0);  // NO TIME LIMIT
		ini_set('memory_limit', '-1'); // IMP - Make sure the Memory can handle large Folders/Files (backup.php)
	}

	public static function uploadToDropBox($fileWithPath,$logFile=''){
		$allSettings  = self::$container->get('mautic.helper.integration')->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME)->getAllSettings();
		$dropbox = new DropboxClient( array(
		'app_key'         => $allSettings['dropboxKey'],
		'app_secret'      => $allSettings['dropboxSecret'],
		'app_full_access' => false,
		) );
		$dropbox->SetBearerToken(array('t'=>$allSettings['dropboxAccessToken']));
		if(file_exists($fileWithPath)){
			if(!empty($logFile))
				self::logToFile($logFile,'Uploading files to dropbox');
				$meta  = $dropbox->UploadFile($fileWithPath);
			if(isset($meta->id)){
				if(!empty($logFile))
				self::logToFile($logFile,'Files uploaded to dropbox');
				return true;
			}else{
				if(!empty($logFile))
				self::logToFile($logFile,'Files uploading to dropbox failed');
				return false;
			}
		}else{
			self::logToFile($logFile,'Files uploading to dropbox failed files does not exists');
			//do whatever if file does not exists		
		}
	}

	public static function createEssentialFiles($backupPath,$type){
			switch($type){
				case 'index':
					if(!file_exists($backupPath.'index.html')){
						$ht = fopen($backupPath.'index.html','w');
						fwrite($ht,'<html><body><a href="https://gautit.com" target="_blank">Mautic backup by gautit backup plugin</a></body></html>');
						fclose($ht);
					}
				break;
				case 'webconfig':
					if(!file_exists($backupPath.'web.config')){
						$ht = fopen($backupPath.'web.config','w');
						fwrite($ht,'<configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>');
						fclose($ht);
					}
				break;
				case 'htaccess':
					if(!file_exists($backupPath.'.htaccess')){
						$ht = fopen($backupPath.'.htaccess','w');
						fwrite($ht,'deny from all');
						fclose($ht);
					}
				break;
				case 'backupdir':
					if(!is_dir($backupPath)){
						mkdir($backupPath);
					}
				break;
			
			}
	
	}
	public static function createSqlBackUp($fileNamePathDb,$dsn, $username, $password,$dumpSettingsDefault,$logFile =''){
	
		$mysqlDump  = new Mysqldump($dsn, $username, $password,$dumpSettingsDefault); 
		$mysqlDump->setInfoHook(function($object, $info) use ($logFile) {
			if ($object === 'table') {
				if(!empty($logFile)){
					self::logToFile($logFile,'Taking backup of table '.$info['name'].' with total rows '. $info['rowCount']);
				//	echo 'Taking backup of table '.$info['name'].' with total rows '. $info['rowCount'];
				}
			}
		});
		$mysqlDump->start($fileNamePathDb);
	}

	public static function logToFile($logFile,$logMessage){
		if(!empty($logFile)){
			$f = fopen($logFile,'a');
			fwrite($f,$logMessage."\r\n");
			fclose($f);
		
		}
	}

	public static function backupFiles($storeDir,$filename,$backupDir,$logFile=''){
		self::$fileName = $filename;
		$allSettings  = self::$container->get('mautic.helper.integration')->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME)->getAllSettings();
	//  WORK
		  if ( strtolower( substr( substr( php_uname(), 0, strpos( php_uname(), " " ) ), 0, 3 ) ) !== "win" ) {
			$res;
			//  LINUX DAEMON
			if ( @$DONT_WORK_YET && function_exists("pcntl_fork") ) {
			  pcntl_fork(); //  FORK PROCESS
			  if (@$_SERVER["SESSIONNAME"] === "Console" or substr( php_sapi_name(), 0, 3 ) == "cli" ) {
				print "\n\nDaemon running\n\n";
			  } else {
				echo "<p>Daemon running&hellip;</p>";
			  }
			  posix_setsid(); //  MAKE CHILD PROCESS SESSION LEADER
			  while (true) { //  DAEMON SCRIPT
				if(class_exists("MauticPlugin\GautitBackupBundle\helper\FlxZipArchive")) {
				  $za->ignoreDir = $storeDir;	 
				  // ZIP
				  $za = new  \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
				  $res = $za->open($storeDir.'/'.$filename.".zip", \ziparchive::CREATE); // $backupDir
				}
				if($res === TRUE) {
				  $za->addDir($backupDir,basename($backupDir));
				  $za->close();
				}else{
				  // TAR
				  $archiv = new Archiv( $filename.".tar" );
				  chdir( $storeDir . "/" ); // $backupDir
				  self::backup( "./", $archiv );
				  $archiv->write();
				}
			  }
			} else {
			  // LINUX
			
			  if(class_exists("MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive")) {
				// ZIP
				$za->ignoreDir = $storeDir;
				$za = new \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
				$res = $za->open($storeDir.'/'.$filename.".zip", \ziparchive::CREATE); // $backupDir
			  }
			 
			  if($res === TRUE) {
				$za->addDir($backupDir,basename($backupDir),$logFile);
				$za->close();
				/*if(is_array($allSettings) && $allSettings['dropboxupload'] == 1){
					self::uploadToDropBox($storeDir.'/'.$filename.".zip",$logFile);
				}
				if(is_array($allSettings) && $allSettings['amazons3upload'] == 1){
					self::uploadToAWS($storeDir.'/'.$filename.".zip",$logFile);
				}*/
			  }else{
				
				// TAR
				$archiv = new archiv( $filename.".tar" );
				chdir( $storeDir . "/" ); // $backupDir
				self::backup( "./", $archiv );
				$archiv->write();
			  }
			}
		  } else {
			//  WINDOWS
			if(class_exists("MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive")) {
			  // ZIP
			  $za = new \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
			  $za->ignoreDir = $storeDir;
			  $res = $za->open($storeDir.'/'.$filename.".zip", \ziparchive::CREATE); // $backupDir
			}

			if($res === TRUE) {
			  $za->addDir($backupDir,basename($backupDir),$logFile);
			  $za->close();
			 /* if(is_array($allSettings) && $allSettings['dropboxupload'] == 1){
				self::uploadToDropBox($storeDir.'/'.$filename.".zip",$logFile);
			  }
			  if(is_array($allSettings) && $allSettings['amazons3upload'] == 1){
					self::uploadToAWS($storeDir.'/'.$filename.".zip",$logFile);
			  }*/
			}else{
			  // TAR
			  $archiv = new archiv( $filename.".tar" );
			  chdir( $storeDir . "/" ); // $backupDir
			  
			  self::backup( './', $archiv );
			  if($archiv->write()){
			  }else{
			   self::logToFile($logFile,'The backup apparently failed.');
			  }
			}
		  }
		  if($res === TRUE) {
			if ( @$_SERVER["SESSIONNAME"] === "Console" or substr(php_sapi_name(), 0, 3) == "cli") { 
			}else{ 
			  //"<p>Write .ZIP File to Server&hellip;</p>";
			} 
		  }
	}

	public static function backup( $dir, &$archiv ) {

	  global $count;
	  $files = array();
	  $d = dir( $dir );
	 
	  while ( false !== ( $entry = $d->read() ) )
		$files[] = $entry;

	  $d->close();
	  foreach ( $files as $file ) {
		  // && $file!== self::$fileName.".tar"
		if ( $file[0] !== "." ) {
		  if ( is_dir( $dir . $file ) ) {
			self::backup( $dir . $file . "/", $archiv );
		  } elseif ( is_file( $dir . $file ) ) {
			$_dir = str_replace( "./", "",  $dir );
			if ( @$_SERVER["SESSIONNAME"] === "Console" or substr( php_sapi_name(), 0, 3 ) == "cli" ) {
			  $z++;
			  if ( $z === 10 ) print "[/]\r"; print "Processing file".$dir.'\n';
			  if ( $z === 20) print "[-]\r"; print "Processing file".$dir.'\n';
			  if ( $z === 30 ) { 
				print "[\]\r";
				$z = 0;
			  }
			  
			}
			$archiv->add( $_dir . $file );
		  }
		}
	  }
	  if ( @$_SERVER["SESSIONNAME"] === "Console" or substr( php_sapi_name(), 0, 3 ) == "cli" ) {
	  } elseif($count < 1) {
		$count++;
		//echo "<p>Read Folder for .TAR File&hellip;</p>";
	  }
	}

	public static function uploadToAWS($file,$logFile=''){
		$allSettings  = self::$container->get('mautic.helper.integration')->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME)->getAllSettings();
		$client = new S3Client([
			'version' => 'latest',
			'region' => 'us-east-1',
			'credentials' => [
				'key'    => $allSettings['amazons3Key'],
				'secret' => $allSettings['amazons3Secret']
			]
		]);
		self::logToFile($logFile,'Starting upload to Amazons3.');
		self::logToFile($logFile,'Checking bucket in Amazons3.');
		$bucketName = self::getAmazonS3BucketFromList($client,$allSettings['amazons3bucket'],$logFile);
		if($bucketName == false){
			$result = self::createAmazonS3Bucket($client,$allSettings['amazons3bucket']);
			if (is_object($result)){
				self::processAwsUpload($client,$allSettings,$file,$logFile);
			}
		}else{
			self::processAwsUpload($client,$allSettings,$file,$logFile);
		}
		
	
	}
	
	private static function processAwsUpload($client,$allSettings,$file,$logFile){
		$logFile = $logFile;
		try {
			$result = $client->putObject([
				'Bucket' => $allSettings['amazons3bucket'],
				'Key'    => basename($file),
				'Body'   => fopen($file, 'r'),
				'ACL'    => 'private',
				'@http' => [
					'progress' => function ($downloadTotalSize, $downloadSizeSoFar, $uploadTotalSize, $uploadSizeSoFar) use($logFile) {
						self::logToFile($logFile,sprintf(
							"%s of %s uploaded to amazons3",
						   // $downloadSizeSoFar,
						   // $downloadTotalSize,
							self::convertToReadableSize($uploadSizeSoFar),
							self::convertToReadableSize($uploadTotalSize)
						));
					}
				]
			]);
			//echo $result->get('ObjectURL');
		} catch (Aws\S3\Exception\S3Exception $e) {
			self::logToFile($logFile,"There was an error uploading the file");
			//echo $e->getMessage();
		}
	
	}

	// Call this function to convert bytes to KB/MB/GB/TB
	public static function convertToReadableSize($size){
	  $base = log($size) / log(1024);
	  $suffix = array("", "KB", "MB", "GB", "TB");
	  $f_base = floor($base);
	  return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
	}

	public static function getAmazonS3BucketFromList($s3Client,$bucketName,$logFile){
		$foundBucket =false;
		try {
			$buckets = $s3Client->listBuckets();
			foreach($buckets as $bucket){
				if(is_array($bucket) && count($bucket) > 0){
					foreach($bucket as $bc){
						if($bc['Name'] == $bucketName){
							$foundBucket = $bc;
						}
					}
				}
			}
		}catch(\Exception $e){
			self::logToFile($logFile,$e->getMessage());
		}
		return $foundBucket;
	}

	public static function createAmazonS3Bucket($s3Client,$bucketName){
		try {
            $result = $s3Client->createBucket([
                'Bucket' => $bucketName,
            ]);
			return $result;
		} catch (AwsException $e) {
        //    echo $e->getMessage();
          //  echo "\n";
            return false;
        }

        if ($result['@metadata']['statusCode'] == 200) {
            return true;
        } else {
            return false;
        }
	
	}

}