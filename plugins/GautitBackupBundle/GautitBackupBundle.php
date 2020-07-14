<?php
/* @author      Gautit.com/Gagandeep Singh
 * @email       team@Gautit.com
 * @link        https://Gautit.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html

*/
namespace MauticPlugin\GautitBackupBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\GautitBackupBundle\Helper\HelperFunctions;

class GautitBackupBundle extends PluginBundleBase
{
	public function boot()
    {
        parent::boot();
		$pathsHelper =$this->container->get('mautic.helper.paths');
		$rootDirPath = $pathsHelper->getSystemPath('root');
		$plugin = $pathsHelper->getSystemPath('plugins');
		$pluginPath = $rootDirPath.DIRECTORY_SEPARATOR.$plugin.DIRECTORY_SEPARATOR.'GautitBackupBundle'.DIRECTORY_SEPARATOR;
		

		/*require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Google_Client.php');
		require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'contrib/Google_Oauth2Service.php');
		require_once($pluginPath.'Lib'.DIRECTORY_SEPARATOR.'googledrive'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'contrib/Google_DriveService.php');*/
	
       // HelperFunctions::init($this->container->get('mautic.helper.integration'));
        HelperFunctions::init($this->container);
    }
}
