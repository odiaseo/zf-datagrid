<?php
namespace SynergyDataGrid\Grid;

use SynergyDataGrid\Grid\Column\EditOptions;
use Doctrine\ORM\PersistentCollection;
use SynergyDataGrid\Grid\Column\FormatOptions;
use SynergyDataGrid\Grid\Column\EditRules;

/**
 * Column class for single grid column implementation
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
 * @package mvcgrid
 */
class Column extends Base
{
    /**
     * Column title
     *
     * @var string
     */
    protected $_title;
    /**
     * Parent jqGrid object
     *
     * @var \SynergyDataGrid\Grid\JqGridFactory
     */
    protected $_grid;
    /**
     * Database field type for corresponding column
     *
     * @var string
     */
    protected $_dbColumnType;
    /**
     * Whether we should include this field in query or not
     *
     * @var boolean
     */
    protected $_selectable = true;
    /**
     * EditOptions object of jqGrid Column
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editoptions
     * @var \SynergyDataGrid\Grid\Column\EditOptions
     */
    protected $_editoptions;
    /**
     * FormatOptions object of jqGrid Column
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     * @var \SynergyDataGrid\Grid\Column\EditOptions
     */
    protected $_formatoptions;
    /**
     * EditRules object of jqGrid Column
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editrules
     * @var \SynergyDataGrid\Grid\Column\EditOptions
     */
    protected $_editrules;
    /**
     * Default rows count for "textarea" editype
     *
     * @var string
     */
    const DEFAULT_TEXTAREA_ROWS = 3;
    /**
     * Default columns count for "textarea" editype
     *
     * @var string
     */
    const DEFAULT_TEXTAREA_COLS = 20;
    /**
     * Default values for "checkbox" editype (1 - checked, 0 - unchecked)
     *
     * @var string
     */
    const DEFAULT_CHECKBOX_VALUE = '1:0';
    /**
     * Default field size for "text" editype
     *
     * @var string
     */
    const DEFAULT_TEXT_SIZE = 30;
    /**
     * Default field maximum edit length for "text" editype
     *
     * @var string
     */
    const DEFAULT_TEXT_MAXLENGTH = 30;
    /**
     * Default source format for date formatter (for use with integrated datepicker)
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     *
     * @var string
     */
    const DEFAULT_DATE_SRCFORMAT = 'Y-m-d';
    /**
     * Default output format for date formatter (for use with integrated datepicker)
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     *
     * @var string
     */
    const DEFAULT_DATE_NEWFORMAT = 'F d, Y';
    /**
     * Default source format for date formatter when using time (for use with integrated datetimepicker)
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     *
     * @var string
     */
    const DEFAULT_DATETIME_SRCFORMAT = 'Y-m-d H:i:s';
    /**
     * Default output format for date formatter when using time (for use with integrated datetimepicker)
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter
     *
     * @var string
     */
    const DEFAULT_DATETIME_NEWFORMAT = 'F d, Y H:i:s';

    /**
     * Set up base options
     *
     * @param array $options array of options
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid instanse of JqGrid object
     *
     * @return void
     */
    public function __construct($options = array(), $grid)
    {
        $this->setGrid($grid)
            ->_setDefaultOptions()
            ->setOptions($options)
            ->_setDependantOptions($options);
    }

