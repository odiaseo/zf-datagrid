<?php

    chdir(dirname(__DIR__));

    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', true);

    $basePath = realpath('../../../') . '/';

    set_include_path(
        implode(PATH_SEPARATOR,
            array($basePath,
                realpath($basePath . '/src'),
                realpath($basePath . '/vendor'),
                realpath($basePath . '/test'),
                get_include_path(),
            )
        )
    );

    $zf2Path = $basePath . 'vendor/zendframework/zendframework/library';


    if (file_exists($basePath . 'vendor/autoload.php')) {
        $loader = include $basePath . 'vendor/autoload.php';
    }

    // Support for ZF2_PATH environment variable or git submodule

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