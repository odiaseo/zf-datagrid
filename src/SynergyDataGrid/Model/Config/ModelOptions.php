<?php

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
namespace SynergyDataGrid\Model\Config;

use Zend\Stdlib\AbstractOptions;

class ModelOptions
    extends AbstractOptions
{
    protected $_grid;

    protected $_entity;

    protected $_rows;

    protected $sidx;

    protected $sord;

    protected $_fieldName;

    protected $_page;

    protected $_gridConfig;

    protected $_treeFilter;

    protected $_filters;

    protected $_sortOrder;

    protected $_customQueryBuilder;

    protected $_subGridFilter;

    public function setCustomQueryBuilder($customQueryBuilder)
    {
        $this->_customQueryBuilder = $customQueryBuilder;
    }

    public function getCustomQueryBuilder()
    {
        return $this->_customQueryBuilder;
    }

    public function setEntity($entity)
    {
        $this->_entity = $entity;
    }

    public function getEntity()
    {
        return $this->_entity;
    }

    public function setFieldName($fieldName)
    {
        $this->_fieldName = $fieldName;
    }

    public function getFieldName()
    {
        return $this->_fieldName;
    }

    public function setFilters($filters)
    {
        $this->_filters = $filters;
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    public function setGrid($grid)
    {
        $this->_grid = $grid;
    }

    public function getGrid()
    {
        return $this->_grid;
    }

    public function setGridConfig($gridConfig)
    {
        $this->_gridConfig = $gridConfig;
    }

    public function getGridConfig()
    {
        return $this->_gridConfig;
    }

    public function setPage($page)
    {
        $this->_page = $page;
    }

    public function getPage()
    {
        return $this->_page;
    }

    public function setRows($rows)
    {
        $this->_rows = $rows;
    }

    public function getRows()
    {
        return $this->_rows;
    }

    public function setSortOrder($sortOrder)
    {
        $this->_sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->_sortOrder;
    }

    public function setSubGridFilter($subGridFilter)
    {
        $this->_subGridFilter = $subGridFilter;
    }

    public function getSubGridFilter()
    {
        return $this->_subGridFilter;
    }

    public function setTreeFilter($treeFilter)
    {
        $this->_treeFilter = $treeFilter;
    }

    public function getTreeFilter()
    {
        return $this->_treeFilter;
    }

    public function setSidx($sidx)
    {
        $this->sidx = $sidx;
    }

    public function getSidx()
    {
        return $this->sidx;
    }

    public function setSord($sord)
    {
        $this->sord = $sord;
    }

    public function getSord()
    {
        return $this->sord;
    }
}