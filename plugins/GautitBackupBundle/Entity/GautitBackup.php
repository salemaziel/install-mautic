<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\GautitBackupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;

/**
 * @ORM\Table(name="plugin_gautitbackup")
 * @ORM\Entity
 */
class GautitBackup
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(name="name", type="string", length=20)
     */
    protected $name;

    /**
     * @ORM\Column(name="files", type="string", length=1024)
     */
    protected $files;

    /**
     * @ORM\Column(name="status", type="string", length=255)
     */
    protected $status;

	/**
     * @ORM\Column(name="action", type="string", length=20)
     */
    protected $action;

	/**
     * @ORM\Column(name="location", type="string", length=20)
     */
    protected $location;

    /**
     * @ORM\Column(name="backup_date", type="datetime")
     */
    protected $backupDate;

    public function __construct()
    {
        $this->name = 'undefined';
        $this->backupDate = new \Datetime();
        $this->files = 'undefined';
        $this->action = 'undefined';
        $this->status = 'undefined';
		$this->location= 'undefined';
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {	
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('plugin_gautit_backup');
       //     ->setCustomRepositoryClass('MauticPlugin\GautitBackupBundle\Entity\GautitBackupRepository');
        $builder->addId();
        $builder->addNamedField('name', 'string', 'name');
        $builder->addNamedField('files', 'string', 'files');
        $builder->addNamedField('status', 'string', 'status');
        $builder->addNamedField('action', 'string', 'action');
        $builder->addNamedField('location', 'string', 'location');
        $builder->addNamedField('backupDate', 'datetime', 'backup_date');
        //$builder->build();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

 
    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBackupDate()
    {
        return $this->backupDate;
    }

    /**
     * @param \DateTime $eventDate
     *
     * @return $this
     */
    public function setBackupDate(\DateTime $backupDate)
    {
        $this->backupDate = $backupDate;

        return $this;
    }
    /**
     * @param $files
     *
     * @return $this
     */
    public function getFiles()
    {
        return $this->files;
    }
    /**
     * @param $files
     *
     * @return $this
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }
    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
	
	 /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

 
    /**
     * @param $name
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

	 /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

 
    /**
     * @param $name
     *
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }
	public static function getTableName(){
		
		return 'plugin_gautit_backup';
		
	}

}
