<?php
namespace SynergyDataGrid\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class GridServiceFactory
 * @package SynergyDataGrid\Service
 */
class GridServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $serviceLocator
     * @param string $requestedName
     * @param array|null $options
     * @return GridService
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $service = new GridService();
        $service->setServiceLocator($serviceLocator);
        $service->setLogger($serviceLocator->get('logger'));

        return $service;
    }
}
