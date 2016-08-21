<?php
namespace SynergyDataGrid\Grid\Adapter;

/*
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
use SynergyDataGrid\Grid\GridType\BaseGrid;
use SynergyDataGrid\Model\BaseModel;

/**
 * Pagination Adapter to paginate results
 *
 * @author  Pele Odiase
 * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
 * @package mvcgrid
 */
abstract class QueryAdapter
{
    /**
     * Doctrine Service Model
     *
     * @var \SynergyDataGrid\Model\BaseModel
     */
    protected $_service;
    /**
     * Filtering parameters, to apply before fetching
     *
     * @var array
     */
    protected $_filter;
    /**
     * Sorting parameters, to apply before fetching
     *
     * @var array
     */
    protected $_sort;
    /**
     * Mapping human-readable constants to DQL operatores
     *
     * @var array
     */
    protected $_operator
        = array(
            'EQUAL'                 => '= ?',
            'NOT_EQUAL'             => '!= ?',
            'LESS_THAN'             => '< ?',
            'LESS_THAN_OR_EQUAL'    => '<= ?',
            'GREATER_THAN'          => '> ?',
            'GREATER_THAN_OR_EQUAL' => '>= ?',
            'BEGIN_WITH'            => 'LIKE ?',
            'NOT_BEGIN_WITH'        => 'NOT LIKE ?',
            'END_WITH'              => 'LIKE ?',
            'NOT_END_WITH'          => 'NOT LIKE ?',
            'CONTAIN'               => 'LIKE ?',
            'NOT_CONTAIN'           => 'NOT LIKE ?'
        );
    /**
     * Tree node data
     *
     * @var array
     */
    protected $_treeFilter;
    /**
     *
     * @var \SynergyDataGrid\Grid\GridType\BaseGrid
     */
    protected $_grid;

    /**
     * Set up base PaginatorAdapter options
     *
     * @param BaseGrid $grid
     * @param BaseModel $service
     * @param bool $filter array of filter options
     * @param array $sort array of sort options
     * @param null $treeData
     */
    public function __construct(BaseGrid $grid, BaseModel $service, $filter = false, $sort = array(), $treeData = null)
    {
        $this->setService($service);
        $this->setFilter($filter);
        $this->setSort($sort);
        $this->setGrid($grid);
        $this->setTreeFilter($treeData);
    }

    /**
     * Get service model instance
     *
     * @return BaseModel
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Set service model instance
     *
     * @param BaseModel $service
     *
     * @return $this
     */
    public function setService(BaseModel $service)
    {
        $this->_service = $service;

        return $this;
    }

    /**
     * Get filter setting
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;

        return $this;
    }

    /**
     * Get sort setting
     *
     * @return array
     */
    public function getSort()
    {
        return $this->_sort;
    }

    /**
     * @param $sort
     *
     * @return $this
     */
    public function setSort($sort)
    {
        $this->_sort = $sort;

        return $this;
    }

    /**
     * @return BaseGrid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * @param BaseGrid $grid
     *
     * @return $this
     */
    public function setGrid(BaseGrid $grid)
    {
        $this->_grid = $grid;

        return $this;
    }

    /**
     * @param $treeData
     *
     * @return $this
     */
    public function setTreeFilter($treeData)
    {
        $this->_treeFilter = $treeData;

        return $this;
    }

    /**
     * @return array
     */
    public function getTreeFilter()
    {
        return $this->_treeFilter;
    }
}