<?php

    chdir(dirname(__DIR__));

    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', true);

    $basePath = '';

    set_include_path(
        implode(PATH_SEPARATOR,
            array(
                 realpath($basePath . '/src'),
                 realpath($basePath . '/vendor'),
                 realpath($basePath . '/test'),
                 get_include_path(),
            )
        )
    );


    if (file_exists('vendor/autoload.php')) {
        $loader = include 'vendor/autoload.php';
    }

// Support for ZF2_PATH environment variable or git submodule
    if (($zf2Path = getenv('ZF2_PATH') ? : (is_dir('vendor/zendframework/zendframework/library') ? 'vendor/zendframework/zendframework/library' : false)) !== false) {
        if (isset($loader)) {
            $loader->add('Zend', $zf2Path . '/Zend');
        } else {
            include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
            Zend\Loader\AutoloaderFactory::factory(
                array(
                     'Zend\Loader\StandardAutoloader' => array(
                         'autoregister_zf' => true
                     )
                ));
        }
    }

    $classList = include 'autoload_classmap.php';


    spl_autoload_register(function ($class) use ($classList, $basePath) {
        if (isset($classList[$class])) {
            @include $classList[$class];
        } else {
            $filename = str_replace('\\\\', '/', $class) . '.php';
            @include($filename);
        }
    });

    $application            = \Zend\Mvc\Application::init(include 'tests/testconfig.php');
    $GLOBALS['application'] = $application;