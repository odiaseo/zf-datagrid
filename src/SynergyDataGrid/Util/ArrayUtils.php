<?php
    namespace SynergyDataGrid\Util;

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
    /**
     * ArrayUtils class to add some custom functionality to arrays
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     * @package mvcgrid
     */
    class ArrayUtils
    {

        /**
         * Merge two arrays recursive. First array values overwritted by second array values in case of the same keys.
         *
         * @param array $array1 first array to merge
         * @param array $array2 second array to merge
         *
         * @return array
         */
        public function arrayMergeRecursiveCustom($array1, $array2)
        {
            foreach ($array2 AS $key => $value) {
                if (array_key_exists($key, $array1) && !empty($array1[$key])) {
                    if (!is_array($array2[$key])) {
                        $array1[$key] = $array2[$key];
                    } else {
                        $array1[$key] = $this->arrayMergeRecursiveCustom($array1[$key], $array2[$key]);
                    }
                } else {
                    $array1[$key] = $value;
                }
            }
            unset($key, $value);

            return $array1;
        }
    }