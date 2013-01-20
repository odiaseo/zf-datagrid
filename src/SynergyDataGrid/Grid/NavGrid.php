<?php
namespace SynergyDataGrid\Grid;

use SynergyDataGrid\Grid\Property;

/**
 * NavGrid class for work with Navigation Control in JqGrid
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
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
    protected $_delParameters = array();
    
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
     * @var \SynergyDataGrid\Grid\JqGridFactory
     */
    protected $_grid;
    
    /**
     * Set up base NavGrid options
     * 
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid JqGrid instance
     * @param array $options array of options
     * @return void
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
     * @param array $editParameters edit parameters
     * 
     * @return \SynergyDataGrid\NavGrid
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
     * @param array $addParameters add parameters
     * 
     * @return \SynergyDataGrid\NavGrid
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
    public function getDelParameters() 
    {
        return $this->_delParameters;
    }
    
    /**
     * Set delete parameters
     * @param array $delParameters delete parameters
     * 
     * @return \SynergyDataGrid\NavGrid
     */
    public function setDelParameters($delParameters) 
    {
        $this->_delParameters = $delParameters;
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
     * @param array $searchParameters search parameters
     * 
     * @return \SynergyDataGrid\NavGrid
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
     * @param array $viewParameters view parameters
     * 
     * @return \SynergyDataGrid\NavGrid
     */
    public function setViewParameters($viewParameters) 
    {
        $this->_viewParameters = $viewParameters;
        return $this;
    }
    
    /**
     * Get grid instance
     * 
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function getGrid() 
    {
        return $this->_grid;
    }
    
    /**
     * Set grid instance
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid
     * 
     * @return \SynergyDataGrid\NavGrid
     */
    public function setGrid($grid) 
    {
        $this->_grid = $grid;
        return $this;
    }
}