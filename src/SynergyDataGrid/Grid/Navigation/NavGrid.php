<?php
namespace SynergyDataGrid\Grid\Navigation;

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
use SynergyDataGrid\Grid\Property;

/**
 * NavGrid class for work with Navigation Control in JqGrid
 *
 * @author  Pele Odiase
 * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
 * @package mvcgrid
 */
class NavGrid extends Property
{
    /**
     * Edit parameters
     *
     * @var array
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:form_editing#editGridRow
     */
    protected $_editParameters = array();

    /**
     * Add parameters
     *
     * @var array
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:form_editing#Adding%20Row
     */
    protected $_addParameters = array();

    /**
     * Delete parameters
     *
     * @var array
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:form_editing#delGridRow
     */
    protected $_deleteParameters = array();

    /**
     * Search parameters
     *
     * @var array
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:singe_searching
     */
    protected $_searchParameters = array();

    /**
     * View parameters
     *
     * @var array
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:form_editing#viewGridRow
     */
    protected $_viewParameters = array();

    /**
     * JqGrid instance
     *
     * @var \SynergyDataGrid\Grid\GridType\BaseGrid
     */
    protected $_grid;

    /**
     *  Set up base NavGrid options
     *
     * @param mixed $grid
     * @param array $options
     */
    public function __construct($grid, $options = array())
    {
        $this->setOptions($options);
        $this->setGrid($grid);
    }

    /**
     * Get edit parameters
     *
     * @return array
     */
    public function getEditParameters()
    {
        return $this->_editParameters;
    }

    /**
     * Set edit parameters
     *
     * @param array $editParameters edit parameters
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setEditParameters($editParameters)
    {
        $this->_editParameters = $editParameters;

        return $this;
    }

    /**
     * Get add parameters
     *
     * @return array
     */
    public function getAddParameters()
    {
        return $this->_addParameters;
    }

    /**
     * Set add parameters
     *
     * @param array $addParameters add parameters
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setAddParameters($addParameters)
    {
        $this->_addParameters = $addParameters;

        return $this;
    }

    /**
     * Get del parameters
     *
     * @return array
     */

    public function getDeleteParameters()
    {
        return $this->_deleteParameters;
    }

    /**
     * Set delete parameters
     *
     * @param array $delParameters delete parameters
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setDeleteParameters($delParameters)
    {
        $this->_deleteParameters = $delParameters;

        return $this;
    }

    /**
     * Get search parameters
     *
     * @return array
     */
    public function getSearchParameters()
    {
        return $this->_searchParameters;
    }

    /**
     * Set search parameters
     *
     * @param array $searchParameters search parameters
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setSearchParameters($searchParameters)
    {
        $this->_searchParameters = $searchParameters;

        return $this;
    }

    /**
     * Get view parameters
     *
     * @return array
     */
    public function getViewParameters()
    {
        return $this->_viewParameters;
    }

    /**
     * Set view parameters
     *
     * @param array $viewParameters view parameters
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setViewParameters($viewParameters)
    {
        $this->_viewParameters = $viewParameters;

        return $this;
    }

    /**
     * Get grid instance
     *
     * @return \SynergyDataGrid\Grid\GridType\BaseGrid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Set grid instance
     *
     * @param \SynergyDataGrid\Grid\GridType\BaseGrid
     *
     * @return \SynergyDataGrid\Grid\Navigation\NavGrid
     */
    public function setGrid($grid)
    {
        $this->_grid = $grid;

        return $this;
    }
}