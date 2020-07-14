<?php
return array(
    'name'        => 'Gautit Backup',
    'description' => 'Gautit backup plugin enables you to backup from admin panel',
    'author'      => 'Gautit/Gagandeep Singh',
    'version'     => '1.0.0',
	'menu'     => array(
	  'admin' => array(
            'PLUGIN.GAUTIT.LABEL.GAUTIT.MENU' => array(
                'route'     => 'gautit_backup_admin',
                'iconClass' => 'fa-floppy-o',
                'access'    => 'admin',
                'priority'  => 60,
				'checks'    => array(
                  'integration' => [
                        'GautitBackup' => [
                            'enabled' => true,
                        ],
                    ],
                ),

            )
        )
    ),
    'routes'   => array(
        'main' => array(
            'gautit_backup_admin' => array(
                'path'       => '/gautit-backup',
                'controller' => 'GautitBackupBundle:Default:index',
				
            ),
			'gautit_backup_start' => array(
                'path'       => '/backup-start',
                'controller' => 'GautitBackupBundle:Default:start',
				'method' => 'POST'
            ),
			'gautit_backup_status' => array(
                'path'       => '/backup-status',
                'controller' => 'GautitBackupBundle:Default:status',
				'method' => 'POST'
            ),
			'gautit_backup_prestart' => array(
                'path'       => '/backup-prestart',
                'controller' => 'GautitBackupBundle:Default:preStart',
				'method' => 'POST'
            ),
			'mautic_backup_delete_action' => array(
                'path'       => '/backup-delete',
                'controller' => 'GautitBackupBundle:Default:delete',
				'method' => 'POST'
            ),
			'mautic_backup_download_action' => array(
                'path'       => '/backup-download',
                'controller' => 'GautitBackupBundle:Default:download',
				'method' => 'GET'
            ),
			'mautic_backup_deleted_action' => array(
                'path'       => '/backup-deleted',
                'controller' => 'GautitBackupBundle:Default:deleted',
				'method' => 'GET'
            ),
			'gautit_backup_savedropbox_action' => array(
                'path'       => '/backup-savedropbox',
                'controller' => 'GautitBackupBundle:Default:saveDropbox',
				'method' => 'POST'
            ),
			'gautit_backup_amazons3_action' => array(
                'path'       => '/backup-saveamazons3',
                'controller' => 'GautitBackupBundle:Default:saveAmazonS3',
				'method' => 'POST'
            ),
			'gautit_backup_google_auth_action' => array(
                'path'       => '/backup-google-auth',
                'controller' => 'GautitBackupBundle:Default:googleAuth',
				'method' => 'GET'
            )

        )
    ),
	'services' => [
		'helpers' => array(
            'plugin.gautit.helper.curlclient' => array(
                'class'     => 'MauticPlugin\GautitBackupBundle\Helper\Mysqldump'
            )
        ),
		'integrations' => [
            'plugin.gautit.integration.backup' => [
                'class'     => \MauticPlugin\GautitBackupBundle\Integration\GautitBackupIntegration::class,
                'arguments' => [
                    'mautic.factory'
                ],
            ]
        ],
		'command' => [
            'plugin.gautit.command.backup' => [
                'class'     => \MauticPlugin\GautitBackupBundle\Command\GautitBackupCommand::class,
            ]
        ],
		'helpers' => array(
            'plugin.gautit.helper.backup' => array(
                'class'     => 'MauticPlugin\GautitBackupBundle\Helper\Mysqldump'
            ),
            'plugin.gautit.helper.backup.flxzip' => array(
                'class'     => 'MauticPlugin\GautitBackupBundle\Helper\FlxZipArchive'
            )
            ,
            'plugin.gautit.helper.backup.archiv' => array(
                'class'     => 'MauticPlugin\GautitBackupBundle\Helper\Archiv'
            )
        ),
		'models' => [
            'mautic.gautitbackup.model.backup' => [
                'class'     => 'MauticPlugin\GautitBackupBundle\Model\GautitBackupModel',
            ],
        ],
		'forms' => [
            'gautit.form.type.backup.dropbox' => [
                'class'     => 'MauticPlugin\GautitBackupBundle\Form\Type\DropboxType',
            ],
            'gautit.form.type.backup.amazon3' => [
                'class'     => 'MauticPlugin\GautitBackupBundle\Form\Type\AmazonS3Type',
            ],
		]
	],
);

