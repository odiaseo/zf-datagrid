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
use SynergyCommon\Paginator\Adapter\DoctrinePaginator;
use SynergyDataGrid\Grid\Adapter\ORMQueryAdapter;
use SynergyDataGrid\Helper\BaseConfigHelper;
use SynergyDataGrid\Util\ArrayUtils;
use Zend\Http\PhpEnvironment\Request;
use Zend\Json\Expr;
use Zend\Stdlib\RequestInterface;

final class DoctrineORMGrid
    extends BaseGrid
{
    /**
     * Response
     *
     * @var null
     */
    public $return = null;
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
     * @var array
     */
    private $_subGridsAsGrid = array();

    /**
     * @var array
     */
    private $_subGrid = array();
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
     * @param $subGridMap
     *
     * @return SubGrid
     */
    public function getSubGridModel($subGridMap)
    {
        $names = $width = array();
        /** @var $model \SynergyDataGrid\Model\BaseModel */
        $model = $this->getModel($subGridMap['targetEntity']);

        $subGrid = new SubGrid($subGridMap['targetEntity'], $model, $this->getEntityManager());

        $mapping = $subGrid->getEntityManager()->getClassMetadata($subGridMap['targetEntity']);

        $params[] = $subGridMap['fieldName'];
        $id       = current($mapping->identifier);

        foreach ($mapping->fieldMappings as $map) {
            if (in_array($map['fieldName'], $this->_config['excluded_columns'])) {
                continue;
            }
            $names[] = $map['fieldName'];
            $width[] = ($id == $map['fieldName']) ? '60px' : '100px';
        }

        foreach ($mapping->associationMappings as $map) {
            if (in_array($map['fieldName'], $this->_config['excluded_columns'])) {
                continue;
            }
            $names[] = $map['fieldName'];
            $width[] = '100px';
        }

        $subGrid->name = $names;

        $subGrid->width  = $width; //@todo get width from column model
        $subGrid->params = $params;


        return $subGrid;
    }

    /**
     * Set grid columns by mapping entity attributes to grid columns
     *
     * @param bool $dataOnly
     *
     * @return $this|mixed
     */
    public function setGridColumns($dataOnly = false)
    {
        $adjustment = 0;

        if (!$this->_columnsSet) {

            $utils = new ArrayUtils();

            $mapping         = $this->getEntityManager()->getClassMetadata($this->getEntity());
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
                        'NullIfEmpty'     => (isset($map['nullable']) && $map["nullable"])
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
                        $data['searchoptions']['sopt'] = $this->getSearchOptions('cn');
                        break;
                }


                if ($map['columnName'] == 'id') {
                    $data['editable']              = false;
                    $data['key']                   = true;
                    $data['formoptions']['rowpos'] = 1;
                    $data['formoptions']['colpos'] = 1;
                    $data['searchoptions']['sopt'] = $this->getSearchOptions('bw');
                    $adjustment                    = 10;

                    if ($this->isTreeGrid) {
                        $this->_config['column_model'][$fieldName]['width']
                            = $this->_config['tree_grid_options']['ExpandColumnWidth'];
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

                if (empty($data['searchoptions']['sopt'])) {
                    $data['searchoptions']['sopt'] = $this->getSearchOptions();
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

                if ($this->_isSubGridAsGrid($fieldName)) {
                    $this->_subGridsAsGrid[] = $this->createSubGridAsGrid($map);
                    $this->setSubGrid(true);
                    continue;
                } else {
                    if (!$this->_hasSubGrid and $this->_isSubGrid($fieldName)) {
                        $index = count($this->_subGrid);
                        /** @var $subGrid \SynergyDataGrid\Grid\GridType\BaseGrid */
                        $subGrid   = $this->getSubGridModel($map);
                        $target    = $fieldName;
                        $generator = null;


                        if (is_string($this->_config['grid_url_generator'])) {
                            $generator  = $this->_serviceLocator->get($this->_config['grid_url_generator']);
                            $subGridUrl = $this->getSubGridUrl();

                            if ($generator instanceof BaseConfigHelper) {
                                $subGridUrl = $generator->execute(
                                    array(
                                         $this->getEntity(),
                                         $fieldName,
                                         $map['targetEntity'],
                                         self::DYNAMIC_URL_TYPE_SUBGRID
                                    )
                                );

                                $editUrl = $generator->execute(
                                    array(
                                         $map['targetEntity'],
                                         $fieldName,
                                         null,
                                         self::DYNAMIC_URL_TYPE_GRID
                                    )
                                );

                                $subGrid->setUrl($subGridUrl);
                                $subGrid->setEditurl($editUrl);
                            }
                        } elseif (is_callable($this->_config['grid_url_generator'])) {
                            $generator = $this->_config['grid_url_generator'];

                            $subGridUrl = $generator(
                                $this->getServiceLocator(),
                                $this->getEntity(),
                                $fieldName,
                                $map['targetEntity'],
                                self::DYNAMIC_URL_TYPE_SUBGRID
                            );

                            $editUrl = $generator(
                                $this->getServiceLocator(),
                                $map['targetEntity'],
                                $fieldName,
                                null,
                                self::DYNAMIC_URL_TYPE_GRID
                            );


                            $subGrid->setUrl($subGridUrl);
                            $subGrid->setEditurl($editUrl);
                        } else {
                            $subGridUrl = $this->getSubGridUrl();
                        }


                        if (strpos($subGridUrl, '?') === false) {
                            $subGridUrl .= '?fieldName=' . $target;
                        } else {
                            $subGridUrl .= '&fieldName=' . $target;
                        }


                        $this->setSubGridModel(array($subGrid));
                        $this->setSubGridUrl($subGridUrl);

                        $this->_hasSubGrid = true;
                        $this->setSubGrid(true);
                        $this->_subGrid[$index] = $subGrid;
                    }
                }

                if (isset($this->_config['column_type_mapping'][$fieldName])) {
                    $type = $this->_config['column_type_mapping'][$fieldName];
                } else {
                    $type = self::TYPE_SELECT;
                }

                $values = $this->_getRelatedList($map, $fieldName);

                if (is_array($values)) {
                    $values = implode(';', $values);
                }

                $data = array_merge_recursive(
                    array(
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
                    ),
                    $data
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
     * Completely render current grid object or just send AJAX response
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return array|\stdClass|string
     */
    public function prepareGridData(RequestInterface $request = null, $options = array())
    {
        /** @var $request \Zend\Http\PhpEnvironment\Request */
        $data = array(
            'error'   => false,
            'message' => ''
        );

        try {
            if (!$request) {
                $serviceManager = $this->getModel()->getServiceManager();
                $request        = $serviceManager->get('request');
            }
            if (!$this->getUrl()) {
                $this->setUrl($request->getRequestUri());
            }


            $str = parse_url($request->getRequestUri(), PHP_URL_QUERY);
            parse_str($str, $queryParams);

            if (isset($queryParams[self::SUB_GRID_IDENTIFIER])) {
                $subGridid = $queryParams[self::SUB_GRID_IDENTIFIER];
            } else {
                $subGridid = $request->getPost(self::SUB_GRID_IDENTIFIER);
            }

            $fieldName = isset($options['fieldName']) ? $options['fieldName'] : null;
            $operation = $request->getPost('oper');

            if ($subGridid) {
                if ($operation == 'del') {
                    $data = $this->deleteSubgridRow($request, $options['fieldName']);
                } elseif ($operation == 'edit' || $operation == 'add') {
                    $data = $this->editSubGrid($request, $subGridid, $options['fieldName']);
                } else {
                    return $this->createSubGridData($request, $subGridid, $fieldName);
                }
            } else {
                if ($operation == 'del') {
                    $data = $this->delete($request);
                } elseif ($operation == 'edit' || $operation == 'add') {
                    $data = $this->edit($request);
                } elseif ($request->getPost(self::GRID_IDENTIFIER) == $this->getId()) {
                    $data = $this->_createGridData($request);
                }
            }
            ;

        } catch (\Exception $e) {
            $data = array(
                'error'   => true,
                'message' => $e->getMessage()
            );
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
        /** @var $model \SynergyDataGrid\Model\BaseModel */
        $model = $this->getModel($this->_entity);

        $row       = $model->getEntityManager()->getRepository($this->_entity)->find($id);
        $method    = 'get' . ucfirst($field);
        $refObject = $row->$method();

        if ($refObject instanceof PersistentCollection) {
            $mapping      = $refObject->getMapping();
            $targetEntity = $mapping['targetEntity'];
        } else {
            $targetEntity = get_class($refObject);
        }

        $subGridModel = $model = $this->getModel($targetEntity);
        $parentMeta   = $model->getEntityManager()->getClassMetadata($this->_entity);

        //$model->setEntityManager($this->getEntityManager());
        //$model->setModelService($this->_entity);

        if ($mappedBy = $parentMeta->associationMappings[$field]['mappedBy']) {
            $subGridFilter = array($mappedBy => $id);
            $paginator     = $this->getPaginator($request, $subGridModel, $subGridFilter);
            $childRows     = $paginator->getIterator();
            $total         = $paginator->count();
            $rowNum        = $paginator->getQuery()->getMaxResults();
        } else {
            $childRows = $refObject;
            $total     = count($refObject);
            $rowNum    = $request->getPost('rows');
        }

        if (!$childRows) {
            $childRows = new ArrayCollection();
        }

        /** @var $subGrid \SynergyDataGrid\Grid\GridType\BaseGrid */
        $subGrid = $this->getServiceLocator()->get('jqgrid');
        $subGrid->setGridIdentity(
            $targetEntity,
            $field
        );

        $subGrid->reorderColumns();
        $columns = $subGrid->setGridColumns()->getColumns();

        $grid = array(
            'page'    => $request->getPost('page', 1),
            'total'   => ceil($total / $rowNum),
            'records' => $total,
            'rows'    => $this->formatGridData($childRows, $columns)
        );

        return $grid;
    }

    /**
     * Create grid data based on request and using pagination
     *
     * @param Request $request
     * @param bool    $dataOnly
     *
     * @return array|\stdClass
     *
     */
    protected function _createGridData(Request $request, $dataOnly = true)
    {
        $page      = $request->getPost('page', 1);
        $paginator = $this->getPaginator($request);

        $rows = $paginator->getIterator();

        $this->reorderColumns();
        $columns = $this->setGridColumns($dataOnly)->getColumns();

        $total  = $paginator->count();
        $rowNum = $paginator->getQuery()->getMaxResults();

        $grid = array(
            'page'    => $page,
            'total'   => ceil($total / $rowNum),
            'records' => $total,
            'rows'    => $this->formatGridData($rows, $columns)
        );

        return $grid;
    }

    /**
     * Delete record based on passed id and return result
     *
     * @param Request $request
     *
     * @return array|string
     *
     */
    public function delete(Request $request)
    {
        $id      = $request->getPost('id');
        $retv    = false;
        $message = 'Unable to delete record.';
        if ($id) {
            $retv = $this->getModel()->remove($id);
            if ($retv) {
                $message = '';
            }
        }

        return array('success' => $retv, 'message' => $message);
    }

    /**
     * Delete record based on passed id and return result
     *
     * @param Request $request
     * @param         $fieldName
     *
     * @return array
     */
    public function deleteSubgridRow(Request $request, $fieldName)
    {
        $id = $request->getPost('id');

        $mapping = $this->getEntityManager()->getClassMetadata($this->getEntity());
        $target  = $mapping->associationMappings[$fieldName]['targetEntity'];

        $model = $this->getModel($target);

        try {
            $retv    = $model->remove($id);
            $message = sprintf('Row #%d successfully deleted', $id);
        } catch (\Exception $e) {
            $message = 'Unable to delete record. ' . $e->getMessage();
            $retv    = false;
        }

        return array('error' => $retv ? false : true, 'message' => $message);
    }

    /**
     * @param      $request
     * @param null $entity
     * @param null $model
     *
     * @return \Doctrine\ORM\Mapping\Entity|null
     */
    protected function createEntity($request, $entity = null, $model = null)
    {
        /** @var $request \Zend\Http\PhpEnvironment\Request */
        $params      = $request->getPost();
        $pass        = true;
        $message     = '';
        $model       = $model ? : $this->getModel();
        $entityClass = $model->getEntityClass();


        if (!$entity) {
            if (array_key_exists('id', $params) && $params['id'] && $params['id'] != 'new_row'
                && $params['id'] != '_empty'
            ) {
                $entity = $model->findObject($params['id']);
            } else {
                $entity = new $entityClass();
            }
        }

        if ($entity) {
            unset($params['oper']);
            unset($params['id']);

            $mapping = $model->getEntityManager()->getClassMetadata($entityClass);

            foreach ($params as $param => $value) {
                if (array_key_exists($param, $mapping->fieldMappings) or array_key_exists(
                    $param, $mapping->associationMappings
                )
                ) {

                    $method = 'set' . ucfirst($param);
                    $value  = ($value == 'null' or empty($value)) ? null : $value;

                    if (isset($mapping->associationMappings[$param])) {
                        $target = $mapping->associationMappings[$param]['targetEntity'];

                        if ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::ONE_TO_MANY) {
                            $message = "OneToMany updates not supported: '{$param}' was not updated";
                        } elseif ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::MANY_TO_MANY) {

                            /** @var $entityParam \Doctrine\Common\Collections\ARrayCollection */
                            $entityParam = $entity->$param;
                            if ($entityParam) {
                                $entityParam->clear();
                            } else {
                                $entityParam = new ArrayCollection();
                            }
                            $value = explode(',', $value);
                            $value = array_unique(array_filter($value));

                            foreach ($value as $v) {
                                if ($foreignEntity = $this->getEntityManager()->find($target, $v)) {
                                    $entityParam->add($foreignEntity);
                                } else {
                                    $pass    = false;
                                    $message = "Unable to update join table: {$target} " . $param . '"';
                                }
                            }
                            $this->$param = $entityParam;
                        } elseif ($value) {
                            if ($foreignEntity = $this->getEntityManager()->find($target, $value)) {
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
        }

        $this->return = array(
            'error'   => !$pass,
            'message' => $message
        );

        return $entity;
    }

    /**
     * @param $request
     * @param $id
     * @param $field
     *
     * @return array
     */
    public function editSubGrid($request, $id, $field)
    {
        /** @var $request \Zend\Http\PhpEnvironment\Request */
        $pass    = true;
        $row     = null;
        $mapping = $this->getEntityManager()->getClassMetadata($this->getEntity());
        $target  = $mapping->associationMappings[$field]['targetEntity'];

        $subGridId = $request->getPost('id', null);

        if (is_numeric($subGridId)) {
            $entity = $this->getEntityManager()->getRepository($target)->find($subGridId);
        } else {
            /** @var $row \SynergyCommon\Entity\AbstractEntity */
            $row    = $this->getEntityManager()->getRepository($this->_entity)->find($id);
            $entity = new $target;
        }

        $model = $this->getModel($target);
        /** @var $entity  \SynergyCommon\Entity\AbstractEntity */
        if ($entity = $this->createEntity($request, $entity, $model)) {
            try {
                if (is_numeric($subGridId)) {
                    $model->save($entity);
                    $id = $entity->getId();
                } else {
                    $row->$field->add($entity);
                    $model->save($row);
                    $id = $row->getId();
                }
                $message = '';
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $pass    = false;
            }

            return array('error' => !$pass, 'message' => $message, 'id' => $id);
        } else {
            return $this->return;
        }

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
        $pass    = true;
        $message = '';
        $id      = '';

        if ($entity = $this->createEntity($request)) {
            try {
                $entity  = $this->getModel()->save($entity);
                $id      = $entity->getId();
                $message = '';
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $pass    = false;
            }
        }

        return array('error' => !$pass, 'message' => $message, 'id' => $id);
    }

    /**
     * @param $map
     * @param $fieldName
     *        if (is_string($this->_config['grid_url_generator'])) {
    $generator = $this->_serviceLocator->get($this->_config['grid_url_generator']);
     *
     * @return array|mixed
     */

    protected function _getRelatedList($map, $fieldName)
    {
        $function = null;
        if (isset($this->_config['association_mapping_callback'][$fieldName])) {
            $function = $this->_config['association_mapping_callback'][$fieldName];
        } elseif (isset($this->_config['association_mapping_callback']['__default__'])) {
            $function = $this->_config['association_mapping_callback']['__default__'];
        }

        if (is_string($function)) {
            /** @var $helper \SynergyDataGrid\Helper\BaseConfigHelper */
            $helper = $this->_serviceLocator->get($function);
            $values = $helper->execute(
                array(
                     $map['targetEntity'],
                     $map['mappedBy']
                )
            );
        } elseif (is_callable($function)) {
            $values = $function($this->_serviceLocator, $map['targetEntity'], $map['mappedBy']);
        } else {
            $idField    = $this->_config['default_association_mapping_id'];
            $labelField = $this->_config['default_association_mapping_label'];

            $qb   = $this->getEntityManager()->createQueryBuilder();
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
     * @param        $entityClassName
     * @param string $gridId
     * @param null   $idSuffix When display multiple grids on the same entity, use to make the gridId unique
     * @param bool   $displayTree
     *
     * @return $this
     */
    public function setGridIdentity($entityClassName, $gridId = '', $idSuffix = null, $displayTree = true)
    {
        $this->setEntityId($gridId);
        $this->setId($gridId . $idSuffix);
        $this->setEntity($entityClassName);
        $this->setService($this->getServiceLocator());

        $config = $this->getConfig();
        $utils  = new ArrayUtils();

        if ($displayTree) {
            $mapping = $this->getEntityManager()->getClassMetadata($this->getEntity());
            if ('Gedmo\Tree\Entity\Repository\NestedTreeRepository' == $mapping->customRepositoryClassName) {
                $this->setTreeGrid(true);
                $this->isTreeGrid = true;

                //Set tree grid options
                $treeOptions               = isset($config ['tree_grid_options']) ? $config ['tree_grid_options']
                    : array();
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

        /**
         * Set default crud route
         */
        /** @var $service \SynergyDataGrid\Service\GridService' */
        $service   = $this->getServiceLocator()->get('synergy\service\grid');
        $entityKey = $service->getEntityKeyFromClassname($entityClassName);
        $crudUrl   = $this->getCrudUrl($entityKey);

        if (!empty($config['api_domain'])) {
            $crudUrl = rtrim($config['api_domain'], '/') . '/' . ltrim($crudUrl, '/');
            //disable local data
            $config ['first_data_as_local'] = false;
        }

        $this->setUrl($crudUrl);
        $this->setSubGridUrl($crudUrl);

        /**
         * Set grid caption
         */
        if (!$caption = $this->getCaption()) {
            $this->setCaption(ucwords(str_replace('-', ' ', $entityKey)));
        }

        if (!empty($config ['grid_options']['onSelectRow']) && is_string($config ['grid_options']['onSelectRow'])) {
            $config ['grid_options']['onSelectRow'] = new Expr($config ['grid_options']['onSelectRow']);
        }
        $this->setConfig($config);

        return $this;
    }


    public function getCrudUrl($entityKey)
    {
        $urlHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('url');

        return $urlHelper('synergydatagrid', array('entity' => $entityKey));
    }

    public function createSubGridAsGrid($subGridMap)
    {
        /** @var $subGrid \SynergyDataGrid\Grid\GridType\BaseGrid */
        $subGrid = $this->getServiceLocator()->setShared('jqgrid', false)->get('jqgrid');

        $subGrid->setIsDetailGrid(true);
        $subGrid->setMasterGridId($this->getId());
        $subGrid->getJsCode()
            ->setContainerClass(SubGrid::WRAPPER_CLASS)
            ->setPadding(SubGrid::GRID_PADDING);

        //disable hide grid
        $subGrid->setHidegrid(false);

        $subGrid->setGridIdentity(
            $subGridMap['targetEntity'],
            $subGridMap['fieldName']
        );
        if (is_string($this->_config['grid_url_generator'])) {
            $generator = $this->_serviceLocator->get($this->_config['grid_url_generator']);

            if ($generator instanceof BaseConfigHelper) {
                $url = $generator->execute(
                    array(
                         $this->getEntity(),
                         $subGridMap['fieldName'],
                         $subGridMap['targetEntity'],
                         self::DYNAMIC_URL_TYPE_ROW_EXPAND
                    )
                );

                $editUrl = $generator->execute(
                    array(
                         $this->getEntity(),
                         $subGridMap['fieldName'],
                         $subGridMap['targetEntity'],
                         self::DYNAMIC_URL_TYPE_SUBGRID
                    )
                );

                $subGrid->setUrl($url);
                $subGrid->setEditurl($editUrl);
            }
        } elseif (is_callable($this->_config['grid_url_generator'])) {
            $url = $this->_config['grid_url_generator'](
                $subGrid->getServiceLocator(), $this->getEntity(),
                $subGridMap['fieldName'], $subGridMap['targetEntity'], self::DYNAMIC_URL_TYPE_ROW_EXPAND
            );

            //subgrid edit url
            $editUrl = $this->_config['grid_url_generator'](
                $subGrid->getServiceLocator(), $this->getEntity(),
                $subGridMap['fieldName'], $subGridMap['targetEntity'], self::DYNAMIC_URL_TYPE_SUBGRID
            );

            $subGrid->setUrl($url);
            $subGrid->setEditurl($editUrl);
        }

        return $subGrid;
    }

    /**
     * @param $entityId
     *
     * @return $this
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
     * @param $entity
     *
     * @return $this
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


    public function getModel($entityClass = '')
    {
        $entityClass = $entityClass ? : $this->getEntity();
        /** @var $model \SynergyDataGrid\Model\BaseModel */
        $model = $this->_serviceLocator->get('synergydatagrid\model');
        $em    = $this->getEntityManager();

        $model->setEntityManager($em);
        $model->setEntityClass($entityClass);
        $model->setRepository($em->getRepository($entityClass));
        $model->setClassMetadata($em->getClassMetadata($entityClass));
        $model->setModelService($entityClass);

        $model->setAlias('e');

        return $model;
    }

    public function setObjectManager($serviceManager)
    {
        $this->_om = $serviceManager;

        return $this;
    }

    public function getEntityManager()
    {
        return $this->getObjectManager();
    }

    /**
     * @param $columnsSet
     *
     * @return $this
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
     * @param QueryBuilder $customQueryBuilder
     *
     * @return $this
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
     * @param       $request \Zend\Http\PhpEnvironment\Request
     * @param null  $model
     * @param array $subGridFilter
     *
     * @return Paginator|\Zend\Paginator\Paginator
     */
    protected function getPaginator($request, $model = null, $subGridFilter = array())
    {
        $treeFilter = array();
        $sort       = array();
        $filter     = $request->getPost('_search') == 'true' ? $this->_getFilterParams($request) : false;

        if ($this->isTreeGrid) {
            $sort['lft'] = array(
                'sidx' => 'lft',
                'sord' => 'ASC'
            );
            $node        = $request->getPost('nodeid', false);
            if (false and $node > 0) { //@todo fix bug
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

        if ($idx = $request->getPost('sidx') and !isset($sort[$idx])) {
            $sort[$idx] = array(
                'sidx' => $idx,
                'sord' => $request->getPost('sord')
            );
        } else {
            if ($f = $this->getSortname() and $o = $this->getSortorder()) {
                $sort[$f] = array(
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
        $model   = $model ? : $this->getModel();
        $adapter = new ORMQueryAdapter($this, $model, $filter, $sort, $treeFilter);
        $query   = $adapter->createQuery($offset, $perPage);

        //filter result if subgrid
        if ($subGridFilter) {
            foreach ($subGridFilter as $field => $value) {
                $query->andWhere(
                    $query->expr()->eq(
                        $model->getAlias() . '.' . $field,
                        $value
                    )
                );
            }
        }

        $paginator = new DoctrinePaginator($query);

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

    /**
     * Get search options and ensure the default is the first item
     *
     * @param string $default
     *
     * @return array
     */
    public function getSearchOptions($default = 'eq')
    {
        $default = strtolower($default);
        $options = $this->_expression;
        if (array_key_exists($default, $options)) {
            unset($options[$default]);
            $values = array_keys($options);
            array_unshift($values, $default);
        } else {
            $values = array_keys($this->_expression);
        }

        return $values;
    }

}
