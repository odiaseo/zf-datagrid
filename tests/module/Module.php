<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace SynergyDataGridTest;


class Module
{
    public function getConfig()
    {
        if (file_exists(__DIR__ . '/../test.local.php')) {
            $config = include __DIR__ . '/../test.local.php';
        } else {
            $config = array();
        }

        $serviceConfig = include __DIR__ . '/../test.global.php';

        $merged = array_merge($serviceConfig, $config);
        return $merged;

    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}