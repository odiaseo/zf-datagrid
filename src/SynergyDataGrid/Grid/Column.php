<?php
namespace SynergyDataGrid\Grid;

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
use Doctrine\ORM\PersistentCollection;
use SynergyDataGrid\Grid\Column\EditOptions;
use SynergyDataGrid\Grid\Column\EditRules;
use SynergyDataGrid\Grid\Column\FormatOptions;
use Laminas\Filter\HtmlEntities;

/**
 * Column class for single grid column implementation
 *
 *
 * @method setEditable()
 * @method setOnSelectRow()
 * @method getFormatter()
 * @method setSorttype()
 * @method getIndex()
 * @method setIndex()
 * @method setResizable()
 *
 * @method getName()
 * @method getEdittype()
 * @method getOptions()
 *
 * @method mergeFormatoptions()
 * @method setDelbutton()
 * @method setEditbutton()
 * @method setWidth()
 * @method setEdittypeIfNotSet()
 * @method setFormatterIfNotSet()
 * @method setSizeIfNotSet()
 * @method setMaxlengthIfNotSet()
 * @author  Pele Odiase
 * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
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
     * @var \SynergyDataGrid\Grid\GridType\BaseGrid
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
     * @var \Laminas\Filter\FilterInterface
     */
    protected $_htmlFilter;

    /** @var  string */
    protected $doctrineDataType;
    /**
     * Default rows count for "textarea" editype
     *
     * @var string
     */
    const DEFAULT_TEXTAREA_ROWS = 2;
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
    const DEFAULT_TEXT_SIZE = 150;
    /**
     * Default field maximum edit length for "text" editype
     *
     * @var string
     */
    const DEFAULT_TEXT_MAXLENGTH = 150;
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
    const DEFAULT_DATE_NEWFORMAT = 'Y-m-d';
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
    const DEFAULT_DATETIME_NEWFORMAT = 'Y-m-d H:i:s';

    /**
     *  Set up base options
     *
     * @param array $options
     * @param       $grid
     */
    public function __construct($options = array(), $grid)
    {
        $this->setGrid($grid);
        $this->_setDefaultOptions();
        $this->setOptions($options);
        $this->_setDependantOptions($options);
    }

    /**
     * Set up default options before applying user defined options
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    protected function _setDefaultOptions()
    {
        //$this->setWidth(200);

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

        if (array_key_exists('editoptions', $options) && is_array($options['editoptions'])
            && count(
                $options['editoptions']
            )
        ) {
            $this->getEditoptions()->mergeOptions($options['editoptions']);
        }

        if (array_key_exists('formatoptions', $options) && is_array($options['formatoptions'])
            && count(
                $options['formatoptions']
            )
        ) {
            $this->getFormatoptions()->mergeOptions($options['formatoptions']);
        }

        if (array_key_exists('editrules', $options) && is_array($options['editrules']) && count($options['editrules'])
        ) {
            $this->getEditrules()->mergeOptions($options['editrules']);
        }

        return $this;
    }

    /**
     * Get cell value for current column for specified row
     *
     * @param $row
     *
     * @return string
     */
    public function cellValue($row)
    {
        $name   = $this->getName();
        $method = 'get' . ucfirst($name);
        $type   = $this->getDoctrineDataType();

        try {
            $cellValue = $row->$method();
        } catch (\Exception $exception) {
            $cellValue = '';
        }

        if ($cellValue instanceof \DateTime) {
            //do nothing
        } elseif ($cellValue instanceof PersistentCollection) {
            $idList = array();
            /** @var $item \SynergyCommon\Entity\BaseEntity */
            foreach ($cellValue as $item) {
                $idList[] = $item->getId();
            }

            if (count($idList)) {
                $cellValue = implode(',', $idList);
            } else {
                $cellValue = '';
            }
        } elseif (is_object($cellValue) and method_exists($cellValue, 'getId')) {
            $cellValue = $cellValue->getId();
        }

        if ($this->getEdittype() == 'select') {
            $value = $this->getEditoptions()->getValue();
            $retv  = is_string($cellValue) ? $this->getHtmlFilter()->filter($cellValue) : $cellValue;
            if ($value and $retv) {
                if (is_array($value)) {
                    if (!is_array($retv)) {
                        $retv = isset($value[$retv]) ? $value[$retv] : $retv;
                    }
                } else {
                    $allPairs = explode(';', $value);
                    if (is_array($allPairs) && count($allPairs)) {
                        foreach ($allPairs as $singlePair) {
                            $pair = explode(':', $singlePair);
                            if (is_array($pair) && count($pair) == 2) {
                                if ($pair[0] == $cellValue) {
                                    $retv = $this->getHtmlFilter()->filter($pair[1]);
                                    break;
                                }
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
        } elseif (is_array($cellValue)) {
            if ($type == 'json_array') {
                $retv = json_encode($cellValue);
            } else {
                $retv = serialize($cellValue);
            }
        } elseif (is_string($cellValue)) {
            $retv = $this->getHtmlFilter()->filter($cellValue);
        } else {
            $retv = $cellValue;
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
            /** @var $model \SynergyDataGrid\Model\BaseModel */
            $model        = $this->getGrid()->getModel();
            $metadata     = $model->getClassMetadata();
            $name         = $this->getName();
            $dbColumnType = null;
            if (isset($metadata->fieldMappings[$name])) {
                $dbColumnType = $metadata->fieldMappings[$name]['type'];
            } elseif (isset($metadata->associationMappings[$name])) {
                $dbColumnType = $metadata->associationMappings[$name]['type'];
            }
            $dbColumnType = $dbColumnType ?: 'string';

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
        $editOptions   = $this->getEditoptions();
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
     * @param $editOptions EditOptions object for current column
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
     * @param $formatOptions FormatOptions object for current column
     *
     * @return \SynergyDataGrid\Grid\Column
     */
    public function setFormatoptions($formatOptions)
    {
        $this->_formatoptions = (gettype($formatOptions) == 'object') ? $formatOptions
            : new FormatOptions($this, $formatOptions);
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
     * @param $editRules
     *
     * @return $this
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
     * @return \SynergyDataGrid\Grid\GridType\BaseGrid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Set JqGrid instance for current column
     *
     * @param \SynergyDataGrid\Grid\GridType\BaseGrid $grid
     *
     * @return $this
     */
    public function setGrid($grid = null)
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
     * @param bool $selectable
     *
     * @return $this
     */
    public function setSelectable($selectable = false)
    {
        $this->_selectable = $selectable;

        return $this;
    }

    public function getHtmlFilter()
    {
        if (!$this->_htmlFilter) {
            $this->_htmlFilter = new HtmlEntities();
        }

        return $this->_htmlFilter;
    }

    /**
     * @return string
     */
    public function getDoctrineDataType()
    {
        return $this->doctrineDataType;
    }

    /**
     * @param string $doctrineDataType
     */
    public function setDoctrineDataType($doctrineDataType)
    {
        $this->doctrineDataType = $doctrineDataType;
    }
}
