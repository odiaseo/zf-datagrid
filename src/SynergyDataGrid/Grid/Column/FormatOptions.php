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
     * FormatOptions class to handle work with Column formatoptions property of jqGrid
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     * @package mvcgrid
     */
    class FormatOptions extends Property
    {
        /**
         * Set up base FormatOptions options
         *
         * @param \SynergyDataGrid\Grid\Column $column  column object
         * @param array                        $options array of FormatOptions options
         *
         * @return void
         */
        public function __construct($column, $options = array())
        {
            $this->setProperty('formatoptions');
            parent::__construct($column, $options);
        }
    }