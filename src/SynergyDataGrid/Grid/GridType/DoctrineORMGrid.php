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

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\AbstractQuery;
    use Doctrine\ORM\Mapping\ClassMetadataInfo;
    use Doctrine\ORM\PersistentCollection;
    use Doctrine\ORM\QueryBuilder;
    use Doctrine\ORM\Tools\Pagination\Paginator;
    use SynergyDataGrid\Grid\Adapter\ORMQueryAdapter;
    use SynergyDataGrid\Util\ArrayUtils;
    use Zend\Http\PhpEnvironment\Request;
    use Zend\ServiceManager\ServiceLocatorInterface;
    use Zend\Stdlib\RequestInterface;

    final class DoctrineORMGrid extends BaseGrid
    {
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
         * Grid id
         *
         * @var string
         */
        private $_id;

        /**
         * Flag to indicate if columns have been set to prevent id being done twice
         *
         * @var bool
         */
        private $_columnsSet = false;
        /**
         * @var array
         */
        private $_subGridsAsGrid = array();

        /**
         * @var array
         */
        private $_subGrid = array();

        /**
         * @var \Zend\Paginator\Paginator
         */
        private $_paginator = null;

        /**
         * @var \Doctrine\ORM\QueryBuilder
         */
        private $_customQueryBuilder;
        /**
         * @var ORMQueryAdapter
         */
        private $_customAdapter;
        /**
         * Entity Manager
         *
         * @var \Doctrine\ORM\EntityManager
         */
        protected $_om;

        /**
         * @param      $request
         * @param bool $dataOnly
         *
         * @return \stdClass
         */
        public function getFirstDataAsLocal(Request $request, $dataOnly = false)
        {
            return $this->_createGridData($request, $dataOnly);
        }

        /**
         * Add subGrid configuration to the grid
         *
         * @param $model
         */
        public function getSubGridModel($subGridMap)
        {
            $subGridService = $this->_serviceLocator->get('ModelService')->getService($subGridMap['targetEntity']);
            $subGrid        = new SubGrid($subGridMap['targetEntity'], $subGridService);

            $params[] = $subGridMap['fieldName'];

            $mapping = $subGrid->getService()->getEntityManager()->getClassMetadata($subGridMap['targetEntity']);

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

            //$subGrid->width  = $width; //@todo get width from column model
            $subGrid->params = $params;


            return $subGrid;
        }

        public function setGridColumns($dataOnly = false)
        {
            if (!$this->_columnsSet) {
                $target = '';

                $utils = new ArrayUtils();

                $mapping         = $this->getService()->getEntityManager()->getClassMetadata($this->getEntity());
                $reflectionClass = new \ReflectionClass($this->getEntity());
                $defaultValues   = $reflectionClass->getDefaultProperties();

                $excludedColumns = array_merge(
                    array_values($this->_config['tree_grid_options']['treeReader']),
                    $this->_config['excluded_columns']
                );

                $excludedColumns = array_unique($excludedColumns);
                $count           = 10;

                foreach ($mapping->fieldMappings as $map) {
                    $adjustment = 0;
                    $fieldName  = $map['fieldName'];
                    $default    = isset($defaultValues[$fieldName]) ? $defaultValues[$fieldName] : null;

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
                        $adjustment                    = 10;

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
                    $colIndex              = $data['hidden'] ? ($count + 99) : ($count - $adjustment);
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

                    //if (!$dataOnly) {

                    if ($this->_isSubGridAsGrid($fieldName)) {
                        $this->_subGridsAsGrid[] = $this->createSubGridAsGrid($map);
                        $this->setSubGrid(true);
                        $data['hidden'] = true;
                    } else if (!$this->_hasSubGrid and $this->_isSubGrid($fieldName)) {
                        $this->_subGrid = $this->getSubGridModel($map);
                        $target         = $fieldName;
                        $data['hidden'] = true;

                        if (is_callable($this->_config['grid_url_generator'])) {
                            $subGridUrl = $this->_config['grid_url_generator'](
                                $this->getServiceLocator(),
                                $this->getEntity(),
                                $fieldName,
                                $map['targetEneity'],
                                self::DYNAMIC_URL_TYPE_SUBGRID
                            );

                            $this->_subGrid->setUrl($url);
                            $this->_subGrid->setEditurl($url);
                        } else {
                            $subGridUrl = $this->getSubGridUrl();
                        }
                        if (strpos($subGridUrl, '?') === false) {
                            $subGridUrl .= '?fieldName=' . $target;
                        } else {
                            $subGridUrl .= '&fieldName=' . $target;
                        }

                        $this->setSubGridModel(array($this->_subGrid));
                        $this->setSubGridUrl($subGridUrl);

                        $this->_hasSubGrid = true;
                        $this->setSubGrid(true);
                    }
                    // }

                    if (isset($this->_config['column_type_mapping'][$fieldName])) {
                        $type = $this->_config['column_type_mapping'][$fieldName];
                    } else {
                        $type = self::TYPE_SELECT;
                    }

                    $values = $this->_getRelatedList($map, $fieldName);

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


                    if ($map['type'] == ClassMetadataInfo::MANY_TO_MANY
                        or $map['type'] == ClassMetadataInfo::ONE_TO_MANY
                    ) {
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

                    $columnData[$count - $adjustment] = $data;
                    $count++;
                }

                ksort($columnData);
                $this->addColumns($columnData);

                // close form after edit
                if ($actionColumn = $this->getColumn('myac')) {
                    $actionColumn->mergeFormatoptions(array('editOptions' => array('closeAfterEdit' => true)));
                }

                $this->_columnsSet = true;
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

                $fieldName = isset($options['fieldName']) ? $options['fieldName'] : null;

                $operation = $request->getPost('oper');

                if ($operation == 'del') {
                    $data = $this->delete($request);
                } elseif ($operation == 'edit' || $operation == 'add') {
                    //$this->setGridColumns(true);
                    $data = $this->edit($request);
                } else {
                    if ($request->getPost(self::GRID_IDENTIFIER) == $this->getId()) {
                        $data = $this->_createGridData($request, $subGridid, $options['fieldName']);
                    } elseif ($subGridid) {
                        return $this->createSubGridData($request, $subGridid, $fieldName);
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
        public function createSubGridData(Request $request, $id, $field)
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
                $childRows = $this->getPaginator($request, $subGridService)->getIterator();
            } elseif ($refObject instanceof PersistentCollection) {
                $childRows = $refObject;
            } else {
                $childRows = new ArrayCollection();
                $childRows->add($refObject);
            }

            $subGrid = $this->getServiceLocator()
                ->setShared('jqgrid', false)
                ->get('jqgrid');
            $subGrid->setGridIdentity(
                $targetEntity,
                $field
            );

            $subGrid->reorderColumns();
            $columns = $subGrid->setGridColumns()->getColumns();

            $total = count($childRows);

            $grid = array(
                'page'    => 1,
                'total'   => $total,
                'records' => $total,
                'rows'    => $this->_formatGridData($childRows, $columns)
            );

            return $grid;
        }

        /**
         * Create grid data based on request and using pagination
         *
         * @param \Zend\Http\Request $request
         *
         * @return \stdClass
         */
        protected function _createGridData(Request $request, $dataOnly = true)
        {
            $page      = $request->getPost('page', 1);
            $paginator = $this->getPaginator($request);
            $rows      = $paginator->getIterator();

            $this->reorderColumns();
            $columns = $this->setGridColumns($dataOnly)->getColumns();

            $total  = $paginator->count();
            $rowNum = $paginator->getQuery()->getMaxResults();

            $grid = array(
                'page'    => $page,
                'total'   => ceil($total / $rowNum),
                'records' => $total,
                'rows'    => $this->_formatGridData($rows, $columns)
            );

            return $grid;
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

                        $method = 'set' . ucfirst($param);
                        $value  = ($value == 'null') ? null : $value;

                        if (isset($mapping->associationMappings[$param])) {
                            $target = $mapping->associationMappings[$param]['targetEntity'];

                            if ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::ONE_TO_MANY) {
                                $message = "OneToMany updates not supported: '{$param}' was not updated";
                            } elseif ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::MANY_TO_MANY) {
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
                                    } else {
                                        $pass    = false;
                                        $message = "Unable to update join table: {$target} " . $param . '"';
                                    }
                                }
                            } else {
                                if ($foreignEntity = $service->getEntityManager()->find($target, $value)) {
                                    $entity->$method($foreignEntity);
                                } else {
                                    $pass    = false;
                                    $message = "Unable to update join table: {$target} field" . $param . '"';
                                }
                            }

                        } else {
                            $type = $mapping->fieldMappings[$param]['type'];
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
                            } else {
                                $entity->$method($value);
                            }
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


        protected function _getRelatedList($map, $fieldName)
        {

            if (isset($this->_config['association_mapping_callback'][$fieldName])
                and  is_callable($this->_config['association_mapping_callback'][$fieldName])
            ) {
                $function = $this->_config['association_mapping_callback'][$fieldName];
                $values   = $function($this->_serviceLocator, $map['targetEntity'], $map['mappedBy']);
            } elseif (is_callable($this->_config['association_mapping_callback']['__default__'])) {
                $function = $this->_config['association_mapping_callback']['__default__'];
                $values   = $function($this->_serviceLocator, $map['targetEntity'], $map['mappedBy']);
            } else {
                $idField    = $this->_config['default_association_mapping_id'];
                $labelField = $this->_config['default_association_mapping_label'];

                $qb   = $this->getService()->getEntityManager()->createQueryBuilder();
                $list = $qb->select("e.$idField, e.$labelField")
                    ->from($map['targetEntity'], 'e')
                    ->getQuery()
                    ->execute(array(), AbstractQuery::HYDRATE_ARRAY);

                $values = array(':select');
                foreach ($list as $item) {
                    $values[] = $item[$idField] . ':' . $item[$labelField];
                }
            }

            return $values;
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
            $this->setService($this->getServiceLocator());
            $this->setObjectManager($this->getServiceLocator());

            $config = $this->getConfig();
            $utils  = new ArrayUtils();

            if ($displayTree) {
                $mapping = $this->getEntityManager()->getClassMetadata($this->getEntity());
                if ('Gedmo\Tree\Entity\Repository\NestedTreeRepository' == $mapping->customRepositoryClassName) {
                    $this->setTreeGrid(true);
                    $this->isTreeGrid = true;

                    //Set tree grid options
                    $treeOptions               = isset($config ['tree_grid_options']) ? $config ['tree_grid_options'] : array();
                    $treeOptions['rownumbers'] = false;
                    $config ['grid_options']   = $utils->arrayMergeRecursiveCustom($config ['grid_options'], $treeOptions);

                }
            }

            /**
             * Merge grid specific configurations
             */
            if (isset($config [$gridId])) {
                $config ['grid_options'] = $utils->arrayMergeRecursiveCustom($config ['grid_options'], $config [$gridId]);
            }

            $this->setConfig($config);

            return $this;
        }

        public function createSubGridAsGrid($subGridMap)
        {
            /** @var $subGrid \SynergyDataGrid\Grid\GridType\BaseGrid */
            $subGrid = $this->getServiceLocator()->setShared('jqgrid', false)->get('jqgrid');

            //$config = $this->getServiceLocator()->get('Config');
            //$subGrid  = new DoctrineORMGrid($config['jqgrid'], $this->getServiceLocator());


            $subGrid->setUrl($this->getUrl());
            $subGrid->setIsDetailGrid(true);
            $subGrid->setMasterGridId($this->getId());
            $subGrid->setCaption($subGridMap['fieldName']);
            $subGrid->getJsCode()
                ->setContainerClass('subgrid-data')
                ->setPadding(20);

            //disable hide grid
            $subGrid->setHidegrid(false);


            $subGrid->setGridIdentity(
                $subGridMap['targetEntity'],
                $subGridMap['fieldName']
            );

            if (is_callable($this->_config['grid_url_generator'])) {
                $url = $this->_config['grid_url_generator']($subGrid->getServiceLocator(), $this->getEntity(),
                    $subGridMap['fieldName'], $subGridMap['targetEntity'], self::DYNAMIC_URL_TYPE_ROW_EXPAND);

                //subgrid edit url
                $editUrl = $this->_config['grid_url_generator']($subGrid->getServiceLocator(), $this->getEntity(),
                    $subGridMap['fieldName'], $subGridMap['targetEntity'], self::DYNAMIC_URL_TYPE_SUBGRID);

                $subGrid->setUrl($url);
                $subGrid->setEditurl($editUrl);
            } else {
                $subGrid->setUrl($this->getUrl());
            }

            return $subGrid;
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

        /**
         * @return \Doctrine\ORM\EntityManager
         */
        public function getObjectManager()
        {
            return $this->_om;
        }

        /**
         * Get service model for current grid
         *
         * @return \SynergyDataGrid\Model\BaseService
         */
        public function getService()
        {
            return $this->_service;
        }

        /**
         * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
         *
         * @return $this
         */
        public function setService(ServiceLocatorInterface $serviceManager)
        {
            $this->_service = $serviceManager->get('ModelService')->getService($this->getEntity());

            return $this;
        }


        public function setObjectManager(ServiceLocatorInterface $serviceManager)
        {
            $this->_om = $this->_service->getEntityManager();

            return $this;
        }

        public function getEntityManager()
        {
            return $this->getObjectManager();
        }

        /**
         * @param boolean $columnsSet
         */
        public function setColumnsSet($columnsSet)
        {
            $this->_columnsSet = $columnsSet;

            return $this;
        }

        /**
         * @return boolean
         */
        public function getColumnsSet()
        {
            return $this->_columnsSet;
        }


        /**
         * @return \SynergyDataGrid\Grid\GridType\SubGrid
         */
        public function getSubGrid()
        {
            return $this->_subGrid;
        }

        /**
         * Returns list of subgrids as grids
         *
         * @return array
         */
        public function getSubGridsAsGrid()
        {
            return $this->_subGridsAsGrid;
        }

        /**
         * @param \Doctrine\ORM\QueryBuilder $customQueryBuilder
         */
        public function setCustomQueryBuilder(QueryBuilder $customQueryBuilder)
        {
            $this->_customQueryBuilder = $customQueryBuilder;

            return $this;
        }

        /**
         * @return \Doctrine\ORM\QueryBuilder
         */
        public function getCustomQueryBuilder()
        {
            return $this->_customQueryBuilder;
        }

        /**
         * @param       $perPage
         * @param int   $page
         * @param array $filter
         * @param array $sort
         * @param array $treeFilter
         *
         * @return Paginator
         */
        protected function getPaginator($request, $service = null)
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
                    $treeFilter = array('level' => 1);
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

            if (!$perPage = $request->getPost('rows')) {
                $perPage = $this->getRowNum() ? : $this->_defaultItemCountPerPage;
            }

            $page    = $request->getPost('page', 1);
            $offset  = ($page - 1) * $perPage;
            $service = $service ? : $this->getService();
            $adapter = new ORMQueryAdapter($this, $service, $filter, $sort, $treeFilter);
            $query   = $adapter->createQuery($offset, $perPage);

            $paginator = new Paginator($query);

            return $paginator;
        }

        /**
         * @param ORMQueryAdapter $paginatorAdapter
         *
         * @return $this
         */
        public function setCustomAdapter(ORMQueryAdapter $paginatorAdapter)
        {
            $this->_customAdapter = $paginatorAdapter;

            return $this;
        }

        /**
         * @return ORMQueryAdapter
         */
        public function getCustomAdapter()
        {
            return $this->_customAdapter;
        }
    }