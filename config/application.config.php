<?php

\date_default_timezone_set('Europe/London');
return array(
    'modules'                 => array(
        'Zend\Db',
        'Zend\Log',
        'Zend\Mail',
        'Zend\Mvc\Console',
        'Zend\Mvc\I18n',
        'Zend\I18n',
        'Zend\Mvc\Plugin\FilePrg',
        'Zend\Mvc\Plugin\FlashMessenger',
        'Zend\Mvc\Plugin\Identity',
        'Zend\Mvc\Plugin\Prg',
        'Zend\Navigation',
        'Zend\Paginator',
        'Zend\Serializer',
        'Zend\ServiceManager\Di',
        'Zend\Session',
        'Zend\Router',
        'Zend\Form',
        'Zend\InputFilter',
        'Zend\Filter',
        'Zend\Validator',
        'Zend\Hydrator',
        'Zend\Cache',
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