    /**
     * Set up default options before applying user defined options
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    protected function _setDefaultOptions()
    {
        $this->setWidth(200);

        // "editable" option is FALSE by default in jqGrid, but 
        // we will change it to TRUE by default, 
        // because we need to edit most of columns
        $this->setEditable(true);

        $this->setEditoptions(new EditOptions($this));
        $this->setFormatoptions(new FormatOptions($this));
        $this->setEditrules(new EditRules($this));
        $this->setOnSelectRow("");
        return $this;
    }

    /**
     * Set up dependant options based on user options
     *
     * @param array $options array of options
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    protected function _setDependantOptions($options = array())
    {
        if ($this->getFormatter() == 'actions' || $this->getFormatter() == 'mactions') {
            $this->setSelectable(false)
                ->setEditable(false);
        }

        $this->_fetchDbColumnType()
            ->_setUpEditing();

        if ($this->getDbColumnType() == 'date' || $this->getDbColumnType() == 'datetime') {
            $this->setSorttype('date');
        }

        if (!$this->getIndex()) {
            $this->setIndex($this->getName());
        }

        if (array_key_exists('editoptions', $options) && is_array($options['editoptions']) && count($options['editoptions'])) {
            $this->getEditoptions()->mergeOptions($options['editoptions']);
        }

        if (array_key_exists('formatoptions', $options) && is_array($options['formatoptions']) && count($options['formatoptions'])) {
            $this->getFormatoptions()->mergeOptions($options['formatoptions']);
        }

        if (array_key_exists('editrules', $options) && is_array($options['editrules']) && count($options['editrules'])) {
            $this->getEditrules()->mergeOptions($options['editrules']);
        }
        return $this;
    }

    /**
     * Get cell value for current column for specified row
     *
     * @param array $row array with grid row
     *
     * @return string
     */
    public function cellValue($row)
    {
        $name = $this->getName();

        if (property_exists($row, $name)) {
            if (is_object($row->{$name})) {
                $cellValue = $row->{$name}->id;
            } else {
                $cellValue = $row->{$name};
            }
        } else {
            $cellValue = '';
        }
        //$cellValue = array_key_exists($this->getName(), $row) ? $row->{$name} : '';
        if ($this->getEdittype() == 'select') {
            $value = $this->getEditoptions()->getValue();
            $retv = htmlentities($cellValue);
            if ($value) {
                $allPairs = explode(';', $value);
                if (is_array($allPairs) && count($allPairs)) {
                    foreach ($allPairs as $singlePair) {
                        $pair = explode(':', $singlePair);
                        if (is_array($pair) && count($pair) == 2) {
                            if ($pair[0] == $cellValue) {
                                $retv = htmlentities($pair[1]);
                                break;
                            }
                        }
                    }
                }
            }
        } elseif ($cellValue instanceof \DateTime) {
            /**
             * @var \DateTime $cellValue
             */
            $retv = $cellValue->format(self::DEFAULT_DATETIME_SRCFORMAT);
        } else {
            $retv = htmlentities($cellValue);
        }
        return $retv;
    }

    /**
     * Fetch database field type and save it in column propery
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    private function _fetchDbColumnType()
    {
        if ($this->getSelectable()) {
            $metadata = $this->getGrid()->getService()->getClassMetadata();
            $name = $this->getName();
            $dbColumnType = null;
            if (isset($metadata->fieldMappings[$name])) {
                $dbColumnType = $metadata->fieldMappings[$name]['type'];
            } elseif (isset($metadata->associationMappings[$name])) {
                $dbColumnType = $metadata->associationMappings[$name]['type'];
            }
            $dbColumnType = $dbColumnType ? : 'string';

            $this->setDbColumnType($dbColumnType);
        }
        return $this;
    }

    /**
     * Set up edit / format options based on database column type
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    private function _setUpEditing()
    {
        $editOptions = $this->getEditoptions();
        $formatOptions = $this->getFormatoptions();
        switch ($this->getDbColumnType()) {
            case 'date':
                $this->setEdittypeIfNotSet('text');
                $this->setFormatterIfNotSet('date');
                $formatOptions->setSrcformatIfNotSet(self::DEFAULT_DATE_SRCFORMAT);
                $formatOptions->setNewformatIfNotSet(self::DEFAULT_DATE_NEWFORMAT);
                break;

            case 'datetime':
                $this->setEdittypeIfNotSet('text');
                $this->setFormatterIfNotSet('date');
                $formatOptions->setSrcformatIfNotSet(self::DEFAULT_DATETIME_SRCFORMAT);
                $formatOptions->setNewformatIfNotSet(self::DEFAULT_DATETIME_NEWFORMAT);
                break;

            case 'boolean':
                $this->setEdittypeIfNotSet('checkbox');
                $editOptions->setValueIfNotSet(self::DEFAULT_CHECKBOX_VALUE);
                break;

            case 'string':
                if ($this->getEdittype() !== 'select') {
                    $this->setEdittypeIfNotSet('text');
                    $editOptions->setSizeIfNotSet(self::DEFAULT_TEXT_SIZE);
                    $editOptions->setMaxlengthIfNotSet(self::DEFAULT_TEXT_MAXLENGTH);
                }
                break;

            case 'integer':
                $this->setEdittypeIfNotSet('text');
                $this->setFormatterIfNotSet('integer');
                break;

            case 'decimal':
                $this->setEdittypeIfNotSet('text');
                $this->setFormatterIfNotSet('number');
                break;

            case 'text':
                $this->setEdittypeIfNotSet('textarea');
                $editOptions->setRowsIfNotSet(self::DEFAULT_TEXTAREA_ROWS);
                $editOptions->setColsIfNotSet(self::DEFAULT_TEXTAREA_COLS);
                break;

            default:
                break;
        }
        $this->getEditoptions()->mergeOptions($editOptions->getOptions());
        $this->getFormatoptions()->mergeOptions($formatOptions->getOptions());
        return $this;
    }

    /**
     * Add current column to grid and update colModel and colNames properies
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    private function _updateGrid()
    {
        $this->getGrid()->addColumn($this);
        $this->getGrid()->setColNames($this->getGrid()->getColumns());
        $this->getGrid()->setColModel($this->getGrid()->getColumns());
        return $this;
    }

    /**
     * Get column title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set column title
     *
     * @param string $title new column title
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setTitle($title = '')
    {
        $this->_title = $title;
        $this->_updateGrid();
        return $this;
    }

    /**
     * Get database column type
     *
     * @return string
     */
    public function getDbColumnType()
    {
        return $this->_dbColumnType;
    }

