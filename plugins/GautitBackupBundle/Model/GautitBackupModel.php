<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\GautitBackupBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\GautitBackupBundle\Entity\GautitBackup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GautitBackupModel.
 */
class GautitBackupModel extends FormModel
{
 /**
     * @param string    $name
     * @param string    $files
     * @param string    $status
     * @param \DateTime $backupDate
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function addBackup($name, $files, $status, $location, \DateTime $backupDate = null)
    {
        try {
			$gautitBackup = new GautitBackup();
			$gautitBackup->setName($name);
			$gautitBackup->setFiles($files);
			$gautitBackup->setLocation($location);
			$gautitBackup->setStatus($status);
		   
			if (null !== $backupDate) {
				$gautitBackup->setBackupDate($backupDate);
			}
		
			$this->em->persist($gautitBackup);
			$this->em->flush();
			return $gautitBackup; 
		}catch(\Exception $e){
			return false;
		}

    }

	public function deleteEntities($ids)
    {
        $entities = parent::deleteEntities($ids);
		return $entities;
	}

}
