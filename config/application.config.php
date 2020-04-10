<?php

\date_default_timezone_set('Europe/London');
return array(
    'modules'                 => array(
        'Laminas\Mvc\Plugin\Identity',
        'Laminas\Mvc\Plugin\FilePrg',
        'Laminas\Mvc\Plugin\FlashMessenger',
        'Laminas\Mvc\Plugin\Prg',
        'Laminas\Session',
        'Laminas\Mvc\I18n',
        'Laminas\Form',
        'Laminas\InputFilter',
        'Laminas\Filter',
        'Laminas\I18n',
        'Laminas\Db',
        'Laminas\Log',
        'Laminas\Mail',
        'Laminas\Mvc\Console',
        'Laminas\Navigation',
        'Laminas\Paginator',
        'Laminas\Serializer',
        'Laminas\ServiceManager\Di',
        'Laminas\Router',
        'Laminas\Validator',
        'Laminas\Hydrator',
        'Laminas\Cache',
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
);
