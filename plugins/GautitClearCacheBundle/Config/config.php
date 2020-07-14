<?php

return array(
    'name'        => 'Gautit Clear Cache',
    'description' => 'Gautit clear cache plugin enables you to clear mautic cache from admin panel',
    'author'      => 'Gautit/Gagandeep Singh',
    'version'     => '1.0.0',
	'menu'     => array(
	  'admin' => array(
            'plugin.gautit.clear.cache.admin' => array(
                'route'     => 'gautit_clear_cache_admin',
                'iconClass' => 'fa-trash-o',
                'access'    => 'admin',
                'priority'  => 60,
				'checks'    => array(
                  'integration' => [
                        'GautitClearCache' => [
                            'enabled' => true,
                        ],
                    ],
                ),

            )
        )
    ),
    'routes'   => array(
        'main' => array(
            'gautit_clear_cache_admin' => array(
                'path'       => '/clearcache',
                'controller' => 'GautitClearCacheBundle:Default:clearCache',
            )
        )
    )
);