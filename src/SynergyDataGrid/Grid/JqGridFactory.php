<?php
    namespace SynergyDataGrid\Grid;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\AbstractQuery;
    use Doctrine\ORM\PersistentCollection;
    use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
    use SynergyDataGrid\Grid\Column;
    use SynergyDataGrid\Util\ArrayUtils;
    use SynergyDataGrid\View\Helper\DisplayGrid;
    use Zend\Filter\Word\CamelCaseToSeparator;
    use Zend\Json\Expr;
    use Zend\Json\Json;
    use Zend\Http\Request;
    use Zend\Paginator\Paginator;
    use SynergyDataGrid\Model\BaseService;
    use SynergyDataGrid\Grid\JsCode;
    use Zend\ServiceManager\FactoryInterface;
    use Zend\ServiceManager\ServiceLocatorInterface;
    use Zend\Stdlib\RequestInterface;

    /**
     * JqGrid class for implement base jqGrid plugin functionality
     *
     * @author  Pele Odiase
     * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:jqgriddocs
     * @package mvcgrid
     */
    class JqGridFactory extends Base implements FactoryInterface
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
        const TYPE_SELECT   = 'select';
        const TYPE_TEXT     = 'text';
        const TYPE_CHECKBOX = 'checkbox';
        const TYPE_TEXTAREA = 'textarea';
        const TYPE_RADIO    = 'radio';
        const TYPE_CUSTOM   = 'custom';
        const TYPE_IMAGE    = 'image';
        const TYPE_FILE     = 'file';
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
         * use secified id
         *
         * @var int
         */
        private $_entityId;

        /**
         * Flag to indicate if columns have been set to prevent id being done twice
         *
         * @var bool
         */
        private $_columnsSet = false;
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
         * Do we already have a subrid
         *
         * @var bool
         *
         */
        protected $_hasSubGrid = false;
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
            self::CURRENT_ROW   => 'id'
        );
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
        private $isTreeGrid = false;
        /**
         * Grid configuration data
         *
         * @var
         */
        private $_config;

        protected $_nodeData = array();
        /**
         * Grid configuration options
         *
         * @see module.config.php
         * @var array
         */
        protected $_options = array();
        /**
         * Registry of grid instances
         *
         * @var array
         */
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
        public function setGridIdentity($entityClassName, $gridId = '', $displayTree = true)
        {
            $this->setEntityId($gridId);
            $this->setId($gridId);
            $this->setEntity($entityClassName);
            self::$gridRegistry[] = $entityClassName;
            $utils                = new ArrayUtils();

            if ($displayTree) {
                $mapping = $this->getService()->getEntityManager()->getClassMetadata($this->_entity);
                if ('Gedmo\Tree\Entity\Repository\NestedTreeRepository' == $mapping->customRepositoryClassName) {
                    $this->setTreeGrid(true);
                    $this->isTreeGrid = true;

                    //Set tree grid options
                    $treeOptions                   = isset($this->_config['tree_grid_options']) ? $this->_config['tree_grid_options'] : array();
                    $this->_config['grid_options'] = $utils->arrayMergeRecursiveCustom($this->_config['grid_options'], $treeOptions);
                }
            }

            /**
             * Merge grid specific configurations
             */
            if (isset($this->_config[$gridId])) {
                $this->_config['grid_options'] = $utils->arrayMergeRecursiveCustom($this->_config['grid_options'], $this->_config[$gridId]);
            }

            return $this;
        }

        /**
         * Preload grid with data
         */
        public function getFirstDataAsLocal($request)
        {
            return $this->_createGridData($request);
        }

        /**
         * Add subGrid configuration to the grid
         *
         * @param $model
         */
        protected function _getSubGridModel($subGridMap)
        {
            $subGrid  = new SubGrid();
            $params[] = $subGridMap['fieldName'];

            $mapping = $this->getService()->getEntityManager()->getClassMetadata($subGridMap['targetEntity']);

            foreach ($mapping->fieldMappings as $map) {
                if (in_array($map['fieldName'], $this->_config['excluded_columns'])) {
                    continue;
                }
                $names[] = $map['fieldName'];
            }

            foreach ($mapping->associationMappings as $map) {
                if (in_array($map['fieldName'], $this->_config['excluded_columns'])) {
                    continue;
                }
                $names[] = $map['fieldName'];
            }

            $subGrid->name = $names;
            //$subGrid->width  = $width;
            $subGrid->params = $params;


            return $subGrid;
        }

        public function _getSubGridAsGrid($subGridMap)
        {
            /** @var $subGrid \SynergyDataGrid\Grid\JqGridFactory */
            $subGrid = clone $this->getServiceLocator()->get('jqgrid');
            $subGrid->setIsDetailGrid(true);
            $subGrid->setMasterGridId($this->getId());
            $subGrid->setCaption($subGridMap['fieldName']);

            $jsCode = new JsCode($subGrid);
            $jsCode->setContainerClass('subgrid-data');
            $subGrid->setJsCode($jsCode);

            $subGrid->setGridIdentity(
                $subGridMap['targetEntity'],
                $subGridMap['fieldName'],
                '_sub'
            );

            if (is_callable($this->_config['grid_url_generator'])) {
                $url = $this->_config['grid_url_generator']($this->getServiceLocator(), $this->_entity, $subGridMap['fieldName']);
                $subGrid->setUrl($url);
            }
            $helper = new DisplayGrid();
            list($onLoad, $js, $html) = $helper->initGrid($subGrid);

            return array(
                implode("\n", $onLoad),
                implode("\n", $js),
                implode("", $html)
            );

        }


        protected function _getRowExpandFunction($data)
        {
            $onLoad = $js = $html = array();

            foreach ($data as $arr) {
                list($onLoad[], $js[], $html[]) = $arr;
            }

            $expandFunction = new Expr(
                sprintf("function(subgrid_id, row_id) {
                       jQuery('#'+subgrid_id).html('%s');
                       %s
                       %s
                }"
                    ,
                    implode("<hr />", $html),
                    implode("\n", $onLoad),
                    implode("\n", $js)
                )
            );

            return $expandFunction;
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

        public function setGridColumns()
        {
            if (!$this->_columnsSet) {
                $subGrids = $subGridsAsGrid = array();
                $target   = '';

                $utils = new ArrayUtils();
                if (!$this->_columns) {
                    $mapping         = $this->getService()->getEntityManager()->getClassMetadata($this->_entity);
                    $reflectionClass = new \ReflectionClass($this->_entity);
                    $defaultValues   = $reflectionClass->getDefaultProperties();

                    $excludedColumns = array_merge(
                        array_values($this->_config['tree_grid_options']['treeReader']),
                        $this->_config['excluded_columns']
                    );

                    $excludedColumns = array_unique($excludedColumns);
                    $count           = 0;

                    foreach ($mapping->fieldMappings as $map) {
                        $fieldName = $map['fieldName'];
                        $default   = isset($defaultValues[$fieldName]) ? $defaultValues[$fieldName] : null;

                        if (in_array($fieldName, $excludedColumns)) {
                            continue;
                        }
                        $data = array(
                            'name'        => $fieldName,
                            'edittype'    => self::TYPE_TEXT,
                            'editable'    => true,
                            'hidden'      => false,
                            'editoptions' => array(
                                'data-field-type' => $map['type'],
                                'defaultValue'    => $default,
                                'NullIfEmpty'     => $map['nullable']
                            ),
                            'editrules'   => array(
                                'edithidden' => false
                            )
                        );

                        switch ($map['type']) {
                            case 'smallint':
                            case 'integer':
                                $data['editrules']['number'] = true;
                                break;
                            case 'string' :
                                if (isset($map['length'])) {
                                    $data['editoptions']['maxlength'] = $map['length'];
                                    $data['editoptions']['size']      = $map['length'];
                                    break;
                                }
                        }


                        if ($map['columnName'] == 'id') {
                            $data['editable']              = false;
                            $data['key']                   = true;
                            $data['formoptions']['rowpos'] = 1;
                            $data['formoptions']['colpos'] = 1;

                            if ($this->isTreeGrid) {
                                $this->_config['column_model'][$fieldName]['width'] = $this->_config['tree_grid_options']['ExpandColumnWidth'];
                            }
                        }

                        if ((isset($map['length']) and $map['length'] >= 255) or $map['type'] == 'text') {
                            $data['edittype']                = self::TYPE_TEXTAREA;
                            $data['hidden']                  = true;
                            $data['editrules']['edithidden'] = true;
                        }

                        if ('boolean' == $map['type']) {
                            $data['align']                  = 'center';
                            $data['formatter']              = 'checkbox';
                            $data['edittype']               = 'checkbox';
                            $data['stype']                  = self::TYPE_SELECT;
                            $data['searchoptions']['sopt']  = array('eq', 'ne');
                            $data['searchoptions']['value'] = ':;0:No;1:Yes';
                            $data['editoptions']['value']   = '1:0';
                        }

                        if (in_array($fieldName, $this->_config['hidden_columns'])) {
                            $data['hidden']                  = true;
                            $data['editrules']['edithidden'] = true;
                        }

                        if (in_array($fieldName, $this->_config['non_editable_columns'])) {
                            $data['editable'] = false;
                            $data['hidden']   = true;
                        }

                        if (isset($this->_config['column_type_mapping'][$fieldName])) {
                            $data['edittype']
                                = $data['stype'] = $this->_config['column_type_mapping'][$fieldName];
                        }


                        if (isset($this->_config['column_model'][$fieldName])) {
                            $data = $utils->arrayMergeRecursiveCustom(
                                $data, $this->_config['column_model'][$fieldName]
                            );
                        }

                        if (!isset($data['searchoptions']['sopt'])) {
                            $data['searchoptions']['sopt'] = array_keys($this->_expression);
                        }

                        if ($this->isRequired($map, $data, $fieldName, $default)) {
                            $data['editrules']['required']    = true;
                            $data['formoptions']['elmprefix'] = '<i class="req">*</i>';
                        } else {
                            $data['editrules']['required']    = false;
                            $data['formoptions']['elmprefix'] = '<i class="nq"></i>';
                        }

                        //@todo temporary fix to tree expand column when hidden elements appear before it
                        $colIndex              = $data['hidden'] ? ($count + 99) : $count;
                        $columnData[$colIndex] = $data;

                        $count++;
                    }

                    foreach ($mapping->associationMappings as $map) {
                        $data      = array();
                        $fieldName = $map['fieldName'];
                        $default   = isset($defaultValues[$fieldName]) ? $defaultValues[$fieldName] : null;

                        if (in_array($fieldName, $this->_config['excluded_columns'])) {
                            continue;
                        }

                        if ($this->_isSubGridAsGrid($fieldName)) {
                            $subGridsAsGrid[] = $this->_getSubGridAsGrid($map);
                            $this->setSubGrid(true);
                            $data['hidden'] = true;
                        } else if (!$this->_hasSubGrid and $this->_isSubGrid($fieldName)) {
                            $subGrid        = $this->_getSubGridModel($map);
                            $target         = $fieldName;
                            $data['hidden'] = true;

                            $subGridUrl = $this->getSubGridUrl();
                            if (strpos($subGridUrl, '?') === false) {
                                $subGridUrl .= '?fieldName=' . $target;
                            } else {
                                $subGridUrl .= '&fieldName=' . $target;
                            }

                            $this->setSubGridModel(array($subGrid));
                            $this->setSubGridUrl($subGridUrl);

                            $this->_hasSubGrid = true;
                            $this->setSubGrid(true);
                        }


                        if (isset($this->_config['column_type_mapping'][$fieldName])) {
                            $type = $this->_config['column_type_mapping'][$fieldName];
                        } else {
                            $type = self::TYPE_SELECT;
                        }

                        if (isset($this->_config['association_mapping_callback'][$fieldName])
                            and  is_callable($this->_config['association_mapping_callback'][$fieldName])
                        ) {
                            $function = $this->_config['association_mapping_callback'][$fieldName];
                            $values   = $function($this->_serviceLocator, $map['targetEntity']);
                        } elseif (is_callable($this->_config['association_mapping_callback']['__default__'])) {
                            $function = $this->_config['association_mapping_callback']['__default__'];
                            $values   = $function($this->_serviceLocator, $map['targetEntity']);
                        } else {
                            $idField    = $this->_config['default_association_mapping_id'];
                            $labelField = $this->_config['default_association_mapping_label'];
                            $list       = $this->_getRelatedList($map['targetEntity'], $idField, $labelField);
                            $values     = array(':select');
                            foreach ($list as $item) {
                                $values[] = $item[$idField] . ':' . $item[$labelField];
                            }

                        }

                        if (is_array($values)) {
                            $values = implode(';', $values);
                        }

                        $data = array_merge($data, array(
                                'name'          => $fieldName,
                                'edittype'      => $type,
                                'stype'         => $type,
                                'editable'      => true,
                                'hidden'        => false,
                                'editrules'     => array(
                                    'edithidden' => true,
                                ),
                                'searchoptions' => array(
                                    'value' => $values,
                                    'sopt'  => array('eq', 'ne')
                                ),
                                'editoptions'   => array(
                                    'value' => $values,
                                )
                            )
                        );


                        if ($map['type'] == 8) {
                            $data['editoptions']['multiple'] = true;
                            $data['hidden']                  = true;
                        }

                        if (!isset($data['searchoptions']['sopt'])) {
                            $data['searchoptions']['sopt'] = array_keys($this->_expression);
                        }

                        if (isset($this->_config['column_model'][$fieldName])) {
                            $data = $utils->arrayMergeRecursiveCustom($data, $this->_config['column_model'][$fieldName]);
                        }

                        if ($this->isRequired($map, $data, $fieldName, $default)) {
                            $data['editrules']['required']    = true;
                            $data['formoptions']['elmprefix'] = '<i class="req">*</i>';
                        } else {
                            $data['editrules']['required']    = false;
                            $data['formoptions']['elmprefix'] = '<i class="nq"></i>';
                        }

                        $columnData[$count] = $data;
                        $count++;
                    }

                    ksort($columnData);
                    $this->addColumns($columnData);

                    //set subgrid expand function
                    if ($subGridsAsGrid) {
                        $expandFunction = $this->_getRowExpandFunction($subGridsAsGrid);
                        $this->setSubGridRowExpanded($expandFunction);
                    }

                    // close form after edit
                    if ($actionColumn = $this->getColumn('myac')) {
                        $actionColumn->mergeFormatoptions(array('editOptions' => array('closeAfterEdit' => true)));
                    }
                }
                $this->_columnsSet = true;
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

        /**
         * Function to replace the deprecated render function
         *Completely render current grid object or just send AJAX response
         *
         * @return string
         */
        public function prepareGridData(RequestInterface $request = null, $options = array())
        {
            try {
                if (!$request) {
                    $serviceManager = $this->getService()->getServiceManager();
                    $request        = $serviceManager->get('request');
                }
                if (!$this->getUrl()) {
                    $this->setUrl($request->getRequestUri());
                }
                $data = null;

                $str = parse_url($request->getRequestUri(), PHP_URL_QUERY);
                parse_str($str, $queryParams);

                if (isset($queryParams[self::SUB_GRID_IDENTIFIER])) {
                    $subGridid = $queryParams[self::SUB_GRID_IDENTIFIER];
                } else {
                    $subGridid = $request->getPost(self::SUB_GRID_IDENTIFIER);
                }

                if ($subGridid) {
                    $data = $this->_createSubGridData($request, $subGridid, $options['fieldName']);
                } else {
                    $this->setGridColumns();
                    $operation = $request->getPost('oper');

                    if ($request->getPost(self::GRID_IDENTIFIER) == $this->getId()) {
                        $data = $this->_createGridData($request);
                    } elseif ($operation == 'del') {
                        $data = $this->delete($request);
                    } elseif ($operation == 'edit' || $operation == 'add') {
                        $data = $this->edit($request);
                    }
                }

            } catch (\Exception $e) {
                header('HTTP/1.1 400 Error Saving Data');
                $data = array('error' => $e->getMessage());
            }

            return $data;
        }

        /**
         * Get data for the subgrid
         *
         * @param Request $request
         * @param         $id
         * @param         $field
         *
         * @return array
         */
        public function _createSubGridData(Request $request, $id, $field)
        {
            $columns   = array();
            $row       = $this->getService()->getEntityManager()->getRepository($this->_entity)->find($id);
            $method    = 'get' . ucfirst($field);
            $refObject = $row->$method();

            if ($refObject instanceof PersistentCollection) {
                $mapping      = $refObject->getMapping();
                $targetEntity = $mapping['targetEntity'];
            } else {
                $targetEntity = get_class($refObject);
            }
            $targetMap      = $this->getService()->getEntityManager()->getClassMetadata($targetEntity);
            $subGridService = $this->_serviceLocator->get('ModelService')->getService($targetEntity);

            if ($request->getPost('_search') == 'true') {
                $childRows = $this->_getRecords($request, $subGridService);
            } elseif ($refObject instanceof PersistentCollection) {
                $childRows = $refObject;
            } else {
                $childRows = new ArrayCollection();
                $childRows->add($refObject);
            }

            $subGrid = clone $this->getServiceLocator()->get('jqgrid');
            $subGrid->setGridIdentity(
                $targetEntity,
                $field,
                '_sub'
            );

            $subGrid->reorderColumns();
            $columns = $subGrid->setGridColumns()->getColumns();

            $total = count($childRows);

            $grid = array(
                'page'    => 1,
                'total'   => $total,
                'records' => $total,
                'rows'    => $this->_getGridRecords($childRows, $columns)
            );

            return $grid;
        }

        /**
         * Get records applying request filters
         *
         * @param Request $request
         * @param null    $service
         * @param null    $isTreeGrid
         *
         * @return \Traversable
         */
        protected function _getRecords(Request $request, $service = null, $isTreeGrid = null)
        {
            $service    = $service ? : $this->getService();
            $isTreeGrid = $isTreeGrid ? : $this->isTreeGrid;

            $filter     = $request->getPost('_search') == 'true' ? $this->_getFilterParams($request) : false;
            $treeFilter = array();

            if ($isTreeGrid) {
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

                } elseif (!$this->_config['tree_load_all']) {
                    $treeFilter = array(
                        'level' => 1
                    );
                }

            } else {
                $sort = $request->getPost('sidx') ? array('sidx' => $request->getPost('sidx'), 'sord' => $request->getPost('sord')) : false;
            }


            $adapter = new PaginatorAdapter($service, $filter, $sort, $this, $treeFilter);

            // Instantiate Zend_Paginator with the required data source adapter
            if (!$this->_paginator instanceof Paginator) {
                $this->_paginator = new Paginator($adapter);
                $this->_paginator->setDefaultItemCountPerPage($request->getPost('rows', $this->_defaultItemCountPerPage));
            }

            // Pass the current page number to paginator
            $this->_paginator->setCurrentPageNumber($request->getPost('page', 1));

            // Fetch a row of items from the adapter
            $rows = $this->_paginator->getCurrentItems();

            return $rows;
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
            $treeFilter = array();
            $sort       = array();
            $filter     = $request->getPost('_search') == 'true' ? $this->_getFilterParams($request) : false;

            if ($this->isTreeGrid) {
                $sort[] = array(
                    'sidx' => 'lft',
                    'sord' => 'ASC'
                );
                $node   = $request->getPost('nodeid', false);
                if ($node > 0) {
                    $n_lvl      = (integer)$request->getPost("n_level");
                    $n_lvl      = $n_lvl + 1;
                    $treeFilter = array(
                        'lft'    => (integer)$request->getPost("n_left"),
                        'rgt'    => (integer)$request->getPost("n_right"),
                        'level'  => $n_lvl,
                        'parent' => $node
                    );

                } elseif (!$this->_config['tree_load_all']) {
                    $treeFilter = array(
                        'level' => 1
                    );
                }

            }

            if ($idx = $request->getPost('sidx')) {
                $sort[] = array(
                    'sidx' => $idx,
                    'sord' => $request->getPost('sord')
                );
            } else {
                if ($f = $this->getSortname() and $o = $this->getSortorder()) {
                    $sort[] = array(
                        'sidx' => $f,
                        'sord' => $o
                    );
                }
            }

            $adapter = new PaginatorAdapter($this->getService(), $filter, $sort, $this, $treeFilter);

            // Instantiate Zend_Paginator with the required data source adapter
            if (!$this->_paginator instanceof Paginator) {
                $this->_paginator = new Paginator($adapter);
                if (!$rowNum = $request->getPost('rows')) {
                    $rowNum = $this->getRowNum() ? : $this->_defaultItemCountPerPage;
                }
                $this->_paginator->setDefaultItemCountPerPage($rowNum);
            }

            // Pass the current page number to paginator
            $this->_paginator->setCurrentPageNumber($request->getPost('page', 1));

            // Fetch a row of items from the adapter
            $rows = $this->_paginator->getCurrentItems();
            $this->reorderColumns();
            $columns = $this->setGridColumns()->getColumns();

            $grid = array(
                'page'    => $this->_paginator->getCurrentPageNumber(),
                'total'   => ceil($this->_paginator->getTotalItemCount() / $this->_paginator->getItemCountPerPage()),
                'records' => $this->_paginator->getTotalItemCount(),
                'rows'    => $this->_getGridRecords($rows, $columns)
            );


            return $grid;
        }

        protected function _getGridRecords($rows, $columns)
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
                        $type   = $this->getColumn($param)->getDbColumnType();
                        $method = 'set' . ucfirst($param);
                        $value  = ($value == 'null') ? null : $value;

                        if ($type == 'datetime' || $type == 'date') {
                            try {
                                //attempt to ensure date is in acceptable format for datetime object
                                $ts    = strtotime($value);
                                $ds    = $ts ? date(\DateTime::ISO8601, $ts) : null;
                                $value = $ds ? new \DateTime($ds) : null;
                                $entity->$method($value);
                            } catch (\Exception $e) {
                                $pass    = false;
                                $message = 'Wrong date format for column "' . $param . '"';
                                break;
                            }
                        } elseif (isset($mapping->associationMappings[$param])) {
                            $target = $mapping->associationMappings[$param]['targetEntity'];

                            if ($mapping->associationMappings[$param]['type'] == 8) {
                                if ($entity->$param) {
                                    $entity->$param->clear();
                                } else {
                                    $entity->$param = new ArrayCollection();
                                }
                                $value = explode(',', $value);
                                $value = array_unique(array_filter($value));

                                foreach ($value as $v) {
                                    if ($foreignEntity = $service->getEntityManager()->find($target, $v)) {
                                        $entity->$param->add($foreignEntity);
                                    }
                                }
                            } else {
                                $foreignEntity = $service->getEntityManager()->find($target, $value);
                                $entity->$method($foreignEntity);
                            }

                        } else {
                            $entity->$method($value);
                        }
                    }
                }

                if ($pass) {
                    try {
                        $entity  = $this->getService()->save($entity);
                        $id      = $entity->getId();
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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
            $this->_id = self::ID_PREFIX . preg_replace('/[^a-z0-9]/i', '', $id);

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
         * @return \SynergyDataGrid\Grid\JqGridFactory
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

        protected function _getRelatedList($entity, $id, $label)
        {
            $qb    = $this->getService()->getEntityManager()->createQueryBuilder();
            $items = $qb->select("e.$id, e.$label")
                ->from($entity, 'e')
                ->getQuery()
                ->execute(array(), AbstractQuery::HYDRATE_ARRAY);

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

        /**
         * @param int $entityId
         */
        public function setEntityId($entityId)
        {
            $this->_entityId = $entityId;

            return $this;
        }

        /**
         * @return int
         */
        public function getEntityId()
        {
            return $this->_entityId;
        }


    }