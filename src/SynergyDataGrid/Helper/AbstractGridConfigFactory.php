<?php
namespace SynergyDataGrid\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class AbstractGridConfigFactory
 * @package SynergyDataGrid\Helper
 */
class AbstractGridConfigFactory implements AbstractFactoryInterface
{
    protected $_configPrefix = 'synergy\helper\\';

    /**
     * @param ContainerInterface $serviceLocator
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $serviceLocator, $requestedName)
    {
        $config       = $serviceLocator->get('config');
        $configHelper = str_replace($this->_configPrefix, '', $requestedName);

        if (substr($requestedName, 0, strlen($this->_configPrefix)) == $this->_configPrefix
            && isset($config['synergy']['config_helpers'][$configHelper])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        /** @var $serviceLocator \Laminas\ServiceManager\ServiceManager */
        $configHelper = str_replace($this->_configPrefix, '', $requestedName);
        $config       = $serviceLocator->get('config');

        $helperClass = $config['synergy']['config_helpers'][$configHelper];

        /** @var $class \SynergyDataGrid\Helper\BaseConfigHelper */
        $service = new $helperClass;
        if ($service instanceof BaseConfigHelper) {
            $service->setServiceManager($serviceLocator);
        }

        return $service;
    }
}