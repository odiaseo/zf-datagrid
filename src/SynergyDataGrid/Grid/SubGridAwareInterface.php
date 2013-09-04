<?php
    namespace SynergyDataGrid\Grid;

    /**
     * This file is part of the Synergy package.
     *
     * (c) Pele Odiase <info@rhemastudio.com>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     *
     * @author  Pele Odiase
     * @license http://opensource.org/licenses/BSD-3-Clause
     *
     */
    use Zend\Http\PhpEnvironment\Request;

    interface SubGridAwareInterface
    {
        /**
         * @return \SynergyDataGrid\Grid\GridType\SubGrid
         */
        public function getSubGrid();

        /**
         * Returns list of subgrids as grids
         *
         * @return array
         */
        public function getSubGridsAsGrid();

        /**
         * Get data for the subgrid
         *
         * @param Request $request
         * @param         $id
         * @param         $field
         *
         * @return array
         */
        public function createSubGridData(Request $request, $id, $field);

        /**
         * Get sbgrid
         *
         * @param $subGridMap
         *
         * @return mixed
         */
        public function createSubGridAsGrid($subGridMap);

        /**
         * Add subGrid configuration to the grid
         *
         * @param $model
         */
        public function getSubGridModel($subGridMap);

    }