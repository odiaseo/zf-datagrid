<?php
namespace SynergyDataGrid\Grid\Column;

/**
 * FormatOptions class to handle work with Column formatoptions property of jqGrid
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
 * @package mvcgrid
 */
class FormatOptions extends \SynergyDataGrid\Grid\Property
{
    /**
     * Set up base FormatOptions options
     * 
     * @param \SynergyDataGrid\Grid\Column $column column object
     * @param array $options array of FormatOptions options
     * @return void
     */
    public function __construct($column, $options = array()) 
    {
        $this->setProperty('formatoptions');
        parent::__construct($column, $options);
    }
}