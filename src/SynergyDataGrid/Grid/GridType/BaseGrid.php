<?php
    namespace SynergyDataGrid\Grid\GridType;

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
    use Doctrine\ORM\AbstractQuery;
    use Doctrine\ORM\Mapping\ClassMetadataInfo;
    use SynergyDataGrid\Grid\Base;
    use SynergyDataGrid\Grid\Column;
    use SynergyDataGrid\Grid\JsCode;
    use SynergyDataGrid\Grid\Navigation\InlineNav;
    use SynergyDataGrid\Grid\Navigation\NavGrid;
    use SynergyDataGrid\Grid\Plugin\DatePicker;
    use SynergyDataGrid\Grid\SubGridAwareInterface;
    use SynergyDataGrid\Util\ArrayUtils;
    use Zend\Filter\Word\CamelCaseToSeparator;
    use Zend\Http\PhpEnvironment\Request;
    use Zend\Json\Expr;
    use Zend\Json\Json;
    use Zend\Paginator\Paginator;
    use Zend\ServiceManager\ServiceLocatorInterface;
    use Zend\Stdlib\RequestInterface;

    /**
     * JqGrid class for implement base jqGrid plugin functionality
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:jqgriddocs
     * @package mvcgrid
     */
    abstract class BaseGrid extends Base implements SubGridAwareInterface
    {
        const SORT_ID             = 'sidx';
        const SORT_ORDER_ID       = 'order';
        const SEARCH_ID           = 'search';
        const ND_ID               = 'nd';
        const OPERATOR_KEY        = 'oper';
        const OPER_EDIT           = 'editoper';
        const OPER_ADD            = 'addoper';
        const OPER_DELETE         = 'deloper';
        const SUBGRID_ID          = 'subgridid';
        const TOTALROWS_ID        = 'totalrows';
        const SORT_COLUMN         = 'title';
        const TOP                 = 'top';
        const BOTTOM              = 'bottom';
        const RECORD_LIMIT        = 'rows';
        const CURRENT_PAGE        = 'page';
        const CURRENT_ROW         = 'id';
        const ROW_TOTAL           = 'totalrows';
        const ID_PREFIX           = 'grid_';
        const GRID_IDENTIFIER     = 'grid';
        const ENTITY_IDENTFIER    = '_entity';
        const SUB_GRID_IDENTIFIER = 'subgridid';
        /** form input types */
        const TYPE_SELECT     = 'select';
        const TYPE_TEXT       = 'text';
        const TYPE_CHECKBOX   = 'checkbox';
        const TYPE_TEXTAREA   = 'textarea';
        const TYPE_RADIO      = 'radio';
        const TYPE_CUSTOM     = 'custom';
        const TYPE_IMAGE      = 'image';
        const TYPE_FILE       = 'file';
        const SUB_GRID_SUFFIX = '_sub_';


        const DYNAMIC_URL_TYPE_GRID       = 1;
        const DYNAMIC_URL_TYPE_EDIT       = 2;
        const DYNAMIC_URL_TYPE_SUBGRID    = 3;
        const DYNAMIC_URL_TYPE_ROW_EXPAND = 4;
        /**
         * Cookie prefix for column sizes
         *
         * @var string
         */
        const COOKIE_COLUMNS_SIZES_PREFIX = 'jqgrid_columns_sizes_';
        /**
         * Cookie prefix for column ordering
         *
         * @var string
         */
        const COOKIE_COLUMNS_ORDERING_PREFIX = 'jqgrid_columns_ordering_';
        /**
         * Cookie prefix for column sorting
         *
         * @var string
         */
        const COOKIE_SORTING_PREFIX = 'jqgrid_sorting_';
        /**
         * Cookie prefix for paging settings
         *
         * @var string
         */
        const COOKIE_PAGING_PREFIX = 'jqgrid_paging_';
        /**
         * Mapping jqGrid filter expressions to Doctrine
         *
         * @var array
         */
        protected $_expression = array(
            'eq' => 'EQUAL',
            'ne' => 'NOT_EQUAL',
            'lt' => 'LESS_THAN',
            'le' => 'LESS_THAN_OR_EQUAL',
            'gt' => 'GREATER_THAN',
            'ge' => 'GREATER_THAN_OR_EQUAL',
            'bw' => 'BEGIN_WITH',
            'bn' => 'NOT_BEGIN_WITH',
            'in' => 'IN',
            'ni' => 'NOT_IN',
            'ew' => 'END_WITH',
            'en' => 'NOT_END_WITH',
            'cn' => 'CONTAIN',
            'nc' => 'NOT_CONTAIN'
        );
        /**
         * The default value of this property is:
         * {page:“page”,rows:“rows”, sort:“sidx”, order:“sord”, search:“_search”, nd:“nd”, id:“id”, oper:“oper”, editoper:“edit”, addoper:“add”,
         * deloper:“del”, subgridid:“id”, npage:null, totalrows:“totalrows”}
         * This customizes names of the fields sent to the server on a POST request. For example, with this setting, you can change the sort
         *  order element from sidx to mysort by setting prmNames: {sort: “mysort”}. The string that will be POST-ed to the server will then be
         * myurl.php?page=1&rows=10&mysort=myindex&sord=asc rather than myurl.php?page=1&rows=10&sidx=myindex&sord=asc
         * So the value of the column on which to sort upon can be obtained by looking at $POST['mysort'] in PHP. When some parameter is set to * null, it will be
         * not sent to the server. For example if we set prmNames: {nd:null} the nd parameter will not be sent to the server. For npage * option see the scroll
         * option.
         * These options have the following meaning and default values:
         *  page: the requested page (default value page)
         * rows: the number of rows requested (default value rows)
         * sort: the sorting column (default value sidx)
         * order: the sort order (default value sord)
         * search: the search indicator (default value _search)
         * nd: the time passed to the request (for IE browsers not to cache the request) (default value nd)
         * id: the name of the id when POST-ing data in editing modules (default value id)
         * oper: the operation parameter (default value oper)
         * editoper: the name of operation when the data is POST-ed in edit mode (default value edit)
         * addoper: the name of operation when the data is posted in add mode (default value add)
         * deloper: the name of operation when the data is posted in delete mode (default value del)
         * totalrows: the number of the total rows to be obtained from server - see rowTotal (default value totalrows)
         * subgridid: the name passed when we click to load data in the subgrid (default value id)
         *
         * @var array
         */
        protected $_prmNames = array(
            self::SORT_ID       => 'sidx',
            self::SORT_ORDER_ID => 'sord',
            self::SEARCH_ID     => '_search',
            self::ND_ID         => 'nd',
            self::OPERATOR_KEY  => 'oper',
            self::OPER_EDIT     => 'edit',
            self::OPER_ADD      => 'add',
            self::OPER_DELETE   => 'del',
            self::SUBGRID_ID    => 'subgridid',
            self::TOTALROWS_ID  => 'totalrows',
            self::RECORD_LIMIT  => 'rows',
            self::CURRENT_PAGE  => 'page',
            self::CURRENT_ROW   => 'id'
        );

        /**
         * @var \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
         */
        protected $_serviceLocator;
        /**
         * Grid configuration options
         *
         * @see module.config.php
         * @var array
         */
        protected $_options = array();

        /**
         * Rows per page count, defsult setting
         *
         * @var int
         */
        protected $_defaultItemCountPerPage = 20;
        /**
         * @var \SynergyDataGrid\Grid\Toolbar
         */
        protected $_toolbarConfig;
        /**
         * Array of grid columns
         *
         * @var array
         */
        protected $_columns = array();
        /**
         * Html code for jqGrid control
         *
         * @var array
         */
        protected $_html = array();
        /**
         * Javascript code for jqGrid control
         *
         * @var array
         */
        protected $_js = array();
        /**
         * Javascript code for jqGrid control to put in onload section
         *
         * @var array
         */
        protected $_onload = array();
        /**
         * Last selected row variable name, javascript
         *
         * @var string
         */
        protected $_lastSelectVariable;
        /**
         * Do we need actions column
         *
         * @var bool
         */
        protected $_actionsColumn = false;
        /**
         * Do we allow user to delete row
         *
         * @var bool
         */
        protected $_allowDelete = true;
        /**
         * Do we allow user to edit row
         *
         * @var bool
         */
        protected $_allowEdit = true;
        /**
         * Do we allow user to edit row, as a form
         *
         * @var bool
         */
        protected $_allowEditForm = false;
        /**
         * Do we allow user to add row
         *
         * @var bool
         */
        protected $_allowAdd = true;
        /**
         * Javascript code to be executed on processAfterSubmit jqGrid event
         *
         * @var string
         */
        protected $_processAfterSubmit;
        /**
         * Do we need NavGrid part in pager
         *
         * @var bool
         */
        protected $_navGridEnabled = true;
        /**
         * NavGrid instance
         *
         * @var \SynergyDataGrid\Grid\NavGrid
         */
        protected $_navGrid;
        /**
         * Do we need inline navigation buttons
         *
         * @var bool
         */
        protected $_inlineNavEnabled = true;
        /**
         * InlineNav instance
         *
         * @var \SynergyDataGrid\Grid\InlineNav
         */
        protected $_inlineNav;
        /**
         * DatePicker object instance
         *
         * @var \SynergyDataGrid\Grid\Plugin\DatePicker
         */
        protected $_datePicker;
        /**
         * Do we use simple or multiple search
         *
         * @var bool
         */

        protected $_multipleSearch = false;
        /**
         * Row action buttons (edit, delete and etc)
         *
         * @var array
         */
        protected $_rowActionButtons = array();
        /**
         * Array with custom navigation buttons
         *
         * @var array
         */
        protected $_navButtons = array();
        /**
         * Do we use allow user to resize columns
         *
         * @var bool
         */
        protected $_allowResizeColumns = true;
        /**
         * Do we need to reload grid after column resize (if we will not reload grid, horizontal scrollbar can appears)
         *
         * @var bool
         */
        protected $_reloadAfterResize = true;
        /**
         * Do we need to reload grid after change column order
         * it fixes jqGrid bug when sorting by column doesn't work right after column reordering
         *
         * @var bool
         */
        protected $_reloadAfterChangeColumnsOrdering = true;
        /**
         * Is current grid "detail grid"
         *
         * @var bool
         */
        protected $_isDetailGrid = false;
        /**
         * id of master grid in case of current grid is detail one
         *
         * @var string
         */
        protected $_masterGridId;
        /**
         * Do we already have a subrid
         *
         * @var bool
         *
         */
        protected $_hasSubGrid = false;
        /**
         * Preload grid with data
         */
        /**
         * JsCode class to keep all javascript code for jqGrid
         *
         * @var \SynergyDataGrid\Grid\JsCode
         */
        protected $_jsCode;
        /**
         * @var String $url
         */
        protected $_url;
        /**
         * columns not displayed on the grid
         *
         * @var array
         */
        protected $_excludedColumns = array();
        /**
         * columns displayed on the grid but not ediable
         *
         * @var array
         */
        protected $_nonEditable = array();
        /**
         * mapping of columns to jqgrid types
         *
         * @var array
         */
        protected $_columnTypeMapping = array();

        /**
         * Is this a grid with a tree. required Gedmo tree module
         *
         * @var bool
         */
        protected $isTreeGrid = false;
        /**
         * Grid configuration data
         *
         * @var
         */
        protected $_config;

        /**
         * tree grid node data
         *
         * @var array
         *
         */
        protected $_nodeData = array();

        /**
         * Service model, attached to grid
         *
         * @var int
         */
        protected $_service;

        /**
         * @param $config
         * @param $serviceLocator
         */
        public function __construct(array $config, ServiceLocatorInterface $serviceLocator)
        {
            $this->setConfig($config);
            $this->setServiceLocator($serviceLocator);
            $this->setJsCode(new JsCode($this));
        }

        public function isRequired($map, $columnData, $fieldName, $default)
        {
            if (isset($columnData['editrules']['required'])) {
                return $columnData['editrules']['required'];
            } elseif (
                $columnData['editable']
                and (!$columnData['hidden'] or $columnData['editrules']['edithidden'])
                and (isset($map['nullable']) and !$map['nullable'])
                and $default === null
                and $fieldName != 'id'
            ) {
                return true;
            }

            return false;
        }


        /**
         * Set up default options before applying user defined options
         *
         * @param string $id id of grid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setGridDisplayOptions()
        {

            $this->setPager($this->getId() . '_pager');
            if (!isset($this->_config['grid_options']['rowList'])) {
                $this->setRowList(range($this->_defaultItemCountPerPage, $this->_defaultItemCountPerPage * 5, $this->_defaultItemCountPerPage));
            }

            //merge prmNames
            $userPrmNames = $this->getPrmNames();
            if ($userPrmNames and is_array($userPrmNames)) {
                $prmNames = array_merge($this->_prmNames, $this->_config['prmNames'], $userPrmNames);
            } else {
                $prmNames = array_merge($this->_prmNames, $this->_config['prmNames']);
            }

            $this->setPrmNames($prmNames);

            //Set grid options
            $gridOptions = isset($this->_config['grid_options']) ? $this->_config['grid_options'] : array();

            foreach ($gridOptions as $key => $val) {
                $method = 'set' . ucfirst($key);
                $this->$method($val);
            }


            //Set default data to be posted back to server
            $this->mergePostData(
                array(
                    self::GRID_IDENTIFIER  => $this->getId(),
                    self::ENTITY_IDENTFIER => $this->_entity
                )
            );

            $this->setDatePicker(new DatePicker($this, $this->_config['plugins']['date_picker']));
            $datePickerFunctionName = $this->getDatePicker()->getFunctionName();

            //Set navigation options
            $navGrid = isset($this->_config['nav_grid']) ? $this->_config['nav_grid'] : array();

            if ($this->getActionsColumn()) {
                $this->getJsCode()->addActionsColumn();
                $navGrid['edit'] = false;
                $navGrid['del']  = false;
            }

            $this->setNavGrid($navGrid);

            //Add parameters
            $addParameters = isset($this->_config['add_parameters']) ? $this->_config['add_parameters'] : array();
            $this->getNavGrid()->mergeAddParameters($addParameters);


            //Edit parameters
            $editParameters = isset($this->_config['edit_parameters']) ? $this->_config['edit_parameters'] : array();
            $this->getNavGrid()->mergeEditParameters($editParameters);


            //Search Parameters
            $searchParameters = isset($this->_config['search_parameters']) ? $this->_config['search_parameters'] : array();
            $this->getNavGrid()->mergeSearchParameters($searchParameters);


            //Delete Parameters
            $deleteParameters = isset($this->_config['delete_parameters']) ? $this->_config['delete_parameters'] : array();
            $this->getNavGrid()->mergeDeleteParameters($deleteParameters);

            //View Parameters
            $viewParameters = isset($this->_config['view_parameters']) ? $this->_config['view_parameters'] : array();
            $this->getNavGrid()->mergeViewParameters($viewParameters);


            //set add/edit form options
            $formOptions = isset($this->_config['form_options']) ? $this->_config['form_options'] : array();
            if ($formOptions) {
                $this->getNavGrid()->mergeAddParameters($formOptions);
                $this->getNavGrid()->mergeEditParameters($formOptions);
            }

            $inlineNavOptions = isset($this->_config['inline_nav']) ? $this->_config['inline_nav'] : array();

            $funcName                                       = $this->getDatePicker()->getFunctionName();
            $inlineNavOptions['addRowParams']['oneditfunc'] = new \Zend\Json\Expr(" function() { {$funcName}(new_row);  }");

            if ($inlineNavOptions) {
                $this->setInlineNav($inlineNavOptions);
            }

            //add user defined navigation buttons
            if (isset($this->_config['custom_nav_buttons'])) {
                $customNavOptions = $this->_config['custom_nav_buttons'];
                if (is_callable($customNavOptions)) {
                    $optionArray = $customNavOptions($this->getId());
                } else {
                    $optionArray = $customNavOptions;
                }

                foreach ($optionArray as $buttonId => $button) {
                    $button['id'] = 'nav_' . $buttonId;
                    $this->setNavButton($button);
                }
            }

            $this->setLastSelectVariable($this->getId() . '_lastSel');

            $this->setOnSortCol(new Expr($this->getJsCode()->prepareSetSortingCookie()));
            $this->setOnPaging(new Expr($this->getJsCode()->prepareSetPagingCookie()));

            $this->getJsCode()->addAutoResizeScript($this->getId());

            return $this;
        }

        /**
         * Create Column object based on given options and add it to the grid
         *
         * @param string $columnTitle title of a column
         * @param array  $column      array of column options
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function addColumn($columnTitle = '', $column = array())
        {
            if (is_array($column) && count($column) && array_key_exists('name', $column) && $columnTitle) {
                $columnName     = $column['name'];
                $existingColumn = $this->getColumn($columnName);
                // if column with such a name already exists we will merge options and overwrite title with new one
                if ($existingColumn) {
                    $utils        = new ArrayUtils();
                    $oldOptions   = $existingColumn->getOptions();
                    $newOptions   = $utils->arrayMergeRecursiveCustom($oldOptions, $column);
                    $columnObject = new Column($newOptions, $this);
                } else {
                    $columnObject = new Column($column, $this);
                }
                $columnObject->setTitle($columnTitle);
                $this->_columns[$columnName] = $columnObject;
            }

            return $this;
        }

        /**
         * Add given columns to the grid
         *
         * @param array $columns array of columns
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function addColumns($columns = array())
        {
            $nameFilter = new CamelCaseToSeparator();

            if (is_array($columns) && count($columns)) {
                foreach ($columns as $column) {
                    $title = ucwords($nameFilter->filter($column['name']));
                    $this->addColumn($title, $column);
                }
                $this->setColNames($this->_columns);
                $this->setColModel($this->_columns);
            }

            return $this;
        }


        protected function _formatGridData($rows, $columns)
        {
            $records     = array();
            $columnNames = array_keys($columns);

            foreach ($rows as $k => $row) {

                if (isset($row->id)) {
                    $records[$k]['id'] = $row->id;
                }

                $records[$k]['cell'] = array();
                /**
                 * @var $column \SynergyDataGrid\Grid\Column
                 */
                foreach ($columns as $name => $column) {

                    $index = array_search($name, $columnNames);
                    if ($index !== false) {
                        $records[$k]['cell'][$index] = $column->cellValue($row);
                    }
                }

                ksort($records[$k]['cell']);

                if ($this->isTreeGrid) {
                    $records[$k]['cell'][] = $row->level; //level
                    $records[$k]['cell'][] = $row->lft; //lft
                    $records[$k]['cell'][] = $row->rgt; //rgt
                    $records[$k]['cell'][] = (($row->rgt - $row->lft) == 1); //isLeaf
                    $records[$k]['cell'][] = ($row->level < 1);
                }
            }

            return $records;
        }

        /**
         * Parse request and prepare filter parameters
         *
         * @param Request $request
         *
         * @return array
         */
        protected function _getFilterParams(Request $request)
        {

            $filters = array();

            // Multiple field filtering
            if ($request->getPost('filters')) {
                $filter = Json::decode($request->getPost('filters'), Json::TYPE_ARRAY);

                if (count($filter['rules']) > 0) {
                    foreach ($filter['rules'] as $rule) {
                        $filters['field'][]      = $rule['field'];
                        $filters['value'][]      = $rule['data'];
                        $filters['expression'][] = $this->_expression[$rule['op']];
                    }

                    $filters['options']['multiple'] = true;
                    $filters['options']['boolean']  = (isset($filter['groupOp'])) ? $filter['groupOp'] : 'AND';

                    return $filters;
                }
            }

            // Single field filtering
            return array(
                'field'      => $request->getPost('searchField'),
                'value'      => trim($request->getPost('searchString')),
                'expression' => $this->_expression[$request->getPost('searchOper', 'eq')],
                'options'    => array()
            );
        }


        /**
         * Set up column sizes based on user settings taken from cookies
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function prepareColumnSizes()
        {
            $id           = $this->getId();
            $columnCookie = str_replace('/', '_', strtolower(self::COOKIE_COLUMNS_SIZES_PREFIX . $id));
            $customSizes  = false;
            if (isset($_COOKIE[$columnCookie])) {
                $sizes = explode(';', $_COOKIE[$columnCookie]);
                if (is_array($sizes)) {
                    foreach ($sizes as $colsize) {
                        list($name, $size) = explode(':', $colsize);
                        $column = $this->getColumn($name);
                        if ($column) {
                            $column->setWidth($size);
                            $customSizes = true;
                        }
                    }
                }
            }
            $this->setAutowidth(!$customSizes);
            $this->setShrinkToFit(!$customSizes);

            return $this;
        }

        /**
         * Set up sorting based on user settings taken from cookies
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function prepareSorting()
        {
            $id            = $this->getId();
            $url           = $this->getUrl();
            $sortingCookie = str_replace('/', '_', strtolower(self::COOKIE_SORTING_PREFIX . $id));
            if (isset($_COOKIE[$sortingCookie])) {
                list($name, $order) = explode(':', $_COOKIE[$sortingCookie]);
                if ($name && $order) {
                    $this->setSortname($name);
                    $this->setSortorder($order);
                }
            }

            return $this;
        }

        /**
         * Set up paging based on user settings taken from cookies
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function preparePaging()
        {
            $id           = $this->getId();
            $url          = $this->getUrl();
            $pagingCookie = str_replace('/', '_', strtolower(self::COOKIE_PAGING_PREFIX . $id));
            if (isset($_COOKIE[$pagingCookie])) {
                $rowNum = $_COOKIE[$pagingCookie];
                if ($rowNum) {
                    $this->setRowNum($rowNum);
                }
            }

            return $this;
        }

        /**
         * Set up column ordering based on user settings taken from cookies
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function reorderColumns()
        {
            if ($this->getSortable()) {
                $id             = $this->getId();
                $url            = $this->getUrl();
                $orderingCookie = str_replace('/', '_', strtolower(self::COOKIE_COLUMNS_ORDERING_PREFIX . $id));
                if (isset($_COOKIE[$orderingCookie])) {
                    $ordering = explode(':', $_COOKIE[$orderingCookie]);
                    if (count($ordering) == count($this->_columns)) {
                        $newColumns = array();
                        foreach ($ordering as $col) {
                            foreach ($this->_columns as $oldCol) {
                                if ($oldCol->getName() == $col) {
                                    $newColumns[] = $oldCol;
                                    break;
                                }
                            }
                        }
                        $this->setColumns($newColumns);
                        $this->setColModel($this->_columns);
                        $this->setColNames($this->_columns);
                    }
                }
            }

            return $this;
        }

        /**
         * Attach detail grid to current master grid
         *
         * @param array $options options array
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function attachDetail($options = array())
        {
            if (array_key_exists('detailGridId', $options) && array_key_exists('detailFieldName', $options)) {
                if (!array_key_exists('captionPrefix', $options)) {
                    $options['captionPrefix'] = '';
                }
                $this->setOnSelectRow($this->getJsCode()->prepareDetailCode($options['detailGridId'], $options['detailFieldName'], $options['captionPrefix']));
                $this->setGridComplete($this->getJsCode()->prepareDetailCodeGridComplete());
            }

            return $this;
        }

        /**
         * Attach current detail grid to given master grid
         *
         * @param string $id id of master grid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function attachToMaster($masterId = '')
        {
            $this->setIsDetailGrid(true);
            $this->setMasterGridId($masterId);

            return $this;
        }

        /**
         * Set columns array
         *
         * @param array $columns array of grid columns
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setColumns($columns = null)
        {
            $this->_columns = $columns;

            return $this;
        }

        /**
         * Get JsCode object instance
         *
         * @return \SynergyDataGrid\Grid\JsCode
         */
        public function getJsCode()
        {
            return $this->_jsCode;
        }

        /**
         * Set JsCode object
         *
         * @param \SynergyDataGrid\Grid\JsCode $jsCode JsCode object instance
         *
         * @return JsGrid
         */
        public function setJsCode($jsCode)
        {
            $this->_jsCode = $jsCode;

            return $this;
        }

        /**
         * Get Html code for EntityGrid
         *
         * @return string
         */
        public function getHtml()
        {
            return $this->_html;
        }

        /**
         * Set Html code for EntityGrid
         *
         * @param string $html html code for EntityGrid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setHtml($html)
        {
            $this->_html = $html;

            return $this;
        }

        /**
         * Get JS code for EntityGrid
         *
         * @return string
         */
        public function getJs()
        {
            return $this->_js;
        }

        /**
         * Set JS code for EntityGrid
         *
         * @param string $js JS code for EntityGrid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setJs($js)
        {
            $this->_js = $js;

            return $this;
        }

        /**
         * Get JS code placed in onload section for EntityGrid
         *
         * @return string
         */
        public function getOnload()
        {
            return $this->_onload;
        }

        /**
         * Set JS code placed in onload section for EntityGrid
         *
         * @param string $onload JS code for EntityGrid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setOnload($onload)
        {
            $this->_onload = $onload;

            return $this;
        }


        /**
         * Get actions column
         *
         * @return \SynergyDataGrid\Grid\Column
         */
        public function getActionsColumn()
        {
            return $this->_actionsColumn;
        }

        /**
         * Set actions column
         *
         * @param \SynergyDataGrid\Grid\Column
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setActionsColumn($actionsColumn)
        {
            $this->_actionsColumn = $actionsColumn;

            return $this;
        }

        /**
         * Get allowEdit setting
         *
         * @return bool
         */
        public function getAllowEdit()
        {
            return $this->_allowEdit;
        }

        /**
         * Set allowEdit setting and make corresponding changes in EntityGrid object
         *
         * @param bool $allowEdit allow edit flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setAllowEdit($allowEdit)
        {
            $this->_allowEdit = $allowEdit;
            $actionColumn     = $this->getColumn('myac');
            if ($actionColumn) {
                $currentFormatOptions = $actionColumn
                    ->getFormatoptions()
                    ->setEditbutton($allowEdit);
                $actionColumn->setFormatoptions($currentFormatOptions);
                $this->setColModel($this->_columns);
            }
            if ($allowEdit) {
                $this->getNavGrid()->mergeOptions(array('add' => false));
                $this->getInlineNav()->mergeOptions(array('add' => true));
            } else {
                $this->getInlineNav()->mergeOptions(array('add' => false));
            }

            return $this;
        }

        /**
         * Get allowEditForm setting
         *
         * @return bool
         */
        public function getAllowEditForm()
        {
            return $this->_allowEditForm;
        }

        /**
         * Set allowEditForm setting and make corresponding changes in EntityGrid object
         *
         * @param bool $allowEditForm allow edit in form format flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setAllowEditForm($allowEditForm)
        {
            $this->_allowEditForm = $allowEditForm;
            $actionColumn         = $this->getColumn('myac');
            if ($actionColumn) {
                $currentFormatOptions = $actionColumn
                    ->getFormatoptions()
                    ->setEditformbutton($allowEditForm);
                $actionColumn->setFormatoptions($currentFormatOptions);
                $this->setColModel($this->_columns);
            }
            if ($allowEditForm) {
                $this->getNavGrid()->mergeAddparameters(array('closeOnEscape' => true));
                $this->getInlineNav()->mergeOptions(array('add' => false));
            }

            return $this;
        }

        /**
         * Get allowDelete setting
         *
         * @return bool
         */
        public function getAllowDelete()
        {
            return $this->_allowDelete;
        }

        /**
         * Set allowDelete setting and make corresponding changes in EntityGrid object
         *
         * @param bool $allowDelete allow delete flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setAllowDelete($allowDelete)
        {
            $this->_allowDelete = $allowDelete;
            $actionColumn       = $this->getColumn('myac');
            if ($actionColumn) {
                $currentFormatOptions = $actionColumn
                    ->getFormatoptions()
                    ->setDelbutton($allowDelete);
                $actionColumn->setFormatoptions($currentFormatOptions);
                $this->setColModel($this->_columns);
            }

            return $this;
        }

        /**
         * Get allowAdd setting
         *
         * @return bool
         */
        public function getAllowAdd()
        {
            return $this->_allowAdd;
        }

        /**
         * Set allowAdd setting and make corresponding changes in EntityGrid object
         *
         * @param bool $allowAdd allow add flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setAllowAdd($allowAdd)
        {
            $this->_allowAdd = $allowAdd;
            $this->getInlineNav()->setAdd($allowAdd);

            return $this;
        }

        /**
         * Get current grid columns
         *
         * @return array
         */
        public function getColumns()
        {
            return $this->_columns;
        }

        /**
         * Set Column Model for current grid
         *
         * @see http://www.trirand.com/EntityGridwiki/doku.php?id=wiki:colmodel_options
         *
         * @param array $columns array of columns
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setColModel($columns)
        {
            $colModel = array();
            foreach ($columns as $column) {
                $colModel[] = $column->getOptions();
                //datepicker doesn't work with edittype=date
                if (array_key_exists('edittype', $colModel[count($colModel) - 1])
                    and $colModel[count($colModel) - 1]['edittype'] == 'date'
                ) {
                    unset($colModel[count($colModel) - 1]['edittype']);
                }
            }
            $this->_options['colModel'] = $colModel;

            return $this;
        }

        /**
         * Set Column Names for current grid
         *
         * @see http://www.trirand.com/JqGridwiki/doku.php?id=wiki:options#colNames
         *
         * @param array $columns array of columns
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setColNames($columns)
        {
            $colNames = array();
            foreach ($columns as $column) {
                $colNames[] = $column->getTitle();
            }
            $this->_options['colNames'] = $colNames;

            return $this;
        }

        /**
         * Get last selected row javascript variable name
         *
         * @return string
         */
        public function getLastSelectVariable()
        {
            return $this->_lastSelectVariable;
        }

        /**
         * Set last select javascript variable name
         *
         * @param string $lastSelectVariable last selected row javascript variable name
         *
         * @return EntityGrid
         */
        public function setLastSelectVariable($lastSelectVariable)
        {
            $this->_lastSelectVariable = preg_replace('/[^a-z0-9\_]/i', '', $lastSelectVariable);

            return $this;
        }

        /**
         * Get grid column by name
         *
         * @param string $name column name
         *
         * @return \SynergyDataGrid\Grid\Column
         */
        public function getColumn($name)
        {
            $return = false;
            if (array_key_exists($name, $this->_columns)) {
                $return = $this->_columns[$name];
            }

            return $return;
        }

        /**
         * Set column for curren grid
         *
         * @param \SynergyDataGrid\Grid\Column $column column object
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setColumn($column)
        {
            if (gettype($column) == 'object' && substr(get_class($column), -7) == '\Column') {
                $name                  = $column->getName();
                $this->_columns[$name] = $column;
                $this->setColModel($this->_columns);
            }

            return $this;
        }

        /**
         * Get javascript code for processAfterSubmit jqGrid event
         *
         * @return \Zend_Json_Expr
         */
        public function getProcessAfterSubmit()
        {
            return $this->_processAfterSubmit;
        }

        /**
         * Set javascript code for processAfterSubmit jqGrid event
         *
         * @param \Zend_Json_Expr $processAfterSubmit javascript code for an event
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setProcessAfterSubmit($processAfterSubmit = '')
        {
            $this->_processAfterSubmit = $processAfterSubmit;

            return $this;
        }

        /**
         * Get inine navigation enabled flag
         *
         * @return bool
         */
        public function getInlineNavEnabled()
        {
            return $this->_inlineNavEnabled;
        }

        /**
         * Set inline navigation enabled flag
         *
         * @param bool $inlineNavEnabled inline navigation enabled flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setInlineNavEnabled($inlineNavEnabled)
        {
            $this->_inlineNavEnabled = $inlineNavEnabled;

            return $this;
        }

        /**
         * Get InineNav object instance for current grid
         *
         * @return \SynergyDataGrid\InlineNav
         */
        public function getInlineNav()
        {
            return $this->_inlineNav;
        }

        /**
         * Set InlineNav instance for current grid
         *
         * @param mixed $options inline navigation as an array of options or \SynergyDataGrid\InlineNav
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setInlineNav($options)
        {
            if (gettype($options) == 'object') {
                $this->_inlineNav = $options;
            } else {
                $this->_inlineNav = new InlineNav($this, $options);
            }

            return $this;
        }

        /**
         * Get NavGrid enabled flag
         *
         * @return bool
         */
        public function getNavGridEnabled()
        {
            return $this->_navGridEnabled;
        }

        /**
         * Set navigation enabled flag
         *
         * @param bool $navGridEnabled navigation enabled flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setNavGridEnabled($navGridEnabled)
        {
            $this->_navGridEnabled = $navGridEnabled;

            return $this;
        }

        /**
         * Get NavGrid object instance for current grid
         *
         * @return \SynergyDataGrid\Grid\NavGrid
         */
        public function getNavGrid()
        {
            return $this->_navGrid;
        }

        /**
         * Set NavGrid instance for current grid
         *
         * @param mixed $options navigation as an array of options or \SynergyDataGrid\NavGrid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setNavGrid($options)
        {
            if (gettype($options) == 'object') {
                $this->_navGrid = $options;
            } else {
                $this->_navGrid = new NavGrid($this, $options);
            }

            return $this;
        }

        /**
         * Get DatePicker object instance for current grid
         *
         * @return \SynergyDataGrid\Grid\Plugin\DatePicker
         */
        public function getDatePicker()
        {
            return $this->_datePicker;
        }

        /**
         * Set DatePicker instance for current grid
         *
         * @param \SynergyDataGrid\DatePicker $datePicker $datePicker object instance for current grid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setDatePicker($datePicker)
        {
            if (gettype($datePicker) == 'object') {
                $this->_datePicker = $datePicker;
            } else {
                $this->_datePicker = new DatePicker($this, $datePicker);
            }

            return $this;
        }

        /**
         * Get multiple search flag
         *
         * @return bool
         */
        public function getMultipleSearch()
        {
            return $this->_multipleSearch;
        }

        /**
         * Set multiple search flag
         *
         * @param bool $multipleSearch multiple search flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setMultipleSearch($multipleSearch)
        {
            $this->_multipleSearch = $multipleSearch;
            $this->getNavGrid()->mergeSearchParameters(array('multipleSearch' => $multipleSearch));

            return $this;
        }

        /**
         * Get row actions button by name
         *
         * @param string $name row action button name
         *
         * @return array
         */
        public function getRowActionButton($name = '')
        {
            $return = '';
            if (array_key_exists($name, $this->_rowActionButtons)) {
                $return = $this->_rowActionButtons[$name];
            }

            return $return;
        }

        /**
         * Get all row actions buttons
         *
         * @return array
         */
        public function getRowActionButtons()
        {
            return $this->_rowActionButtons;
        }

        /**
         * Set row actions button
         *
         * @param array $options array of options
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setRowActionButton($options = array())
        {
            if (array_key_exists('name', $options) && array_key_exists('icon', $options)) {
                $options['class']                          = !array_key_exists('class', $options) ? 'ui-inline-' . strtolower(str_replace(' ', '-', $options['name'])) : $options['class'];
                $this->_rowActionButtons[$options['name']] = !array_key_exists($options['name'], $this->_rowActionButtons) ? $options : array_merge($this->_rowActionButtons[$options['name']], $options);
            }

            return $this;
        }

        /**
         * Set all row actions buttons
         *
         * @param array $rowActionButtons array of options
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setRowActionButtons($rowActionButtons = array())
        {
            foreach ($rowActionButtons as $button) {
                $this->setRowActionButton($button);
            }

            return $this;
        }

        /**
         * Get custom navigator button by title
         *
         * @param string $title title of custom navigator button
         *
         * @return array
         */
        public function getNavButton($title = '')
        {
            $return = '';
            if (array_key_exists($title, $this->_navButtons)) {
                $return = $this->_navButtons[$title];
            }

            return $return;
        }

        /**
         * Get all custom navigator buttons
         *
         * @return array
         */
        public function getNavButtons()
        {
            return $this->_navButtons;
        }

        /**
         * Set custom navigator button
         *
         * @param array $options options for custom navigator button
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setNavButton($options = array())
        {
            if (array_key_exists('title', $options)) {
                $options['icon']    = !array_key_exists('icon', $options) ? 'none' : $options['icon'];
                $options['caption'] = !array_key_exists('caption', $options) ? '' : $options['caption'];
                $options['action']  = !array_key_exists('action', $options) ? null : $options['action'];
                if ($options['action'] !== null && substr($options['action'], 0, 8) !== 'function') {
                    $options['action'] = 'function() { ' . $options['action'] . '}';
                }
                $options['position']               = !array_key_exists('position', $options) ? 'last' : $options['position'];
                $options['cursor']                 = !array_key_exists('cursor', $options) ? 'pointer' : $options['cursor'];
                $options['id']                     = !array_key_exists('id', $options) ? strtolower(str_replace(' ', '_', $options['title'])) . '_' . $this->getId() : $options['id'];
                $this->_navButtons[$options['id']] = !array_key_exists($options['id'], $this->_navButtons) ? $options : array_merge($this->_navButtons[$options['id']], $options);
            }

            return $this;
        }

        /**
         * Set all custom navigator buttons
         *
         * @param array $options array of options for all custom navigator buttons
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setNavButtons($navButtons = array())
        {
            foreach ($navButtons as $button) {
                $this->setNavButton($button);
            }

            return $this;
        }

        protected function _isSubGrid($name)
        {
            return isset($this->_config['column_model'][$name]['isSubGrid'])
                and $this->_config['column_model'][$name]['isSubGrid'];
        }

        protected function _isSubGridAsGrid($name)
        {
            return isset($this->_config['column_model'][$name]['isSubGridAsGrid'])
                and $this->_config['column_model'][$name]['isSubGridAsGrid'];
        }

        /**
         * Get allowResizeColumns flag
         *
         * @return bool
         */
        public function getAllowResizeColumns()
        {
            return $this->_allowResizeColumns;
        }

        /**
         * Set allowResizeColumns flag
         *
         * @param bool $allowResizeColumns allow reize columns flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setAllowResizeColumns($allowResizeColumns = true)
        {
            $this->_allowResizeColumns = $allowResizeColumns;
            if (!$allowResizeColumns) {
                $this->setResizeStop(null);
                $this->setAutowidth(true);
                // no columns allowed to be resized

                foreach ($this->_columns as $column) {
                    $column->setResizable(false);
                }
            } else {
                $this->setResizeStop($this->getJsCode()->prepareSetColumnSizeCookie());
            }

            return $this;
        }

        /**
         * Get reloadAfterResize flag
         *
         * @return bool
         */
        public function getReloadAfterResize()
        {
            return $this->_reloadAfterResize;
        }

        /**
         * Set reloadAfterResize flag
         *
         * @param bool $reloadAfterResize reload after resize flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setReloadAfterResize($reloadAfterResize)
        {
            $this->_reloadAfterResize = $reloadAfterResize;
            $this->setResizeStop($this->getJsCode()->prepareSetColumnSizeCookie());

            return $this;
        }

        /**
         * Get reloadAfterChangeColumnOrdering flag
         *
         * @return bool
         */
        public function getReloadAfterChangeColumnsOrdering()
        {
            return $this->_reloadAfterChangeColumnsOrdering;
        }

        /**
         * Set reloadAfterChangeColumnOrdering flag
         *
         * @param bool $reloadAfterChangeColumnOrdering reload after change column ordering flag
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setReloadAfterChangeColumnsOrdering($reloadAfterChangeColumnsOrdering)
        {
            $this->_reloadAfterChangeColumnsOrdering = $reloadAfterChangeColumnsOrdering;

            return $this;
        }

        /**
         * Determine if current grid is detail one
         *
         * @return bool
         */
        public function getIsDetailGrid()
        {
            return $this->_isDetailGrid;
        }

        /**
         * Mark current grid as a detail one
         *
         * @param bool $isDetailGrid flag to deterimine if this grid is detail one
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setIsDetailGrid($isDetailGrid)
        {
            $this->_isDetailGrid = $isDetailGrid;

            return $this;
        }

        /**
         * Get master grid id
         *
         * @return string
         */
        public function getMasterGridId()
        {
            return $this->_masterGridId;
        }

        /**
         * Set master grid id for cuurent detail grid
         *
         * @param string $masterGridId id of master grid
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setMasterGridId($masterGridId)
        {
            $this->_masterGridId = $masterGridId;

            return $this;
        }

        public function setUrl($url)
        {
            $this->_url            = $url;
            $this->_options['url'] = $url;

            return $this;
        }

        public function getUrl()
        {
            if (!$this->_url) {
                $request = $this->getService()->getServiceManager()->get('request');
                if ($request instanceof Request) {
                    $this->_url = $request->getRequestUri();
                }
            }

            return $this->_url;
        }


        /**
         * Get current grid id
         *
         * @return string
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * Set grid id
         *
         * @param string $id EntityGrid id
         *
         * @return \SynergyDataGrid\Grid\GridType\BaseGrid
         */
        public function setId($id)
        {
            $this->_id = self::ID_PREFIX . preg_replace('/[^a-z0-9]/i', '', $id);

            return $this;
        }

        public function setIsTreeGrid($isTreeGrid)
        {
            $this->isTreeGrid = $isTreeGrid;
        }

        public function getIsTreeGrid()
        {
            return $this->isTreeGrid;
        }

        public function setNodeData($nodeData)
        {
            $this->_nodeData = $nodeData;
        }

        public function getNodeData()
        {
            return $this->_nodeData;
        }

        /**
         * @return Toolbar
         */
        public function getToolbarConfig()
        {
            return $this->_toolbarConfig;
        }

        public function getConfig()
        {
            return $this->_config;
        }


        /**
         * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
         */
        public function setServiceLocator($serviceLocator)
        {
            $this->_serviceLocator = $serviceLocator;

            return $this;
        }

        /**
         * @return \Zend\ServiceManager\ServiceLocatorInterface
         */
        public function getServiceLocator()
        {
            return $this->_serviceLocator;
        }


        public function setConfig($config)
        {
            $this->_config = $config;

            return $this;
        }

        /**
         * Set service model for current grid
         *
         * @param ServiceLocatorInterface $serviceManager
         *
         * @return mixed
         */
        abstract public function setService(ServiceLocatorInterface $serviceManager);


        /**
         * Delete record based on passed id and return result
         *
         * @param \Zend\Http\Request $request
         *
         * @return string
         */
        abstract public function delete(Request $request);

        /**
         * Edit record  (CRUD) based on passed id and return result
         *
         * @param $request
         *
         * @return string
         */
        abstract public function edit($request);

        /**
         * Function to replace the deprecated render function
         *Completely render current grid object or just send AJAX response
         *
         * @return string
         */
        abstract public function prepareGridData(RequestInterface $request = null, $options = array());

        /**
         * Set column model for grid
         *
         * @return mixed
         */
        abstract public function setGridColumns();

        /**
         * Get association mapping data for jon column
         *
         * @param $map
         * @param $fieldName
         *
         * @return mixed
         */
        abstract protected function _getRelatedList($map, $fieldName);

        /**
         * Create grid data based on request and using pagination
         *
         * @param \Zend\Http\Request $request
         *
         * @return \stdClass
         */
        abstract protected function _createGridData(Request $request, $dataOnly = false);

        /**
         * Preload grid with data
         */
        abstract public function getFirstDataAsLocal(Request $request, $dataOnly = false);

        /**
         * Binds the grid to the database entity and assigns an ID to the grid
         *
         * @param      $entityClassName
         * @param null $gridId
         */
        abstract public function setGridIdentity($entityClassName, $gridId = '', $displayTree = true);


        /**
         * Set the object manager (entityManager, etc for the grid type)
         *
         * @param ServiceLocatorInterface $serviceManager
         *
         * @return mixed
         */
        abstract public function setObjectManager(ServiceLocatorInterface $serviceManager);

        /**
         * @param $filer
         * @param $sort
         * @param $treeFiler
         *
         * @return \Zend\Paginator\Paginator
         */

        abstract protected function getPaginator($request, $service);

        /**
         * Get user defined query builder for paginato
         *
         * @return mixed
         */
        abstract public function getCustomQueryBuilder();
    }