<?php
    namespace SynergyDataGrid\Grid\Adapter;

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
    interface GridAdapterInterface
    {
        /**
         * @param bool $countOnly
         * @param null $offset
         * @param null $itemCountPerPage
         *
         * @return \Doctrine\ORM\QueryBuilder
         */
        public function createQuery($offset = null, $itemCountPerPage = null);
    }