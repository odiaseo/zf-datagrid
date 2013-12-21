<?php
namespace SynergyDataGrid\Grid;

use SynergyDataGrid\Grid\GridType\DoctrineODMGrid;
use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractGridFactory
    implements AbstractFactoryInterface
{

    protected $_configPrefix = 'jqgrid';

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (substr($requestedName, 0, strlen($this->_configPrefix)) != $this->_configPrefix) {
            return false;
        }

        return true;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $gridType = trim(str_replace($this->_configPrefix, '', $requestedName), '\\');
        $config   = $serviceLocator->get('Config');

        switch ($gridType) {
            case 'odm':
                $manager = $serviceLocator->get('doctrine.entitymanager.odm_default');
                $class   = 'SynergyDataGrid\Grid\GridType\DoctrineODMGrid';
                break;
            default:
                $manager = $serviceLocator->get('doctrine.entitymanager.orm_default');
                $class   = 'SynergyDataGrid\Grid\GridType\DoctrineORMGrid';
        }
 
        $grid = new $class($config['jqgrid'], $serviceLocator, $manager);

        return $grid;
    }
}