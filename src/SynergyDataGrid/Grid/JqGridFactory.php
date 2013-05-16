<?php
    namespace SynergyDataGrid\Grid;

    use Doctrine\ORM\AbstractQuery;
    use SynergyDataGrid\Grid\Column;
    use SynergyDataGrid\Util\ArrayUtils;
    use Zend\Json\Expr;
    use Zend\Json\Json;
    use Zend\Http\Request;
    use Zend\Paginator\Paginator;
    use SynergyDataGrid\Model\BaseService;
    use SynergyDataGrid\Grid\JsCode;
    use Zend\ServiceManager\FactoryInterface;
    use Zend\ServiceManager\ServiceLocatorInterface;

    /**
     * JqGrid class for implement base jqGrid plugin functionality
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:jqgriddocs
     * @package mvcgrid
     */
    class JqGridFactory extends Base implements FactoryInterface
    {
        const SORT_ID         = 'sidx';
        const SORT_ORDER_ID   = 'order';
        const SEARCH_ID       = 'search';
        const ND_ID           = 'nd';
        const OPERATOR_KEY    = 'oper';
        const OPER_EDIT       = 'editoper';
        const OPER_ADD        = 'addoper';
        const OPER_DELETE     = 'deloper';
        const SUBGRID_ID      = 'subgridid';
        const TOTALROWS_ID    = 'totalrows';
        const SORT_COLUMN     = 'title';
        const TOP             = 'top';
        const BOTTOM          = 'bottom';
        const RECORD_LIMIT    = 'rows';
        const CURRENT_PAGE    = 'page';
        const CURRENT_ROW     = 'id';
        const ROW_TOTAL       = 'totalrows';
        const ID_PREFIX       = 'grid_';
        const GRID_IDENTIFIER = 'grid';
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
         * Entity class name
         *
         * @var string
         */
        private $_entity;
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
         * Grid id
         *
         * @var string
         */
        protected $_id;
        /**
         * Rows per page count, defsult setting
         *
         * @var int
         */
        protected $_defaultItemCountPerPage = 20;
        /**
         * Service model, attached to grid
         *
         * @var int
         */
        protected $_service;
        /**
         * @var \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
         */
        protected $_serviceLocator;
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
         * @var \SynergyDataGrid\Grid\DatePicker
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
            'bn' => 'NOT_BEGIN_WITH', 'in' => 'IN',
            'ni' => 'NOT_IN', 'ew' => 'END_WITH',
            'en' => 'NOT_END_WITH',
            'cn' => 'CONTAIN',
            'nc' => 'NOT_CONTAIN'
        );
        /**
         * The default value of this property is:
         * {page:“page”,rows:“rows”, sort:“sidx”, order:“sord”, search:“_search”, nd:“nd”, id:“id”, oper:“oper”, editoper:“edit”, addoper:“add”, deloper:“del”, subgridid:“id”, npage:null, totalrows:“totalrows”}
         * This customizes names of the fields sent to the server on a POST request. For example, with this setting, you can change the sort order element from sidx to mysort by setting prmNames: {sort: “mysort”}. The string that will be POST-ed to the server will then be myurl.php?page=1&rows=10&mysort=myindex&sord=asc rather than myurl.php?page=1&rows=10&sidx=myindex&sord=asc
         * So the value of the column on which to sort upon can be obtained by looking at $POST['mysort'] in PHP. When some parameter is set to null, it will be not sent to the server. For example if we set prmNames: {nd:null} the nd parameter will not be sent to the server. For npage option see the scroll option.
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
            self::CURRENT_ROW   => '_id_'
        );
        /**
         * JsCode class to keep all javascript code for jqGrid
         *
         * @var \SynergyDataGrid\Grid\JsCode
         */
        protected $_jsCode;
        /**
         * @var Grid Url
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
        private $isTreeGrid = false;
        private $_config;
        protected $_nodeData = array();
        /**
         * Grid configuration options
         *
         * @see module.config.php
         * @var array
         */
        protected $_options = array();
        public static $gridRegistry = array();

        /**
         * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
         *
         * @return mixed|JqGrid
         */
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            $config                = $serviceLocator->get('Config');
            $this->_config         = $config['jqgrid'];
            $this->_serviceLocator = $serviceLocator;
            $this->_jsCode         = new JsCode($this);

            return $this;
        }

        /**
         * Binds the grid to the database entity and assigns an ID to the grid
         *
         * @param      $entityClassName
         * @param null $gridId
         */
        public function setGridIdentity($entityClassName, $gridId = '')
        {
            $this->setId($gridId);
            $this->setEntity($entityClassName);
            self::$gridRegistry[] = $entityClassName;

            $mapping = $this->getService()->getEntityManager()->getClassMetadata($this->_entity);
            if ('Gedmo\Tree\Entity\Repository\NestedTreeRepository' == $mapping->customRepositoryClassName) {
                $this->setTreeGrid(true);
                $this->isTreeGrid = true;
            }

            return $this;
        }

        public function setGridColumns()
        {
            if (!$this->_columns) {
                $mapping         = $this->getService()->getEntityManager()->getClassMetadata($this->_entity);
                $excludedColumns = array_merge(
                    array_values($this->_config['tree_grid_options']['treeReader']),
                    $this->_config['excluded_columns']
                );

                $excludedColumns = array_unique($excludedColumns);

                foreach ($mapping->fieldMappings as $map) {
                    $title = ucwords($map['fieldName']);

                    if (in_array($map['fieldName'], $excludedColumns)) {
                        continue;
                    }
                    $columnData[$title] = array(
                        'name'     => $map['fieldName'],
                        'sortable' => true,
                    );

                    switch ($map['type']) {
                        case 'integer':
                            $columnData[$title]['editrules']['number'] = true;
                            break;
                    }


                    if ($map['columnName'] == 'id') {
                        $columnData[$title]['editable'] = true;
                        $columnData[$title]['key']      = true;

                        if (!$this->getIsTreeGrid()) {
                            //@TODO add config to specify column width
                            $columnData[$title]['width'] = '40px';
                        }
                    }

                    if ((isset($map['length']) and $map['length'] > 255) or $map['type'] == 'text') {
                        $columnData[$title]['edittype'] = 'textarea';
                    } elseif ('boolean' == $map['type']) {
                        $columnData[$title]['edittype']              = 'checkbox';
                        $columnData[$title]['searchoptions']['sort'] = array('eq', 'ne');
                        $columnData[$title]['editoptions']['value']  = '1:0';
                    }

                    if (in_array($map['fieldName'], $this->_config['hidden_columns'])) {
                        $columnData[$title]['hidden']                  = true;
                        $columnData[$title]['editrules']['edithidden'] = true;
                    }

                    if (in_array($map['fieldName'], $this->_config['non_editable_columns'])) {
                        $columnData[$title]['editable'] = false;
                        $columnData[$title]['hidden']   = true;
                    }

                    if (isset($this->_config['column_type_mapping'][$map['fieldName']])) {
                        $columnData[$title]['edittype'] = $columnData[$title]['stype'] = $this->_config['column_type_mapping'][$map['fieldName']];
                    }

                }

                foreach ($mapping->associationMappings as $map) {
                    if (in_array($map['fieldName'], $this->_config['excluded_columns']) or $map['type'] != 2) {
                        continue;
                    }

                    $title = ucwords($map['fieldName']);

                    if (is_callable($this->_config['association_mapping_callback'])) {
                        $function = $this->_config['association_mapping_callback'];
                        $values   = $function($this->_serviceLocator, $map['targetEntity']);
                    } else {
                        $list   = $this->_getRelatedList($map['targetEntity']);
                        $values = array(':select');
                        foreach ($list as $item) {
                            $values[] = $item['id'] . ':' . $item['title'];
                        }
                        $values = implode(';', $values);
                    }

                    $columnData[$title] = array(
                        'name'          => $map['fieldName'],
                        'edittype'      => 'select',
                        'stype'         => 'select',
                        'hidden'        => true,
                        'editrules'     => array(
                            'edithidden' => true,
                        ),
                        'sortable'      => true,
                        'searchoptions' => array(
                            'value' => $values,
                            'sopt'  => array('eq', 'ne')
                        ),
                        'editoptions'   => array(
                            'value' => $values
                        )
                    );
                }

                $this->addColumns($columnData);
                // close form after edit
                if ($actionColumn = $this->getColumn('myac')) {
                    $actionColumn->mergeFormatoptions(array('editOptions' => array('closeAfterEdit' => true)));
                }
            }

            return $this;
        }

        /**
         * Set up default options before applying user defined options
         *
         * @param string $id id of grid
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setGridDisplayOptions()
        {
            $this->setPager($this->getId() . '_pager');
            $this->setRowList(range($this->_defaultItemCountPerPage, $this->_defaultItemCountPerPage * 5, $this->_defaultItemCountPerPage));
            $this->setPrmNames($this->_prmNames);

            //Set grid options
            $gridOptions = isset($this->_config['grid_options']) ? $this->_config['grid_options'] : array();
            foreach ($gridOptions as $key => $val) {
                $method = 'set' . ucfirst($key);
                $this->$method($val);
            }

            //Set tree grid options
            if ($this->getIsTreeGrid()) {
                $treeOptions = isset($this->_config['tree_grid_options']) ? $this->_config['tree_grid_options'] : array();
                foreach ($treeOptions as $key => $val) {
                    $method = 'set' . ucfirst($key);
                    $this->$method($val);
                }
            }

            //Set default data to be posted back to server
            $this->setPostData(array(self::GRID_IDENTIFIER => $this->getId()));

            $this->setDatePicker(new DatePicker($this));
            $datePickerFunctionName = $this->getDatePicker()->getFunctionName();

            //Set navigation options
            $navGrid = isset($this->_config['nav_grid']) ? $this->_config['nav_grid'] : array();
            $this->setNavGrid($navGrid);

            //Add parameters
            $addParameters = isset($this->_config['add_parameters']) ? $this->_config['add_parameters'] : array();
            $this->getNavGrid()->mergeAddParameters($addParameters);


            //Edit parameters
            $editParameters = isset($this->_config['edit_parameters']) ? $this->_config['edit_parameters'] : array();
            $this->getNavGrid()->mergeEditParameters($editParameters);


            //set add/edit form options
            $formOptions = isset($this->_config['form_options']) ? $this->_config['form_options'] : array();
            if ($formOptions) {
                $this->getNavGrid()->mergeAddParameters($formOptions);
                $this->getNavGrid()->mergeEditParameters($formOptions);
            }

            $inlineNavOptions = isset($this->_config['inline_nav']) ? $this->_config['inline_nav'] : array();
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

            if ($this->getActionsColumn()) {
                $this->getJsCode()->addActionsColumn();
                $this->_config['nav_grid']['edit'] = false;
                $this->_config['nav_grid']['del']  = false;
                $this->setNavGrid($this->_config['nav_grid']);
            }


            $this->setOnSortCol(new Expr($this->getJsCode()->prepareSetSortingCookie()));
            $this->setOnPaging(new Expr($this->getJsCode()->prepareSetPagingCookie()));

            return $this;
        }

        /**
         * Create Column object based on given options and add it to the grid
         *
         * @param string $columnTitle title of a column
         * @param array  $column      array of column options
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function addColumns($columns = array())
        {
            if (is_array($columns) && count($columns)) {
                foreach ($columns as $colName => $column) {
                    $this->addColumn($colName, $column);
                }
                $this->setColNames($this->_columns);
                $this->setColModel($this->_columns);
            }

            return $this;
        }

        /**
         * Function to replace the deprecated render function
         *Completely render current grid object or just send AJAX response
         *
         * @return string
         */
        public function prepareGridData()
        {
            try {
                $serviceManager = $this->getService()->getServiceManager();
                $request        = $serviceManager->get('request');
                if (!$this->getUrl()) {
                    $this->setUrl($request->getRequestUri());
                }
                $data = null;
                $this->setGridColumns();
                $operation = $request->getPost('oper');

                if ($request->getPost(self::GRID_IDENTIFIER) == $this->getId()) {
                    $data = $this->_createGridData($request);
                } elseif ($operation == 'del') {
                    $data = $this->delete($request);
                } elseif ($operation == 'edit' || $operation == 'add') {
                    $data = $this->edit($request);
                }

            } catch (\Exception $e) {
                header('HTTP/1.1 400 Error Saving Data');
                $data = array('error' => $e->getMessage());
            }

            return $data;
        }

        /**
         * Create grid data based on request and using pagination
         *
         * @param \Zend\Http\Request $request
         *
         * @return \stdClass
         */
        protected function _createGridData(Request $request)
        {
            $filter     = $request->getPost('_search') == 'true' ? $this->_getFilterParams($request) : false;
            $treeFilter = array();

            if ($this->isTreeGrid) {
                $sort = array(
                    'sidx' => 'lft',
                    'sord' => 'ASC'
                );
                $node = $request->getPost('nodeid', false);
                if ($node > 0) {
                    $n_lvl      = (integer)$request->getPost("n_level");
                    $n_lvl      = $n_lvl + 1;
                    $treeFilter = array(
                        'lft'    => (integer)$request->getPost("n_left"),
                        'rgt'    => (integer)$request->getPost("n_right"),
                        'level'  => $n_lvl,
                        'parent' => $node
                    );

                } else {
                    $treeFilter = array(
                        'level' => 1
                    );
                }

            } else {
                $sort = $request->getPost('sidx') ? array('sidx' => $request->getPost('sidx'), 'sord' => $request->getPost('sord')) : false;
            }


            $adapter = new PaginatorAdapter($this->getService(), $filter, $sort, $this, $treeFilter);

            // Instantiate Zend_Paginator with the required data source adapter
            if (!$this->_paginator instanceof Paginator) {
                $this->_paginator = new Paginator($adapter);
                $this->_paginator->setDefaultItemCountPerPage($request->getPost('rows', $this->_defaultItemCountPerPage));
            }

            // Pass the current page number to paginator
            $this->_paginator->setCurrentPageNumber($request->getPost('page', 1));

            // Fetch a row of items from the adapter
            $rows = $this->_paginator->getCurrentItems();

            $grid = array(
                'page'    => $this->_paginator->getCurrentPageNumber(),
                'total'   => ceil($this->_paginator->getTotalItemCount() / $this->_paginator->getItemCountPerPage()),
                'records' => $this->_paginator->getTotalItemCount(),
                'rows'    => array()
            );

            $this->reorderColumns();
            $columns     = $this->setGridColumns()->getColumns();
            $columnNames = array_keys($columns);

            foreach ($rows as $k => $row) {

                if (isset($row->id)) {
                    $grid['rows'][$k]['id'] = $row->id;
                }

                $grid['rows'][$k]['cell'] = array();
                /**
                 * @var $column \SynergyDataGrid\Grid\Column
                 */
                foreach ($columns as $name => $column) {

                    $index = array_search($name, $columnNames);
                    if ($index !== false) {
                        $grid['rows'][$k]['cell'][$index] = $column->cellValue($row);
                    }
                }

                ksort($grid['rows'][$k]['cell']);

                if ($this->isTreeGrid) {
                    $grid['rows'][$k]['cell'][] = $row->level; //level
                    $grid['rows'][$k]['cell'][] = $row->lft; //lft
                    $grid['rows'][$k]['cell'][] = $row->rgt; //rgt
                    $grid['rows'][$k]['cell'][] = (($row->rgt - $row->lft) == 1); //isLeaf
                    $grid['rows'][$k]['cell'][] = 0; //($row->level == 0); //expanded
                }
            }

            if ($this->isTreeGrid) {
                $this->setTreeGrid(true);
            }

            return $grid;
        }

        /**
         * Parse request and prepare filter parameters
         *
         * @param Request $request
         *
         * @return array
         */
        private function _getFilterParams(Request $request)
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
         * Delete record based on passed id and return result
         *
         * @param \Zend\Http\Request $request
         *
         * @return string
         */
        public function delete(Request $request)
        {
            $id      = $request->getPost('id');
            $retv    = false;
            $message = 'Unable to delete record.';
            if ($id) {
                $retv = $this->getService()->remove($id);
                if ($retv) {
                    $message = '';
                }
            }

            return array('success' => $retv, 'message' => $message);
        }

        /**
         * Edit record based on passed id and return result
         *
         * @param $request
         *
         * @return string
         */
        public function edit($request)
        {
            $params      = $request->getPost();
            $pass        = true;
            $id          = 0;
            $message     = '';
            $service     = $this->getService();
            $entityClass = $service->getEntityClass();

            if (array_key_exists('id', $params) && $params['id'] && $params['id'] != 'new_row' && $params['id'] != '_empty') {
                $entity = $this->getService()->findObject($params['id']);
            } else {
                $entity = new $entityClass();
            }
            if ($entity) {
                unset($params['oper']);
                unset($params['id']);

                $mapping = $service->getEntityManager()->getClassMetadata($entityClass);

                foreach ($params as $param => $value) {
                    if (array_key_exists($param, $mapping->fieldMappings) or array_key_exists($param, $mapping->associationMappings)) {
                        $type = $this->getColumn($param)->getDbColumnType();
                        if ($type == 'datetime' || $type == 'date') {
                            try {
                                $value            = new \DateTime($value);
                                $entity->{$param} = $value;
                            } catch (\Exception $e) {
                                $pass    = false;
                                $message = 'Wrong date format for column "' . $param . '"';
                                break;
                            }
                        } elseif (isset($mapping->associationMappings[$param])) {
                            $target           = $mapping->associationMappings[$param]['targetEntity'];
                            $foreignEntity    = $service->getEntityManager()->find($target, $value);
                            $entity->{$param} = $foreignEntity;
                        } else {
                            $entity->{$param} = $value;
                        }
                    }
                }

                if ($pass) {
                    try {
                        $entity  = $this->getService()->save($entity);
                        $id      = $entity->id;
                        $message = '';
                    } catch (\Exception $e) {
                        $message = $e->getMessage();
                        $pass    = false;
                    }
                }

                if (!$pass) {
                    header('HTTP/1.1 400 Error Saving Data');
                }
            }

            return array('success' => $pass, 'message' => $message, 'id' => $id);
        }

        /**
         * Set up column sizes based on user settings taken from cookies
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function prepareColumnSizes()
        {
            $id           = $this->getId();
            $url          = $this->getService()->getServiceManager()->get('request')->getRequestUri();
            $columnCookie = str_replace('/', '_', strtolower(self::COOKIE_COLUMNS_SIZES_PREFIX . $url . '_' . $id));
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function prepareSorting()
        {
            $id            = $this->getId();
            $url           = $this->getUrl();
            $sortingCookie = str_replace('/', '_', strtolower(self::COOKIE_SORTING_PREFIX . $url . '_' . $id));
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function preparePaging()
        {
            $id           = $this->getId();
            $url          = $this->getUrl();
            $pagingCookie = str_replace('/', '_', strtolower(self::COOKIE_PAGING_PREFIX . $url . '_' . $id));
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function reorderColumns()
        {
            if ($this->getSortable()) {
                $id             = $this->getId();
                $url            = $this->getUrl();
                $orderingCookie = str_replace('/', '_', strtolower(self::COOKIE_COLUMNS_ORDERING_PREFIX . $url . '_' . $id));
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * Get Html code for JqGridFactory
         *
         * @return string
         */
        public function getHtml()
        {
            return $this->_html;
        }

        /**
         * Set Html code for JqGridFactory
         *
         * @param string $html html code for JqGridFactory
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setHtml($html)
        {
            $this->_html = $html;

            return $this;
        }

        /**
         * Get JS code for JqGridFactory
         *
         * @return string
         */
        public function getJs()
        {
            return $this->_js;
        }

        /**
         * Set JS code for JqGridFactory
         *
         * @param string $js JS code for JqGridFactory
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setJs($js)
        {
            $this->_js = $js;

            return $this;
        }

        /**
         * Get JS code placed in onload section for JqGridFactory
         *
         * @return string
         */
        public function getOnload()
        {
            return $this->_onload;
        }

        /**
         * Set JS code placed in onload section for JqGridFactory
         *
         * @param string $onload JS code for JqGridFactory
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setOnload($onload)
        {
            $this->_onload = $onload;

            return $this;
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
         * @param string $id JqGridFactory id
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setId($id)
        {
            $this->_id = self::ID_PREFIX . $id . count(self::$gridRegistry);

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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * Set allowEdit setting and make corresponding changes in JqGridFactory object
         *
         * @param bool $allowEdit allow edit flag
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * Set allowEditForm setting and make corresponding changes in JqGridFactory object
         *
         * @param bool $allowEditForm allow edit in form format flag
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
                $this->getNavGrid()->mergeOptions(array('add' => true));
                $this->getInlineNav()->mergeOptions(array('add' => false));
            } else {
                $this->getNavGrid()->mergeOptions(array('add' => false));
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
         * Set allowDelete setting and make corresponding changes in JqGridFactory object
         *
         * @param bool $allowDelete allow delete flag
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * Set allowAdd setting and make corresponding changes in JqGridFactory object
         *
         * @param bool $allowAdd allow add flag
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @see http://www.trirand.com/JqGridFactorywiki/doku.php?id=wiki:colmodel_options
         *
         * @param array $columns array of columns
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setColModel($columns)
        {
            $colModel = array();
            foreach ($columns as $column) {
                $colModel[] = $column->getOptions();
                //datepicker doesn't work with edittype=date
                if (array_key_exists('edittype', $colModel[count($colModel) - 1]) && $colModel[count($colModel) - 1]['edittype'] == 'date') {
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * Get service model for current grid
         *
         * @return \SynergyDataGrid\Model\BaseService
         */
        public function getService()
        {
            if (!$this->_service) {
                $this->_service = $this->_serviceLocator->get('ModelService')->getService($this->_entity);
            }

            return $this->_service;
        }

        /**
         * Set service model for current grid
         *
         * @param \SynergyDataGrid\Model\BaseService $service service model connected to the grid
         *
         * @return JqGridFactory
         */
        public function setService($service)
        {
            $this->_service = $service;

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
         * @return JqGridFactory
         */
        public function setLastSelectVariable($lastSelectVariable)
        {
            $this->_lastSelectVariable = $lastSelectVariable;

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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\DatePicker
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
                $options['position']                  = !array_key_exists('position', $options) ? 'last' : $options['position'];
                $options['cursor']                    = !array_key_exists('cursor', $options) ? 'pointer' : $options['cursor'];
                $options['id']                        = !array_key_exists('id', $options) ? strtolower(str_replace(' ', '_', $options['title'])) . '_' . $this->getId() : $options['id'];
                $this->_navButtons[$options['title']] = !array_key_exists($options['title'], $this->_navButtons) ? $options : array_merge($this->_navButtons[$options['title']], $options);
            }

            return $this;
        }

        /**
         * Set all custom navigator buttons
         *
         * @param array $options array of options for all custom navigator buttons
         *
         * @return \SynergyDataGrid\Grid\JqGridFactory
         */
        public function setNavButtons($navButtons = array())
        {
            foreach ($navButtons as $button) {
                $this->setNavButton($button);
            }

            return $this;
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
                $this->_url = $this->getService()->getServiceManager()->get('request')->getRequestUri();
            }

            return $this->_url;
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
         * @param string $entity
         */
        public function setEntity($entity)
        {
            $this->_entity = $entity;

            return $this;
        }

        /**
         * @return string
         */
        public function getEntity()
        {
            return $this->_entity;
        }

        protected function _getRelatedList($entity)
        {
            $items = $this->getService()->getEntityManager()->getRepository($entity)->findAll();

            /*           $qb = $this->getService()->getEntityManager()->createQueryBuilder();
         /*
                       $items = $qb->select('e.*')
                           ->from($entity, 'e')
                           ->getQuery()
                           ->execute(array(), AbstractQuery::HYDRATE_OBJECT);*/

            return $items;
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

    }