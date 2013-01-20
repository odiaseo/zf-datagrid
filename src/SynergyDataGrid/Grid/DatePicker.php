<?php
namespace SynergyDataGrid\Grid;
use Zend\Json\Encoder;
/**
 * DatePicker class for DatePicker plugin integration in jQgrid
 *
 * @author Pele Odiase
 * @see http://docs.jquery.com/UI/Datepicker
 * @package mvcgrid
 */
class DatePicker extends Property
{
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
     * @see http://docs.jquery.com/UI/Datepicker/formatDate
     * @const string
     */
    const DATE_DEFAULTFORMAT = 'MM d, yy';
    
    /**
     * Default time format for DatetimePicker control
     * @see http://trentrichardson.com/examples/timepicker/
     * @const string
     */
    const TIME_DEFAULTFORMAT = 'hh:mm:ss';
    
    /**
     * Set up base DatePicker options
     * 
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid JqGrid instance
     * @param array $options array of options
     * @return void
     */
    public function __construct($grid, $options = array()) 
    {
        $this->setGrid($grid);
        //$this->setFunctionName('dp_' . $this->getGrid()->getId());
        $this->setFunctionName('dp_picker');
        $this->setDateFormat(self::DATE_DEFAULTFORMAT);
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
     * @param string $functionName DatePicker javaqscript function name
     * 
     * @return \SynergyDataGrid\DatePicker
     */
    public function setFunctionName($functionName)
    {
        $this->_functionName = $functionName;
        return $this;
    }
    
    /**
     * Get JqGrid instance
     * 
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function getGrid()
    {
        return $this->_grid;
    }
    
    /**
     * Set JqGrid instance
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid JqGrid instance
     * 
     * @return \SynergyDataGrid\DatePicker
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
        $grid = $this->getGrid();
        $columns = $grid->getColumns();
        $allDatesInGrid = "";
        $allDatetimesInGrid = "";
        $allDatesInForm = "'";
        $allDatetimesInForm = "'";
        foreach ($columns as $column) {
            if ($column->getDbColumnType() == 'date') {
                $allDatesInGrid .= "'#'+id+'_". $column->getName() . ",'+";
                $allDatesInForm .= "#". $column->getName() . ", ";
            } else if ($column->getDbColumnType() == 'datetime') {
                $allDatetimesInGrid .= "'#'+id+'_". $column->getName() . ",'+";
                $allDatetimesInForm .= "#". $column->getName() . ", ";
            }
        }
        if ($allDatesInGrid) {
            $allDatesInGrid = substr($allDatesInGrid, 0, strlen($allDatesInGrid) - 3) . "'";
            $allDatesInForm = substr($allDatesInForm, 0, strlen($allDatesInForm) - 2) . "'";
        } else {
            $allDatesInGrid = "";
            $allDatesInForm = "";
        }
        if ($allDatetimesInGrid) {
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
        $datePickerFunctionName = $this->getFunctionName();
        $id = $this->getGrid()->getId();
        $datePicker[] = 
        "
            function $datePickerFunctionName(id) { 
        ";
            
        if ($allDatesInGrid) {
            $datePicker[] = 
                sprintf('
                    if (typeof id != "object") {
                        jQuery(%s, "#%s").datepicker(%s)
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
            $datePicker[] = 
                sprintf('jQuery(%s).datepicker(%s)
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
            $datePicker[] = 
                sprintf('
                    if (typeof id != "object") {
                        jQuery(%s, "#%s").datetimepicker(%s)
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
            $datePicker[] = 
                sprintf('jQuery(%s).datetimepicker(%s)
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
        $datePicker[] = 
        "
            }
        ";
        return $datePicker;
    }
    
}