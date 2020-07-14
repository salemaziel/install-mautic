<?php

namespace MauticPlugin\GautitBackupBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MauticPlugin\GautitBackupBundle\Integration\GautitBackupIntegration;
use MauticPlugin\GautitBackupBundle\Helper\Mysqldump;
use MauticPlugin\GautitBackupBundle\Helper\Archiv;
use MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive;
use MauticPlugin\GautitBackupBundle\Helper\HelperFunctions;
use MauticPlugin\GautitBackupBundle\Helper\DropboxClient;
class GautitBackupCommand extends ContainerAwareCommand
{
    protected $batchSize;

  

    /**
     * @var
     */
    protected $maxPerIterations;

    /**
     * @var
     */
    protected $output;

    /**
     * @var
     */
    protected $input;


	/**
     * @var
     */
    private $container;


	private $fileName;


    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setName('gautit:backup')
            ->setDescription('Take backup of db and mautic files');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {	
        $this->input  = $input;
        $this->output = $output;
		$this->container =  $this->getContainer();
		$integrationHelper = $this->container->get('mautic.helper.integration');
		$pathsHelper = $this->container->get('mautic.helper.paths');
		$root = $pathsHelper->getSystemPath('root');

	    $integrationObject = $integrationHelper->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME);
        if ($integrationObject->getIntegrationSettings()->getIsPublished()) {
			$this->output->writeln('Gautit backup process started.');
			$params =  $this->getContainer()->getParameter('mautic.parameters');
			$host  = $params['db_host'];
			$username  = $params['db_user'];
			$password  = $params['db_password'];
			$port  = $params['db_port'];
			$dbName  = $params['db_name'];
			$dsn = 'mysql:host='.$host.';dbname='.$dbName;
			$backupPath  = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'gautitbackup'.DIRECTORY_SEPARATOR;
			$filesToBackup  = $root;
		
			if(!is_dir($backupPath)){
				mkdir($backupPath);
			}
			if(!file_exists($backupPath.'.htaccess')){
				$ht = fopen($backupPath.'.htaccess','w');
				fwrite($ht,'deny from all');
				fclose($ht);
			}
			if(!file_exists($backupPath.'index.html')){
				$ht = fopen($backupPath.'index.html','w');
				fwrite($ht,'<html><body><a href="https://gautit.com" target="_blank">Mautic backup by gautit backup plugin</a></body></html>');
				fclose($ht);
			}
			if(!file_exists($backupPath.'web.config')){
				$ht = fopen($backupPath.'web.config','w');
				fwrite($ht,'<configuration><system.webServer><authorization><deny users="*" />
</authorization></system.webServer></configuration>');
				fclose($ht);
			}
			$fileNameDb = 'gautit-db'.date('-y-m-d-h-i-s').'.gz';
			$fileName = 'gautit-files'.date('-y-m-d-h-i-s');
			$this->fileName = $fileName;
			//$file = fopen($backupPath.$fileNameDb,"a");
			//fclose($file);

			$dumpSettingsDefault = array(
				'compress' => Mysqldump::GZIP
			);
			$helperFunction = new HelperFunctions();
			$helperFunction->setMemoryTimeLimit();

			$mysqlDump  = new Mysqldump($dsn, $username, $password,$dumpSettingsDefault); 
			$mysqlDump->setInfoHook(function($object, $info) {
				if ($object === 'table') {
					echo 'Taking backup of table '.$info['name'].' with total rows '. $info['rowCount'];
					echo "\n";
				}
			});
			$mysqlDump->start($backupPath.$fileNameDb);
			$this->output->writeln('Taking backup of files. Please wait.');
	
			$this->backupFiles($backupPath,$fileName,$filesToBackup);
			$this->output->writeln('Uploading files to dropbox. Please wait.');
			$this->uploadToDropBox($integrationObject,$backupPath,$fileName);
			$this->output->writeln('Uploading db files to dropbox. Please wait.');
			$this->uploadToDropBox($integrationObject,$backupPath,$fileNameDb);

		}else{
			$this->output->writeln('Gautit backup plugin not published');
		}
        return 0;
    }

   
	private function backupFiles($storeDir,$filename,$backupDir){
		
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
					
				  // ZIP
				  $za = new  \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
				  $res = $za->open($storeDir.'/'.$filename.".zip", ZipArchive::CREATE); // $backupDir
				}
				if($res === TRUE) {
				  $za->addDir($backupDir,basename($backupDir));
				  $za->close();
				}else{
				  // TAR
				  $archiv = new Archiv( $filename.".tar" );
				  chdir( $storeDir . "/" ); // $backupDir
				  $this->backup( "./", $archiv );
				  $archiv->write();
				}
			  }
			} else {
			  // LINUX
			  if(class_exists("MauticPlugin\GautitBackupBundle\helper\FlxZipArchive")) {
				// ZIP
				$za = new \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
				$res = $za->open($storeDir.'/'.$filename.".zip", ZipArchive::CREATE); // $backupDir
			  }
			  if($res === TRUE) {
				$za->addDir($backupDir,basename($backupDir));
				$za->close();
			  }else{
				// TAR
				$archiv = new archiv( $filename.".tar" );
				chdir( $storeDir . "/" ); // $backupDir
				$this->backup( "./", $archiv );
				$archiv->write();
			  }
			}
		  } else {
			//  WINDOWS
			if(class_exists("MauticPlugin\GautitBackupBundle\helper\FlxZipArchive")) {
			  // ZIP
			  $za = new \MauticPlugin\GautitBackupBundle\helper\FlxZipArchive;
			  $res = $za->open($storeDir.'/'.$filename.".zip", \ZipArchive::CREATE); // $backupDir
			}

			if($res === TRUE) {
			  $za->addDir($backupDir,basename($backupDir));
			  $za->close();
			}else{
			  // TAR
			  $archiv = new archiv( $filename.".tar" );
			  chdir( $storeDir . "/" ); // $backupDir
			  
			  $this->backup( './', $archiv );
			  $archiv->write();
			}
		  }
		  if($res === TRUE) {
			if ( @$_SERVER["SESSIONNAME"] === "Console" or substr(php_sapi_name(), 0, 3) == "cli") { 
			}else{ 
			 // echo "<p>Write .ZIP File to Server&hellip;</p>";
			} 
		  }
	}

	private function backup( $dir, &$archiv ) {

	  global $count;
	  $files = array();
	  $d = dir( $dir );
	 
	  while ( false !== ( $entry = $d->read() ) )
		$files[] = $entry;

	  $d->close();
	  foreach ( $files as $file ) {
		  
		if ( $file[0] !== "." && $file!== $this->fileName.".tar" ) {
		  if ( is_dir( $dir . $file ) ) {
			$this->backup( $dir . $file . "/", $archiv );
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
		echo "<p>Read Folder for .TAR File&hellip;</p>";
	  }
	}


	private function uploadToDropBox($integrationObject,$backupPath,$fileName){
		$allSettings  = $integrationObject->getAllSettings();
		$dropbox = new DropboxClient( array(
		'app_key'         => $allSettings['dropboxkey'],
		'app_secret'      => $allSettings['dropboxkeysecret'],
		'app_full_access' => false,
		) );
		$dropbox->SetBearerToken(array('t'=>$allSettings['dropboxaccesstoken']));
		$meta  = $dropbox->UploadFile($backupPath.$fileName);
		if(isset($meta->id)){
			return true;
		}else{
			return false;
		}
		$integrationObject->getIntegrationSettings()->getIsPublished();
	}
}