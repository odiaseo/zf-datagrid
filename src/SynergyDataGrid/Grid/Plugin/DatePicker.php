<?php
namespace SynergyDataGrid\Grid\Plugin;

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
use Laminas\Json\Encoder;

/**
 * DatePicker class for DatePicker plugin integration in jQgrid
 *
 * @method setDateFormat()
 * @method setTimeFormat()
 *
 * @author  Pele Odiase
 * @see     http://docs.jquery.com/UI/Datepicker
 * @package mvcgrid
 */
class DatePicker extends Property
{
    const DATE_PICKER_FUNCTION = 'initDatePicker';
    /**
     * Javascript function name to attach DatePicker to fields
     *
     * @var string
     */
    protected $_functionName;

    /**
     * JqGrid instance
     *
     * @var string
     */
    protected $_grid;

    /**
     * Default date format for DatePicker control
     *
     * @see http://docs.jquery.com/UI/Datepicker/formatDate
     * @const string
     */
    const DATE_DEFAULTFORMAT = 'yy-mm-dd';

    /**
     * Default time format for DatetimePicker control
     *
     * @see http://trentrichardson.com/examples/timepicker/
     * @const string
     */
    const TIME_DEFAULTFORMAT = 'hh:mm';

    /**
     * Set up base DatePicker options
     *
     * @param      \SynergyDataGrid\Grid\GridType\BaseGrid $grid
     * @param null $options
     */
    public function __construct($grid, $options = null)
    {
        $this->setGrid($grid);
        //$this->setFunctionName('dp_' . $this->getGrid()->getId());
        $this->setFunctionName('synergyDataGrid.' . self::DATE_PICKER_FUNCTION . '["' . $grid->getId() . '"]');
        $this->setDateFormat(self::DATE_DEFAULTFORMAT);
        $this->setTimeFormat(self::TIME_DEFAULTFORMAT);
        $this->setOptions($options);
    }

    /**
     * Get DatePicker javascript function name
     *
     * @return string
     */
    public function getFunctionName()
    {
        return $this->_functionName;
    }

