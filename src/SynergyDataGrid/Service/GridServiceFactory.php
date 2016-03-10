<?php
namespace SynergyDataGrid\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GridServiceFactory
 * @package SynergyDataGrid\Service
 */
class GridServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new GridService();
        $service->setServiceManager($serviceLocator);
        $service->setLogger($serviceLocator->get('logger'));

        return $service;
    }
}
