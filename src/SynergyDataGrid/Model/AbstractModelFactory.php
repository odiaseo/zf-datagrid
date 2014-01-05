<?php
namespace SynergyDataGrid\Model;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * This file is part of the Synergy package.
 *
 * (c) Pele Odiase <info@rhemastudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 *
 */
class AbstractModelFactory
    implements AbstractFactoryInterface
{
    protected $_configPrefix = 'synergydatagrid\model';

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
        $model = new BaseModel();
        /** @var $logger \SynergyCommon\Util\ErrorHandler */
        $logger = $serviceLocator->get('logger');

        $model->getLogger($logger);

        return $model;

    }
}