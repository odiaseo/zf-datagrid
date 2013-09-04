<?php
    namespace SynergyDataGrid;

    use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
    use SynergyDataGrid\Grid\GridType\ORMGrid;
    use Zend\ServiceManager\FactoryInterface;
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
    class JqGridFactory implements FactoryInterface
    {
        /**
         * @param ServiceLocatorInterface $serviceLocator
         *
         * @return EntityGrid
         */
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            $config = $serviceLocator->get('Config');
            $grid   = new DoctrineORMGrid($config['jqgrid'], $serviceLocator);

            return $grid;
        }
    }