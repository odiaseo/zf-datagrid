<?php
namespace SynergyDataGrid\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class GridServiceFactory
 * @package SynergyDataGrid\Service
 */
class SubGridServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $serviceLocator
     * @param string $requestedName
     * @param array|null $options
     * @return SubGridService
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $service = new SubGridService();
        $service->setServiceLocator($serviceLocator);
        $service->setLogger($serviceLocator->get('logger'));

        return $service;
    }
}
