<?php
    namespace SynergyDataGrid;

    use Zend\ServiceManager\ServiceManager;
    use Doctrine\Common\Annotations\AnnotationRegistry;

    /**
     * Module
     *
     * @package   Synergy
     * @copyright Pele Odiase (c) - http://www.peleodiase.com
     * @license   http://www.zfdaily.com/code/license New BSD License
     * @link      http://www.zfdaily.com
     * @link      https://bitbucket.org/dlu/dlutwbootstrap
     */
    class Module
    {
        public function init()
        {
            $lib = 'vendor/gedmo/doctrine-extensions/lib';
            AnnotationRegistry::registerAutoloadNamespace('Gedmo\Mapping\Annotation', $lib);
        }

        /* ********************** METHODS ************************** */

        public function getAutoloaderConfig()
        {
            return array(
                'Zend\Loader\ClassMapAutoloader' => array(
                    __DIR__ . '/autoload_classmap.php',
                ),
                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    ),
                ),
            );
        }

        public function getConfig()
        {
            return include __DIR__ . '/config/module.config.php';
        }

        public function getServiceConfig($e = null)
        {
            return array(
                'factories' => array(
                    'ModelService' => __NAMESPACE__ . '\Model\ServiceFactory',
                    'jqgrid'       => 'SynergyDataGrid\Grid\JqGridFactory'
                ),
            );
        }

        public function getViewHelperConfig()
        {
            return array(
                'invokables' => array(
                    'displayGrid' => 'SynergyDataGrid\View\Helper\DisplayGrid',
                )
            );
        }
    }
