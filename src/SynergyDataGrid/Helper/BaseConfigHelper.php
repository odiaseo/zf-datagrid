<?php
namespace SynergyDataGrid\Helper;

use Zend\ServiceManager\ServiceManager;

/**
 * Class BaseConfigHelper
 * @package SynergyDataGrid\Helper
 */
abstract class BaseConfigHelper
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $_serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->_serviceManager = $serviceManager;
    }

    abstract public function execute(array $arguments);
}
