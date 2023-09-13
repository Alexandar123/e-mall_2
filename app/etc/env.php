<?php
return [
    'backend' => [
        'frontName' => 'admin'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'e514d4d6a4a12723c9d9aee71f30b4c0'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'ad4565df_e_mall',
                'username' => 'ad4565df_devdyna',
                'password' => 'MixesLoftyFrugalPries',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => '/var/run/redis-multi-ad4565df.redis/redis.sock',
            'port' => '0',
            'database' => '2',
            'compression_library' => 'gzip'
        ]
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '375_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => '/var/run/redis-multi-ad4565df.redis/redis.sock',
                    'database' => '1',
                    'port' => '0'
                ]
            ],
            'page_cache' => [
                'id_prefix' => '375_',
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => '/var/run/redis-multi-ad4565df.redis/redis.sock',
                    'database' => '0',
                    'port' => '0'
                ]
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => '58cd776fb50de87c47b14a983038ba8a'
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'downloadable_domains' => [
        'c358e5c39e.nxcli.io'
    ],
    'install' => [
        'date' => 'Wed, 08 Mar 2023 20:58:41 +0000'
    ]
];
