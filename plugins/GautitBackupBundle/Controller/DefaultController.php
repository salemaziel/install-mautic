<?php

/* @author      Gautit.com/Gagandeep Singh
 * @email       team@Gautit.com
 * @link        https://Gautit.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html

*/

namespace MauticPlugin\GautitBackupBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MauticPlugin\GautitBackupBundle\Integration\GautitBackupIntegration;
use MauticPlugin\GautitBackupBundle\Helper\Mysqldump;
use MauticPlugin\GautitBackupBundle\Helper\Archiv;
use MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive;
use MauticPlugin\GautitBackupBundle\Helper\HelperFunctions;
use MauticPlugin\GautitBackupBundle\Helper\DropboxClient;
use MauticPlugin\GautitBackupBundle\Entity\GautitBackup;
use MauticPlugin\GautitBackupBundle\Model\GautitBackupModel;
use MauticPlugin\GautitBackupBundle\Form\Type\DropboxType;
use MauticPlugin\GautitBackupBundle\Form\Type\AmazonS3Type;
use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Model\IntegrationEntityModel;
use Aws\S3\S3Client;

/**
 * Class class DefaultController extends CommonController.
 */
class DefaultController extends CommonController
{

	private $rootPath =null;

	private $backupPath = null;
	private $integrationEntityModel =null;
 /**
     * @param MauticFactory $factory
     */
    public function setFactory(MauticFactory $factory)
    {
        $this->factory = $factory;
		$this->integrationEntityModel = $factory->getModel('plugin.integration_entity');

		$pathsHelper = $this->container->get('mautic.helper.paths');
		$this->rootPath = $pathsHelper->getSystemPath('root');
		$this->backupPath = $this->rootPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'gautitbackup'.DIRECTORY_SEPARATOR;
		$pathsHelper =$this->container->get('mautic.helper.paths');
		$rootDirPath = $pathsHelper->getSystemPath('root');
		$plugin = $pathsHelper->getSystemPath('plugins');
		$pluginPath = $rootDirPath.DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR.'GautitBackupBundle'.DIRECTORY_SEPARATOR;
		//require dirname(__FILE__)."/../config-googledrive.php"; // $driveId AND $driveKey
		//require 'lib/Google_Client.php';
		//require 'lib/contrib/Google_Oauth2Service.php';
		//require 'lib/contrib/Google_DriveService.php';

		/*require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Google_Client.php');
		require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'contrib/Google_Oauth2Service.php');
		require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'contrib/Google_DriveService.php');
		*/
    }
	public function indexAction(Request $request){
		
		if (!$this->get('mautic.security')->isGranted('plugin:gautitBackup:backup:view')) {
            return $this->accessDenied();
        }
		
		$allSettings = $this->getAllSavedSettings();
		
		DropboxType::$allOptions = $allSettings;
		$dropboxForm = $this->createForm(DropboxType::class,null,array('action'=>$this->generateUrl('gautit_backup_savedropbox_action')));
		AmazonS3Type::$allOptions = $allSettings;

		$amazonS3Form = $this->createForm(AmazonS3Type::class,null,array('action'=>$this->generateUrl('gautit_backup_amazons3_action')));

	
		
		$backupModel =  $this->getModel('gautitbackup.backup');
		$repository = $this->getDoctrine()->getRepository(GautitBackup::class);
		$backups = $repository->findBy(array(), array('id' => 'DESC'));
		//print_r($backups);
		//die;	
		
		$flashes[] = [
						'type'    => 'notice',
						'msg'     => $this->get('translator')->trans('plugin.automauticly.clear.cache.notice.success'),
					];
		/*$pathsHelper = $this->container->get('mautic.helper.paths');
		$root = $pathsHelper->getSystemPath('root');
		$backupPath  = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'gautitbackup'.DIRECTORY_SEPARATOR.'test11.txt';
		HelperFunctions::uploadToDropBox($backupPath);
		*/
	
		
		return $this->delegateView(
                [
                    'viewParameters' => [
						'backup_start' => $this->generateUrl('gautit_backup_start'),
						'backup_status' => $this->generateUrl('gautit_backup_status'),
						'backup_pre_start' => $this->generateUrl('gautit_backup_prestart'),
						'existing_backups' => $backups,
						'dropbox_form' => $dropboxForm->createView(),
						'amazons3_form' => $amazonS3Form->createView(),
						'licensed' =>(isset($allSettings['l_active']) && $allSettings['l_active'] == true) ? true :false,
						'defaultTabMenu' => 'dropbox',
                    ],
                    'contentTemplate' => 'GautitBackupBundle:Backup:backup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_contact_index',
                        'mauticContent' => 'lead',
                    ],
                ]
            );           
		/* return $this->postActionRedirect(
				array_merge(
					$postActionVars,
					[
						'flashes' => $flashes,
					]
				)
			);*/
	}

	public function preStartAction(Request $request){
		$backupName = $request->request->get('backup_name');
		$backupLocation = $request->request->get('location');
		if (!$this->get('mautic.security')->isGranted('plugin:gautitBackup:backup:view')) {
            return $this->accessDenied();
        }
		/** @var \MauticPlugin\GautitBackupBundle\Model\GautitBackupModel $model */
		$backupModel =  $this->getModel('gautitbackup.backup');
		$now = new \DateTime();
		$fileTime = date('y-m-d-h-i-s');
		$fileNameDb = 'gautit-db-'.$fileTime.'.zip';
		$fileName = 'gautit-files-'.$fileTime;
		if(empty($backupName)){
			$backupName = 'gautit-files-'.$fileTime;
		}
		$logFile = 'gautit-log-'.$fileTime.'.log';
		$savedRecord = $backupModel->addBackup($backupName,json_encode(array('files'=>$fileName ,'db'=>$fileNameDb,'log'=>$logFile)),'started',json_encode($backupLocation), $now);
		if($savedRecord->getId() != null){
			$backupRecord = new \stdClass();
			$backupRecord->id = $savedRecord->getId();
			$backupRecord->name = $savedRecord->getName();
			return new JsonResponse(['backupDetails'    => $backupRecord]);
	
		}
	
	}
	public function startAction(Request $request){
		if (!$this->get('mautic.security')->isGranted('plugin:gautitBackup:backup:edit')) {
            return $this->accessDenied();
        }
		$allSettings = $this->getAllSavedSettings();
		
		$backupDetails = $request->request->get('backupDetails');
		$response = new \stdClass();
		$response->msg = '';
		$response->status = false;
		$request->getSession()->save();

		if(is_array($backupDetails) && count($backupDetails) > 0){		
			$currentBackup = $this->getDoctrine()
			->getRepository(GautitBackup::class)->find($backupDetails['id']);
			$files = json_decode($currentBackup->getFiles());
			$locations = json_decode($currentBackup->getLocation());

			$integrationHelper = $this->container->get('mautic.helper.integration');
			$pathsHelper = $this->container->get('mautic.helper.paths');
			$root = $pathsHelper->getSystemPath('root');
			$this->rootPath = $pathsHelper->getSystemPath('root');

			$integrationObject = $integrationHelper->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME);
			
			if ($integrationObject->getIntegrationSettings()->getIsPublished()) {
				//$this->output->writeln('Gautit backup process started.');
				$params =  $this->container->getParameter('mautic.parameters');
				$host  = $params['db_host'];
				$username  = $params['db_user'];
				$password  = $params['db_password'];
				$port  = $params['db_port'];
				$dbName  = $params['db_name'];
				$dsn = 'mysql:host='.$host.';dbname='.$dbName;
				$backupPath  = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'gautitbackup'.DIRECTORY_SEPARATOR;
				$filesToBackup  = $root;
				HelperFunctions::createEssentialFiles($this->backupPath,'backupdir');
				HelperFunctions::createEssentialFiles($this->backupPath,'htaccess');
				HelperFunctions::createEssentialFiles($this->backupPath,'index');
				HelperFunctions::createEssentialFiles($this->backupPath,'webconfig');
				$fileNameDb = $files->db;
				$fileName = $files->files;
				$this->fileName = $fileName;


				/** @var \MauticPlugin\GautitBackupBundle\Model\GautitBackupModel $model */
				$backupModel =  $this->getModel('gautitbackup.backup');
				$dumpSettingsDefault = array(
					'compress' => Mysqldump::ZIP
				);
				$helperFunction = new HelperFunctions();
				HelperFunctions::setMemoryTimeLimit();
				HelperFunctions::logToFile($this->backupPath.$files->log,'Backup Process Started');
				HelperFunctions::createSqlBackUp($this->backupPath.$files->db,$dsn, $username, $password,$dumpSettingsDefault,$this->backupPath.$files->log);
				try{
					$mysqlDump  = new Mysqldump($dsn, $username, $password,$dumpSettingsDefault); 
					$backupPath = $this->backupPath;
					$mysqlDump->setInfoHook(function($object, $info) use($backupPath,$files) {
						if ($object === 'table') {
							HelperFunctions::logToFile($backupPath.$files->log,'Taking backup of table '.$info['name'].' with total rows '. $info['rowCount']);
						}
					});
					$mysqlDump->start($this->backupPath.$files->db);
					HelperFunctions::logToFile($this->backupPath.$files->log,'Database backup completed successfully');
				}catch(\Exception $e){
					HelperFunctions::logToFile($this->backupPath.$files->log,'Mysql backup failed with error'.$e->getMessage());
						$response->status = false;
				}
				HelperFunctions::backupFiles($this->backupPath,$files->files,$filesToBackup,$this->backupPath.$files->log);
				if(in_array('dropbox',$locations) &&  (isset($allSettings['l_active']) &&  $allSettings['l_active'] == true)){
					HelperFunctions::uploadToDropBox($this->backupPath.$files->files.".zip",$this->backupPath.$files->log);
					HelperFunctions::uploadToDropBox($this->backupPath.$files->db,$this->backupPath.$files->log);

				}
				if(in_array('amazons3',$locations) && (isset($allSettings['l_active']) &&  $allSettings['l_active'] == true)){
					HelperFunctions::uploadToAWS($this->backupPath.$files->files.".zip",$this->backupPath.$files->log);
					HelperFunctions::uploadToAWS($this->backupPath.$files->db,$this->backupPath.$files->log);
				}
				HelperFunctions::logToFile($this->backupPath.$files->log,'The backup apparently succeeded and is now complete.');

				return new JsonResponse(['response'    => $response]);

			}
		}else{
			$response->msg ='Backup process halted.';
			$response->status = false;
		}
        return new JsonResponse(['test'    => rand()]);
	}

	public function statusAction(Request $request){
		

		$backupDetails = $request->request->get('backupDetails');
		$currentBackup = $this->getDoctrine()
			->getRepository(GautitBackup::class)->find($backupDetails['id']);
		$files = json_decode($currentBackup->getFiles());
		$pathsHelper = $this->container->get('mautic.helper.paths');
		$this->rootPath = $pathsHelper->getSystemPath('root');
		$backupPath  = $this->rootPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'gautitbackup'.DIRECTORY_SEPARATOR;
		$line ='';
		if(file_exists($this->backupPath.$files->log)){
			$f = fopen($this->backupPath.$files->log,'r');
			$cursor = -1;
			fseek($f, $cursor, SEEK_END);
			$char = fgetc($f);
			/**
			 * Trim trailing newline chars of the file
			 */
			while ($char === "\n" || $char === "\r") {
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
			}

			/**
			 * Read until the start of file or first newline char
			 */
			while ($char !== false && $char !== "\n" && $char !== "\r") {
				/**
				 * Prepend the new char
				 */
				$line = $char . $line;
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
			}
		}
	    return new JsonResponse(['message'    => $line,'status'=>true]);
	}

	public function deleteAction(Request $request){
		if (!$this->get('mautic.security')->isGranted('plugin:gautitBackup:backup:edit')) {
            return $this->accessDenied();
        }
	
		$action = $request->query->get('objectAction');
	
		switch($action){
			case 'delete':
				$backupId = $request->query->get('objectId');
				if(!empty($backupId)){
					$em = $this->getDoctrine()->getManager();
					$backup = $em->getRepository(GautitBackup::class)->findBy(['id' => $backupId]);
					
					if(count($backup) > 0){
						$backup = $backup[0];
						$files = $backup->getFiles();
						if(!empty($files)){
							$files = json_decode($files);
							if(file_exists($this->backupPath.$files->files.'.zip')){
								unlink($this->backupPath.$files->files.'.zip');
							}
							if(file_exists($this->backupPath.$files->db)){
								unlink($this->backupPath.$files->db);
							}
							if(file_exists($this->backupPath.$files->log)){
								unlink($this->backupPath.$files->log);
							}
						}
						$em->remove($backup);
						$em->flush();
						$redirectUrl = $this->generateUrl('gautit_backup_admin');
						$this->addFlash($this->translator->trans('PLUGIN.GAUTIT.NOTICE.BACKUSPDELETED'));
						return new RedirectResponse($redirectUrl);

					}else{
						return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.BACKUNOTFOUND'));
					}
				}else{
					return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.PARAMETERMISSING'));
				}
			break;
			case 'batchDelete':
				$backupIds = $request->query->get('ids');
				$ids  = json_decode($backupIds);
				if(is_array($ids) && count($ids)> 0){

					
					$em = $this->getDoctrine()->getManager();
					$qb = $em->createQueryBuilder();
					$qb->select('b');
					$qb->from('GautitBackupBundle:GautitBackup', 'b');
					$qb->where($qb->expr()->in('b.id', $ids));

					//ArrayCollection
					$results = $qb->getQuery()->getResult();
					if(count($results)){
						foreach($results as $result){
							$files = json_decode($result->getFiles());
							if(file_exists($this->backupPath.$files->files.'.zip')){
								unlink($this->backupPath.$files->files.'.zip');
							}
							if(file_exists($this->backupPath.$files->db)){
								unlink($this->backupPath.$files->db);
							}
							if(file_exists($this->backupPath.$files->log)){
								unlink($this->backupPath.$files->log);
							}
						
						}
						
						$qb1 = $em->createQueryBuilder();
						$qb1->delete('GautitBackupBundle:GautitBackup', 's');
						$qb1->where($qb1->expr()->in('s.id', $ids));
						$qb1->getQuery()->execute();
					
						/*$backupModel =  $this->getModel('gautitbackup.backup');
						$entities = $backupModel->deleteEntities($ids);
						print_R($entities);
						die;
						$em->flush();

						$em->clear('MauticPlugin\GautitBackupBundle\Entity\GautitBackup');
						*/
						//return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.BACKUSPDELETED'));
						$redirectUrl = $this->generateUrl('gautit_backup_admin');
						$this->addFlash($this->translator->trans('PLUGIN.GAUTIT.NOTICE.BACKUSPDELETED'));
						return new RedirectResponse($redirectUrl);

					}else{
						return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.BACKUNOTFOUND'));
					}

				
					//$sql = "DELETE FROM ".GautitBackup::getTableName();
					//$em = $this->getDoctrine()->getManager();
					//$stmt = $em->getConnection()->prepare($sql);
					//$stmt->execute();
				}else{
					return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.PARAMETERMISSING'));
				}
				
				
			break;
		}
	
	}

	public function downloadAction(Request $request){
	
		$backupId = $request->query->get('backId');
		$objectAction = $request->query->get('objectAction');
		if (!$this->get('mautic.security')->isGranted('plugin:gautitBackup:backup:edit')) {
            return $this->accessDenied();
        }
		if(!empty($backupId) && !empty($objectAction)){
			$backupRecord = $this->getDoctrine()
			->getRepository(GautitBackup::class)->find($backupId);	
			if(!empty($backupRecord)){
				$files = $backupRecord->getFiles();
				$files = json_decode($files);
				switch($objectAction){
					case 'files':
						if(!empty($files->files)){
							$filePath = $this->backupPath.$files->files.'.zip';
							if(file_exists($filePath)){
								return $this->processDownload($filePath);
							}else{
								return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.FILENOTEXISTS'));
							}
						}
					break;
					case 'db':
						if(!empty($files->db)){
							$filePath = $this->backupPath.$files->db;
							if(file_exists($filePath)){
								return $this->processDownload($filePath);
							}else{
								return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.FILENOTEXISTS'));
							}
						}
					break;
					case 'log':
						if(!empty($files->log)){
							$filePath = $this->backupPath.$files->log;
						
							if(file_exists($filePath)){
								return $this->processDownload($filePath);
							}else{
								return $this->redirectWithNotice($this->translator->trans('PLUGIN.GAUTIT.NOTICE.FILENOTEXISTS'));
							}
						}
					break;
				}
			}
		}else{
			return $this->redirectWithNotice();
		}
	
	}

	private function processDownload($filePath){
		$response = new Response();
		// Set headers
		$response->headers->set('Cache-Control', 'private');
		$response->headers->set('Content-type', mime_content_type($filePath));
		$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filePath) . '";');
		$response->headers->set('Content-length', filesize($filePath));
		// Send headers before outputting anything
		$response->sendHeaders();
		$response->setContent(readfile($filePath));
		return $response;
	}

	private function redirectWithNotice($msg,$route=''){
		if(empty($route)){
			$redirectUrl = $this->generateUrl('gautit_backup_admin');
		}else{
			$redirectUrl = $this->generateUrl($route);

		}
		$postActionVars = [
		'returnUrl'       =>   $redirectUrl,
		];
		$flashes[] = [
			'type'    => 'error',
			'msg'     => $msg,
		];
		 return $this->postActionRedirect(
			array_merge(
				$postActionVars,
				[
					'flashes' => $flashes,
				]
			)
		);
	}
	public function deletedAction(Request $request){
		return $this->redirectWithNotice($msg);
	}
	public function saveDropboxAction(Request $request){
		$settingsSavedDrop = false;
		$repository = $this->getDoctrine()->getRepository(GautitBackup::class);
		$backups = $repository->findBy(array(), array('id' => 'DESC'));
		$allSettings = $this->getAllSavedSettings();
		DropboxType::$allOptions = $allSettings;
		AmazonS3Type::$allOptions = $allSettings;
		$dropboxForm = $this->createForm(DropboxType::class,null,array('action'=>$this->generateUrl('gautit_backup_savedropbox_action')));
		$amazons3Form = $this->createForm(AmazonS3Type::class,null,array('action'=>$this->generateUrl('gautit_backup_amazons3_action')));
		$dropboxForm->handleRequest($request);
		if ($dropboxForm->isSubmitted() && $dropboxForm->isValid()) {
			$data = $dropboxForm->getData();
			/*if(!isset($data['dropboxupload'])){
				$data['dropboxupload'] = NULL;
			}*/
			$newSettings = array_merge($allSettings,$data);
			
			if(count($newSettings) > 0){
				$this->updateApiSettings($newSettings);
				$settingsSavedDrop= true;
			}
		}
		return $this->delegateView(
                [
                    'viewParameters' => [
						'backup_start' => $this->generateUrl('gautit_backup_start'),
						'backup_status' => $this->generateUrl('gautit_backup_status'),
						'backup_pre_start' => $this->generateUrl('gautit_backup_prestart'),
						'existing_backups' => $backups,
						'dropbox_form' => $dropboxForm->createView(),
						'amazons3_form' => $amazons3Form->createView(),
						'defaultTabMenu' => 'dropbox',
						'licensed' =>(isset($allSettings['l_active']) && $allSettings['l_active'] == true) ? true :false,
						'settingsSavedDrop' =>$settingsSavedDrop
                    ],
                    'contentTemplate' => 'GautitBackupBundle:Backup:backup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#gautit_backup_admin',
                        'mauticContent' => 'lead',
                    ],
                ]
            );      
		
    }
	public function saveAmazonS3Action(Request $request){
		$settingsSaved = false;
		$repository = $this->getDoctrine()->getRepository(GautitBackup::class);
		$allSettings = $this->getAllSavedSettings();
		DropboxType::$allOptions = $allSettings;
		AmazonS3Type::$allOptions = $allSettings;
		$backups = $repository->findBy(array(), array('id' => 'DESC'));
		$dropboxForm = $this->createForm(DropboxType::class,null,array('action'=>$this->generateUrl('gautit_backup_savedropbox_action')));
		$amazons3Form = $this->createForm(AmazonS3Type::class,null,array('action'=>$this->generateUrl('gautit_backup_amazons3_action')));
		$amazons3Form->handleRequest($request);
		if ($amazons3Form->isSubmitted() && $amazons3Form->isValid()) {
			$data = $amazons3Form->getData();
			/*if(!$data['amazons3upload']){
				$data['amazons3upload'] = NULL;
			}*/
			$newSettings = array_merge($allSettings,$data);
			if(count($newSettings) > 0){
				$this->updateApiSettings($newSettings);
				$settingsSaved = true;
			}
		}
		
		return $this->delegateView(
                [
                    'viewParameters' => [
						'backup_start' => $this->generateUrl('gautit_backup_start'),
						'backup_status' => $this->generateUrl('gautit_backup_status'),
						'backup_pre_start' => $this->generateUrl('gautit_backup_prestart'),
						'existing_backups' => $backups,
						'dropbox_form' => $dropboxForm->createView(),
						'amazons3_form' => $amazons3Form->createView(),
						'defaultTabMenu' => 'amazons3',
						'licensed' =>(isset($allSettings['l_active']) && $allSettings['l_active'] == true) ? true :false,
						'settingsSaved'=> $settingsSaved 
                    ],
                    'contentTemplate' => 'GautitBackupBundle:Backup:backup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#gautit_backup_admin',
                        'mauticContent' => 'lead',
                    ],
                ]
            );      
    }

	public function googleAuth(){
		$client = new Google_Client();
		$client->setAuthConfig('/path/to/client_credentials.json');
		$client->addScope(Google_Service_Drive::DRIVE);

	}

	private function updateApiSettings($allSettings){
		$integrationHelper = $this->container->get('mautic.helper.integration');
		$repositoryIntegration = $this->getDoctrine()->getRepository(Integration::class);
		$integrationRecord = $repositoryIntegration->findOneBy(array('name'=>GautitBackupIntegration::INTEGRATION_NAME));
		$integrationObject = $integrationHelper->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME);
		$encrypedKeys = $integrationObject->encryptKeys($allSettings);
	
		$integrationRecord->setApiKeys($encrypedKeys);
		$this->integrationEntityModel->saveEntity($integrationRecord);
	}
	private function getAllSavedSettings(){
		$integrationHelper = $this->container->get('mautic.helper.integration');
		$integrationObject = $integrationHelper->getIntegrationObject(GautitBackupIntegration::INTEGRATION_NAME);
		$allSettings = $integrationObject->getAllSettings();
		return $allSettings;
	}
}