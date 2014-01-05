<?php
namespace SynergyDataGrid\Grid\Column;

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
 * EditOptions class to handle work with Column edit options
 *
 * @method etSizeIfNotSet()
 * @method setMaxlengthIfNotSet()
 * @method setValueIfNotSet()
 * @method setSizeIfNotSet()
 * @method setRowsIfNotSet()
 * @method setColsIfNotSet()
 *
 * @method getValue()
 * @author  Pele Odiase
 * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editoptions
 * @package mvcgrid
 */
class EditOptions extends Property
{
    /**
     * Set up base EditOptions option
     *
     * @param       $column
     * @param       $options
     */
    public function __construct($column, $options = null)
    {
        $this->setProperty('editoptions');
        parent::__construct($column, $options);
    }
}