    /**
     * Set database column type
     *
     * @param string $dbColumnType new database column type
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setDbColumnType($dbColumnType = '')
    {
        $this->_dbColumnType = $dbColumnType;
        $this->_updateGrid();
        return $this;
    }

    /**
     * Get EditOptions object for current column
     *
     * @return \SynergyDataGrid\Grid\Column\EditOptions
     */
    public function getEditoptions()
    {
        return $this->_editoptions;
    }

    /**
     * Set EditOptions object for current column
     *
     * @param \SynergyDataGrid\Grid\Column\EditOptions $editOptions EditOptions object for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setEditoptions($editOptions)
    {
        $this->_editoptions = (gettype($editOptions) == 'object') ? $editOptions : new EditOptions($this, $editOptions);
        $this->setOption('editoptions', $this->_editoptions->getOptions());
        $this->_updateGrid();
        return $this;
    }

    /**
     * Get FormatOptions object for current column
     *
     * @return \SynergyDataGrid\Grid\Column\FormatOptions
     */
    public function getFormatoptions()
    {
        return $this->_formatoptions;
    }

    /**
     * Set FormatOptions object for current column
     *
     * @param \SynergyDataGrid\Grid\Column\FormatOptions $formatOptions FormatOptions object for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setFormatoptions($formatOptions)
    {
        $this->_formatoptions = (gettype($formatOptions) == 'object') ? $formatOptions : new FormatOptions($this, $formatOptions);
        $this->setOption('formatoptions', $this->_formatoptions->getOptions());
        $this->_updateGrid();
        return $this;
    }

    /**
     * Get EditRules object for current column
     *
     * @return \SynergyDataGrid\Grid\Column\EditRules
     */
    public function getEditrules()
    {
        return $this->_editrules;
    }

    /**
     * Set EditRules object for current column
     *
     * @param \SynergyDataGrid\Grid\Column\EditRules $formatOption EditRules object for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setEditrules($editRules)
    {
        $this->_editrules = (gettype($editRules) == 'object') ? $editRules : new EditRules($this, $editRules);
        $this->setOption('editrules', $this->_editrules->getOptions());
        $this->_updateGrid();
        return $this;
    }

    /**
     * Get Grid instance for current column
     *
     * @return \SynergyDataGrid\Grid\Column\EditRules
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Set JqGrid instance for current column
     *
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid JqGrid instance for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setGrid($grid = '')
    {
        $this->_grid = $grid;
        return $this;
    }

    /**
     * Get selectable property for current column
     *
     * @return bool
     */
    public function getSelectable()
    {
        return $this->_selectable;
    }

    /**
     * Set Selectable property for current column
     *
     * @param bool $selectable selectable property for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setSelectable($selectable = '')
    {
        $this->_selectable = $selectable;
        return $this;
    }


}