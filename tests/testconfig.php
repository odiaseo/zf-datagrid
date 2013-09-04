<?php
    return array(
        'modules'                 => array(
            'SynergyDataGrid',
            'DoctrineModule',
            'DoctrineORMModule'
        ),
        'module_listener_options' => array(
            'config_glob_paths' => array(
                'config/autoload/{,*.}{global,local}.php',
                __DIR__.'/configuration.php'
            ),
            'module_paths'      => array(
                './module',
                './vendor'
            ),
        ),
    );
