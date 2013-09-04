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
     * EditRules class to handle work with Column editrules property of jqGrid
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editrules
     * @package mvcgrid
     */
    class EditRules extends Property
    {
        /**
         * Set up base EditRules options
         *
         * @param \SynergyDataGrid\Grid\Column $column  column object
         * @param array                        $options array of EditRules options
         *
         * @return void
         */
        public function __construct($column, $options = array())
        {
            $this->setProperty('editrules');
            parent::__construct($column, $options);
        }
    }