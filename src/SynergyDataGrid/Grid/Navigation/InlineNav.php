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
     * InlineNav class for work with Inline Navigation in JqGrid
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:inline_editing#inlineNav
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
         * @param \SynergyDataGrid\Grid\GridType\BaseGrid $grid    JqGrid instance
         * @param array                                   $options array of options
         *
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
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function getGrid()
        {
            return $this->_grid;
        }

        /**
         * Set JqGrid instance
         *
         * @param \SynergyDataGrid\Grid\GridType\BaseGrid $grid JqGrid instance
         *
         * @return \SynergyDataGrid\Navigation\InlineNav
         */
        public function setGrid($grid)
        {
            $this->_grid = $grid;

            return $this;
        }
    }