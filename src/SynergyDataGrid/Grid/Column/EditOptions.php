<?php
namespace SynergyDataGrid\Grid\Column;

/**
 * EditOptions class to handle work with Column edit options
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editoptions
 * @package mvcgrid
 */
class EditOptions extends \SynergyDataGrid\Grid\Property
{
    /**
     * Set up base EditOptions options
     * 
     * @param \SynergyDataGrid\Grid\Column $column column object
     * @param array $options array of EditOptions options
     * @return void
     */
    public function __construct($column, $options = array()) 
    {
        $this->setProperty('editoptions');
        parent::__construct($column, $options);
    }
}