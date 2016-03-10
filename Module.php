<?php
namespace SynergyDataGrid;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Zend\ServiceManager\ServiceManager;

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

    public function getServiceConfig()
    {
        return array(
            'aliases'            => array(
                'synergy\service\grid'    => 'SynergyDataGrid\Service\GridService',
                'synergy\service\subgrid' => 'SynergyDataGrid\Service\SubGridService',
            ),
            'shared'             => array(
                'jqgrid'                => false,
                'synergydatagrid\model' => false,
            ),
            'abstract_factories' => array(
                'SynergyDataGrid\Model\AbstractModelFactory',
                'SynergyDataGrid\Grid\AbstractGridFactory',
                'SynergyDataGrid\Helper\AbstractGridConfigFactory'
            ),
            'factories'          => array(
                'logger'                                 => 'SynergyCommon\Service\LoggerFactory',
                'SynergyDataGrid\Service\GridService'    => 'SynergyDataGrid\Service\GridServiceFactory',
                'SynergyDataGrid\Service\SubGridService' => 'SynergyDataGrid\Service\SubGridServiceFactory',
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
