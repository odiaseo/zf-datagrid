<?php
namespace SynergyDataGrid\Helper;

use SynergyDataGrid\Helper\BaseConfigHelper;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractGridConfigFactory
    implements AbstractFactoryInterface
{
    protected $_configPrefix = 'synergy\helper\\';

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
        $config = $serviceLocator->get('config');
        $configHelper = str_replace($this->_configPrefix, '', $requestedName);

        if (substr($requestedName, 0, strlen($this->_configPrefix)) == $this->_configPrefix
            && isset($config['synergy']['config_helper'][$configHelper])
        ) {
            return true;
        }

        return false;
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
        /** @var $serviceLocator \Zend\ServiceManager\ServiceManager */
        $configHelper = str_replace($this->_configPrefix, '', $requestedName);
        $config       = $serviceLocator->get('config');

        $helperClass = $config['synergy']['config_helper'][$configHelper];

        /** @var $class \SynergyDataGrid\Config\BaseConfig */
        $service = new $helperClass;
        if ($service instanceof BaseConfigHelper) {
            $service->setServiceManager($serviceLocator);
        }

        return $service;
    }
}