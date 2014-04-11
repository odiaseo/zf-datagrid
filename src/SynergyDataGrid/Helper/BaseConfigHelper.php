<?php
namespace SynergyDataGrid\Helper;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

abstract class BaseConfigHelper
    implements ServiceManagerAwareInterface
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