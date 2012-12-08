<?php
namespace SynergyDataGrid\Grid;

/**
 * InlineNav class for work with Inline Navigation in JqGrid
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:inline_editing#inlineNav
 * @package mvcgrid
 */
class InlineNav extends Property
{
    /**
     * JqGrid instance
     * 
     * @var string
     */
    protected $_grid;
    
    /**
     * Set up base DatePicker options
     * 
     * @param \SynergyDataGrid\Grid\JqGrid $grid JqGrid instance
     * @param array $options array of options
     * @return void
     */
    public function __construct($grid, $options = array()) 
    {
        $this->setOptions($options);
        $this->setGrid($grid);
    }
    
    /**
     * Get JqGrid instance
     * 
     * @return \SynergyDataGrid\Grid\JqGrid
     */
    public function getGrid()
    {
        return $this->_grid;
    }
    
    /**
     * Set JqGrid instance
     * @param \SynergyDataGrid\Grid\JqGrid $grid JqGrid instance
     * 
     * @return \SynergyDataGrid\InlineNav
     */
    public function setGrid($grid)
    {
        $this->_grid = $grid;
        return $this;
    }
}