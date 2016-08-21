<?php
namespace SynergyDataGrid\Grid;

/**
 * This file is part of the Synergy package.
 *
 * (c) Pele Odiase <info@rhemastudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 *
 */

use Interop\Container\ContainerInterface;
use SynergyDataGrid\Grid\GridType\DoctrineODMGrid;
use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
use SynergyDataGrid\Util\ArrayUtils;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Class AbstractGridFactory
 * @package SynergyDataGrid\Grid
 */
class AbstractGridFactory implements AbstractFactoryInterface
{

    protected $_configPrefix = 'jqgrid';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (substr($requestedName, 0, strlen($this->_configPrefix)) != $this->_configPrefix) {
            return false;
        }

        return true;
    }

    /**
     * @param ContainerInterface $serviceLocator
     * @param string $requestedName
     * @param array|null $options
     * @return DoctrineODMGrid
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $gridType   = trim(str_replace($this->_configPrefix, '', $requestedName), '\\');
        $config     = $serviceLocator->get('Config');
        $gridConfig = $config['jqgrid'];

        if (array_key_exists('factories', $gridConfig)) {
            $util = new ArrayUtils();
            foreach ((array)$gridConfig['factories'] as $alias) {
                if ($serviceLocator->has($alias)) {
                    $addConfig  = $serviceLocator->get($alias);
                    $gridConfig = $util->arrayMergeRecursiveCustom($gridConfig, $addConfig, true);
                }
            }
        }

        switch ($gridType) {
            case 'odm':
                $manager = $serviceLocator->get('doctrine.entitymanager.odm_default');
                $class   = DoctrineODMGrid::class;
                break;
            default:
                $manager = $serviceLocator->get('doctrine.entitymanager.orm_default');
                $class   = DoctrineORMGrid::class;
        }

        /** @var DoctrineODMGrid $grid */
        $grid = new $class($gridConfig, $serviceLocator, $manager);

        return $grid;
    }
}
