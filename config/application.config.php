<?php

use SynergyCommon\Service\ServiceLocatorAwareInitializer;
use SynergyCommon\Service\ServiceManagerAwareInitializer;

\date_default_timezone_set('Europe/London');
return array(
    'modules'                 => array(
        'DoctrineModule',
        'DoctrineORMModule',
        'SynergyCommon',
        'SynergyDataGrid',
    ),
    'module_listener_options' => array(
        'module_paths'             => array(
            './module',
            './vendor',
        ),
        'config_glob_paths'        => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        /** Whether or not to enable a configuration cache.
         *
         * If enabled, the merged configuration will be cached and used in
         * subsequent requests.
         */
        'config_cache_enabled'     => false,
        'config_cache_key'         => 'synergy_grid_config',
        'module_map_cache_enabled' => false,
        'module_map_cache_key'     => 'synergy_grid_module',
        'cache_dir'                => 'data/cache',
    ),
    'service_manager'         => [
        'initializers' => [
            'ServiceManagerAwareInitializer' => ServiceManagerAwareInitializer::class,
            'ServiceLocatorAwareInitializer' => ServiceLocatorAwareInitializer::class,
        ],
    ]
);
