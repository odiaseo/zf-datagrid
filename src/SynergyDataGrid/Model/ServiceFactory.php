<?php
    namespace SynergyDataGrid\Model;

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
    use Zend\ServiceManager\FactoryInterface;
    use Zend\ServiceManager\ServiceLocatorInterface;

    class ServiceFactory implements FactoryInterface
    {
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $baseService   = new BaseService($entityManager);

            return $baseService;
        }
    }