<?php

/* @author      Gautit.com/Gagandeep Singh
 * @email       team@gautit.com
 * @link        https://gautit.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html

*/

namespace MauticPlugin\GautitClearCacheBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class class DefaultController extends CommonController.
 */
class DefaultController extends CommonController
{
	public function clearCacheAction(Request $request){

		$appDir = $this->container->getParameter('kernel.root_dir');
		$cacheDir =  $appDir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'prod';
		$referer = $request->headers->get('referer');
		if($referer){
			$postActionVars = [
					'returnUrl'       =>    $referer,
			];
		}else{
			$redirectUrl = $this->generateUrl('mautic_dashboard_index');
			$postActionVars = [
					'returnUrl'       =>   $redirectUrl,
			];
		}
		if($this->rrmDir($cacheDir)){
			$flashes[] = [
							'type'    => 'notice',
							'msg'     => $this->get('translator')->trans('plugin.gautit.clear.cache.notice.success'),
						];
		}else{
			$flashes[] = [
							'type'    => 'error',
							'msg'     => $this->get('translator')->trans('plugin.gautit.clear.cache.notice.error'),
						];
		}
		 return $this->postActionRedirect(
				array_merge(
					$postActionVars,
					[
						'flashes' => $flashes,
					]
				)
			);
	}

	private function rrmDir($dir) {

		if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
		   if ($object != "." && $object != "..") {
			 if (filetype($dir."/".$object) == "dir") $this->rrmDir($dir."/".$object); else unlink($dir."/".$object);
		   }
		 }
		 reset($objects);
		 return rmdir($dir);
	   }

	}
}