    /**
     * Set DatePicker javascript function name
     *
     * @param string $functionName DatePicker javaqscript function name
     *
     * @return \SynergyDataGrid\Grid\Plugin\DatePicker
     */
    public function setFunctionName($functionName)
    {
        $this->_functionName = $functionName;

        return $this;
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
     * @return \SynergyDataGrid\Grid\Plugin\DatePicker
     */
    public function setGrid($grid)
    {
        $this->_grid = $grid;

        return $this;
    }

    /**
     * Get jQuery selectors for all dates and dateimes in grid
     *
     * @return array
     */
    protected function _getAllDatesInGrid()
    {
        $grid    = $this->getGrid();
        $columns = $grid->getColumns();

        $allDatesInGrid     = "";
        $allDatetimesInGrid = "";
        $allDatesInForm     = "'";
        $allDatetimesInForm = "'";

        /** @var $dateColumn \SynergyDataGrid\Grid\Column */
        $dateColumn = null;

        /** @var $dateTimeColumn \SynergyDataGrid\Grid\Column */
        $dateTimeColumn = null;

        /** @var $column \SynergyDataGrid\Grid\Column */
        foreach ($columns as $column) {
            if ($column->getDbColumnType() == 'date') {
                $dateColumn = $column;
                $allDatesInGrid .= "'#'+id+'_" . $column->getName() . ",'+";
                $allDatesInForm .= "#" . $column->getName() . ", ";
            } else {
                if ($column->getDbColumnType() == 'datetime') {
                    $allDatetimesInGrid .= "'#'+id+'_" . $column->getName() . ",'+";
                    $allDatetimesInForm .= "#" . $column->getName() . ", ";
                    $dateTimeColumn = $column;
                }
            }
        }
        if ($allDatesInGrid) {
            if ($dateFormat = $dateColumn->getFormatOptions()->getNewformat()) {
                $this->setDateFormat($dateFormat);
            }

            $allDatesInGrid = substr($allDatesInGrid, 0, strlen($allDatesInGrid) - 3) . "'";
            $allDatesInForm = substr($allDatesInForm, 0, strlen($allDatesInForm) - 2) . "'";
        } else {
            $allDatesInGrid = "";
            $allDatesInForm = "";
        }
        if ($allDatetimesInGrid) {
            if ($dateFormat = $dateTimeColumn->getFormatOptions()->getNewformat()) {
                //  $this->setDateFormat($dateFormat);
            }
            $this->setTimeFormat(self::TIME_DEFAULTFORMAT);
            $allDatetimesInGrid = substr($allDatetimesInGrid, 0, strlen($allDatetimesInGrid) - 3) . "'";
            $allDatetimesInForm = substr($allDatetimesInForm, 0, strlen($allDatetimesInForm) - 2) . "'";
        } else {
            $allDatetimesInGrid = "";
            $allDatetimesInForm = "";
        }

        return array($allDatesInGrid, $allDatetimesInGrid, $allDatesInForm, $allDatetimesInForm);
    }

    /**
     * Prepare javascript code for binding DatePicker and DatetimePicker to grid fields
     *
     * @return array
     */
    public function prepareDatepicker()
    {
        $datePicker = array();
        list($allDatesInGrid, $allDatetimesInGrid, $allDatesInForm, $allDatetimesInForm) = $this->_getAllDatesInGrid();

        if ($allDatesInGrid or $allDatetimesInGrid) {
            $datePickerFunctionName = $this->getFunctionName();
            $id                     = $this->getGrid()->getId();
            $datePicker[]
                                    = "; {$datePickerFunctionName} = function(id) {  ";
            if ($allDatesInGrid) {
                $datePicker[]
                    = sprintf(
                    '
                                        if (typeof id != "object") {
                                            jQuery(%s, "#%s").datepicker(%s);
                                            if (jQuery(%s).is(":focus")) {
                                                jQuery(%s).datepicker("show");
                                            }
                                        }
                                        ',
                    $allDatesInGrid,
                    $id,
                    Encoder::encode($this->getOptions(), false, array('enableJsonExprFinder' => true)),
                    $allDatesInGrid,
                    $allDatesInGrid
                );
                $datePicker[]
                    = sprintf(
                    '
                                        jQuery(%s).datepicker(%s);
                                        if (jQuery(%s).is(":focus")) {
                                            jQuery(%s).datepicker("show");
                                        }
                                        ',
                    $allDatesInForm,
                    Encoder::encode($this->getOptions(), false, array('enableJsonExprFinder' => true)),
                    $allDatesInForm,
                    $allDatesInForm
                );
            }
            if ($allDatetimesInGrid) {
                $datePicker[]
                    = sprintf(
                    '
                                        if (typeof id != "object") {
                                            jQuery(%s, "#%s").datetimepicker(%s);
                                            if (jQuery(%s).is(":focus")) {
                                                jQuery(%s).datetimepicker("show");
                                            }
                                        }
                                        ',
                    $allDatetimesInGrid,
                    $id,
                    Encoder::encode($this->getOptions(), false, array('enableJsonExprFinder' => true)),
                    $allDatetimesInGrid,
                    $allDatetimesInGrid
                );
                $datePicker[]
                    = sprintf(
                    '
                                        jQuery(%s).datetimepicker(%s);
                                        if (jQuery(%s).is(":focus")) {
                                            jQuery(%s).datetimepicker("show");
                                        }
                                        ',
                    $allDatetimesInForm,
                    Encoder::encode($this->getOptions(), false, array('enableJsonExprFinder' => true)),
                    $allDatetimesInForm,
                    $allDatetimesInForm
                );
            }
            $datePicker[]
                = "
                        }
                    ";
        }

        return $datePicker;
    }
}