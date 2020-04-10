<?php
namespace SynergyDataGrid\Helper;

use Laminas\ServiceManager\ServiceManager;

/**
 * Class BaseConfigHelper
 * @package SynergyDataGrid\Helper
 */
abstract class BaseConfigHelper
{
    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected $_serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->_serviceManager = $serviceManager;
    }

    abstract public function execute(array $arguments);
}
