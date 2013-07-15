<?php
namespace SynergyDataGrid\Grid\Column;

use SynergyDataGrid\Grid\Property;

/**
 * EditRules class to handle work with Column editrules property of jqGrid
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editrules
 * @package mvcgrid
 */
class EditRules extends Property
{
    /**
     * Set up base EditRules options
     * 
     * @param \SynergyDataGrid\Grid\Column $column column object
     * @param array $options array of EditRules options
     * @return void
     */
    public function __construct($column, $options = array())
    {
        $this->setProperty('editrules');
        parent::__construct($column, $options);
    }
}