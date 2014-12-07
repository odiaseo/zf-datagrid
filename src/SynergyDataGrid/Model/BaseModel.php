<?php
namespace SynergyDataGrid\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SynergyCommon\Paginator\Adapter\DoctrinePaginator;
use SynergyDataGrid\Model\Config\ModelOptions;

/**
 * This file is part of the Synergy package.
 * (c) Pele Odiase <info@rhemastudio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

/**
 * Class to handle base functionality to work with Doctrine Models
 *
 * @author  Pele Odiase
 * @package mvcgrid
 */
class BaseModel
{

    const PER_PAGE = 15;
    /**
     * Mapping human-readable constants to DQL operatores
     *
     * @var array
     */
    protected $_operator
        = array(
            'EQUAL'                 => '= ?',
            'NOT_EQUAL'             => '!= ?',
            'LESS_THAN'             => '< ?',
            'LESS_THAN_OR_EQUAL'    => '<= ?',
            'GREATER_THAN'          => '> ?',
            'GREATER_THAN_OR_EQUAL' => '>= ?',
            'BEGIN_WITH'            => 'LIKE ?',
            'NOT_BEGIN_WITH'        => 'NOT LIKE ?',
            'END_WITH'              => 'LIKE ?',
            'NOT_END_WITH'          => 'NOT LIKE ?',
            'CONTAIN'               => 'LIKE ?',
            'NOT_CONTAIN'           => 'NOT LIKE ?'
        );
    /**
     * Error handler
     *
     * @var \SynergyCommon\Util\ErrorHandler
     */
    protected $_logger;

    protected $_orm_key = 'doctrine.entitymanager.orm_default';
    /**
     * @var \SynergyDataGrid\Model\Config\ModelOptions
     */
    protected $_options;
    /**
     * Doctrine Query Builder
     *
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $_qb;

    protected $_customQueryBuilder;
    /**
     * Service model, attached to grid
     *
     * @var int
     */
    protected $_service;
    /**
     * Entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;
    /**
     * Entity repository
     *
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $_repository;
    /**
     * Entity class name
     *
     * @var string
     */
    protected $_entityClass;
    /**
     * Model alias
     *
     * @var string
     */
    protected $_alias = 'e';
    /**
     * Class metadata
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $_classMetadata;
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $_sm;

    public function __construct(ModelOptions $options = null)
    {
        $this->_options = $options;
    }

    public function setModelService($entityClass)
    {
        $this->setEntityClass($entityClass);
        $this->setRepository($this->getEntityManager()->getRepository($entityClass));
        $this->setClassMetadata($this->getEntityManager()->getClassMetadata($entityClass));

        $alias = 'e';
        $this->setAlias($alias);

        return $this;

    }

    /**
     * Find object by id in repository
     *
     * @param int @id id of an object
     *
     * @return \Doctrine\ORM\Mapping\Entity
     */
    public function findObject($id = 0)
    {
        return $this->getEntityManager()->getRepository($this->_entityClass)->find($id);
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Exception
     */
    public function remove($id = 0)
    {
        $retv = false;
        if ($id) {
            $object = $this->findObject($id);
            if ($object) {
                try {
                    $this->getEntityManager()->remove($object);
                    $this->getEntityManager()->flush();
                    $retv = true;
                } catch (\Exception $e) {
                    $this->recoverEntityManager();
                    throw new \Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $retv;
    }

    /**
     * Save given entity
     *
     * @param $entity
     *
     * @return mixed
     * @throws \Exception
     */
    public function save($entity)
    {
        try {
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->recoverEntityManager();
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $entity;
    }

    /**
     * Since Doctrine closes the EntityManager after a Exception, we have to create
     * a fresh copy (so it is possible to save logs in the current request)
     *
     * @return void
     */
    private function recoverEntityManager()
    {
        $this->setEntityManager(
            EntityManager::create(
                $this->getEntityManager()->getConnection(),
                $this->getEntityManager()->getConfiguration()
            )
        );
    }

    /**
     * Get entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    /**
     * Set entity repository
     *
     * @param \Doctrine\ORM\EntityRepository $repository repository to set
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function setRepository($repository)
    {
        $this->_repository = $repository;

        return $this;
    }

    /**
     * Get entity class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * Set entity class name
     *
     * @param string $entityClass entity class name to set
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function setEntityClass($entityClass)
    {
        $this->_entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    /**
     * Set entity manager
     *
     * @param \Doctrine\ORM\EntityManager $em entity manager to set
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function setEntityManager($em)
    {
        $this->_em = $em;

        return $this;
    }

    /**
     * Get model alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Set model alias
     *
     * @param string $alias model alias to set
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function setAlias($alias)
    {
        $this->_alias = $alias;

        return $this;
    }

    /**
     * Get class metadata
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_classMetadata;
    }

    /**
     * Set class metadata
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata class metadata
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function setClassMetadata($classMetadata)
    {
        $this->_classMetadata = $classMetadata;

        return $this;
    }

    /**
     * Get paginator for records
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        $query     = $this->createQuery();
        $paginator = new DoctrinePaginator($query);

        return $paginator;
    }

    protected function addPresets($data)
    {
        $alias = $this->getAlias();
        if (isset($data['searchField'])) {
            $value = $data[$data['searchField']];
            $this->filter($data['searchField'], $value, $data['searchOper']);
        }

        if (isset($data['sidx'])) {
            $dir = (strtolower($data['sord']) == 'asc') ? 'ASC' : 'DESC';
            $this->_qb->addOrderBy($alias . '.' . $data['sidx'], $dir);
        }
    }

    /**
     * Create query object
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQuery()
    {

        $offset = ($this->_options->getPage() - 1) * $this->_options->getRows();

        if (!$this->_qb = $this->_options->getCustomQueryBuilder()) {
            $this->_qb = $this->getEntityManager()->createQueryBuilder();
        }

        $this->_qb->select($this->getAlias());
        $this->_qb->from($this->getEntityClass(), $this->getAlias());

        if ($itemCountPerPage = $this->_options->getRows()) {
            $this->_qb->setMaxResults($itemCountPerPage);
        }

        if ($offset) {
            $this->_qb->setFirstResult($offset);
        }

        if ($presets = $this->_options->getPresets()) {
            $this->addPresets($presets);
        }

        $filter = $this->_options->getFilters();

        if (is_array($filter)
            && array_key_exists('field', $filter)
            && array_key_exists('value', $filter)
            && array_key_exists('expression', $filter)
            && array_key_exists('options', $filter)
        ) {
            $this->filter($filter['field'], $filter['value'], $filter['expression'], $filter['options']);
        }

        if ($treeFilter = $this->_options->getTreeFilter()) {
            $this->buildTreeFilterQuery($treeFilter);
        }

        $sort = $this->_options->getSortOrder();
        if (is_array($sort)) {
            $c = 0;
            foreach ($sort as $s) {
                if (!empty($s['sidx'])) {
                    $field     = $s['sidx'];
                    $direction = isset($s['sord']) ? $s['sord'] : 'asc';
                    if ($c) {
                        $this->_qb->addOrderBy($this->getAlias() . '.' . $field, $direction);
                    } else {
                        $this->_qb->orderBy($this->getAlias() . '.' . $field, $direction);
                    }
                    $c++;
                }
            }
        }

        if ($subGridFilter = $this->_options->getSubGridFilter()) {
            foreach ($subGridFilter as $field => $value) {
                $this->_qb->andWhere(
                    $this->_qb->expr()->eq(
                        $this->getAlias() . '.' . $field,
                        $value
                    )
                );
            }
        }

        return $this->_qb;
    }

    /**
     * Sort the result set by a specified column.
     *
     * @param string $field     Column name
     * @param string $direction Ascending (ASC) or Descending (DESC)
     *
     * @return void
     */
    protected function sort($field, $direction)
    {
        if (isset($field)) {
            $this->_qb->orderBy($this->getAlias() . '.' . $field, $direction);
        }
    }

    /**
     * @param array $options
     */
    protected function buildTreeFilterQuery($options = array())
    {
        $gridOptions   = $this->_options->getGridConfig();
        $readerCols    = $gridOptions['tree_grid_options']['treeReader'];
        $filterColumns = array_values($gridOptions['tree_grid_options']['treeReader']);
        $alias         = $this->getAlias();

        foreach ($options as $col => $value) {
            $placeHolder = 'param_' . $col;
            if (in_array($col, $filterColumns)) {

                switch ($col) {
                    case $readerCols['level_field']:
                        $this->_qb->andWhere($alias . '.' . $col . ' = :' . $placeHolder);
                        break;
                    case $readerCols['left_field']:
                        $this->_qb->andWhere($alias . '.' . $col . ' > :' . $placeHolder);
                        break;
                    case $readerCols['right_field']:
                        $this->_qb->andWhere($alias . '.' . $col . ' <:' . $placeHolder);
                        break;
                }
                $this->_qb->setParameter($placeHolder, $value);
            } elseif (isset($options['parent'])) {
                $this->_qb->andWhere($alias . '.' . $col . ' = :' . $placeHolder);
                $this->_qb->setParameter($placeHolder, $value);
            }
        }
    }

    /**
     * Filter the result set based on criteria.
     *
     * @param mixed $field      column name or array of column names if search is multiple
     * @param mixed $value      value to filter result set or array of values if search is multiple
     * @param mixed $expression search expression or array of expressions if search is multiple
     * @param array $options    array of search options
     *
     * @return void
     */
    protected function filter($field, $value, $expression, $options = array())
    {
        if (isset($options['multiple'])) {
            if (is_array($field) && is_array($value) && is_array($expression)
                && count($field) == count($value)
                && count($field) == count($expression)
            ) {
                $rules = array();
                for ($i = 0; $i < count($field); $i++) {
                    $rules[] = array('field'      => $field[$i],
                                     'value'      => $value[$i],
                                     'expression' => $expression[$i]);
                }
                $this->_multiFilter($rules, $options);
            }
        } elseif (is_string($expression) && array_key_exists($expression, $this->_operator)) {
            $this->_qb->andWhere(
                $this->getAlias() . '.' . $field . ' '
                . str_replace('?', ':' . $field, $this->_operator[$expression])
            );
            $this->_qb->setParameter($field, $this->_setWildCardInValue($expression, $value));
        }
    }

    /**
     * Multiple filtering
     *
     * @param array $rules   array of rules fore multiple filtering
     * @param array $options array of search options
     *
     * @return void
     */
    protected function _multiFilter($rules, $options = array())
    {
        $boolean = strtoupper($options['boolean']);

        foreach ($rules as $rule) {
            $aliasField  = $this->getAlias() . '.' . $rule['field'];
            $placeHolder = ':' . $rule['field'];
            $method      = strtolower($boolean) . 'Where';

            if ($rule['value'] != 0 && (is_null($rule['value']) or ($rule['value'] == 'null'))) {
                $clause = $this->_qb->expr()->isNull($aliasField);
            } else {
                $clause = $aliasField . ' ' . str_replace('?', $placeHolder, $this->_operator[$rule['expression']]);
                $this->_qb->setParameter($placeHolder, $this->_setWildCardInValue($rule['expression'], $rule['value']));
            }

            $this->_qb->$method($clause);
        }
    }

    /**
     * Place wildcard filtering in value
     *
     * @param string $expression expression to filter
     * @param string $value      value to add wildcard to
     *
     * @return string
     */
    protected function _setWildCardInValue($expression, $value)
    {
        switch (strtoupper($expression)) {
            case 'BEGIN_WITH':
            case 'NOT_BEGIN_WITH':
                $value = $value . '%';
                break;

            case 'END_WITH':
            case 'NOT_END_WITH':
                $value = '%' . $value;
                break;

            case 'CONTAIN':
            case 'NOT_CONTAIN':
                $value = '%' . $value . '%';
                break;
        }

        return $value;
    }

    public function setCustomQueryBuilder($customQueryBuilder)
    {
        $this->_customQueryBuilder = $customQueryBuilder;

        return $this;
    }

    public function getCustomQueryBuilder()
    {
        return $this->_customQueryBuilder;
    }

    /**
     * @param $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * @return \SynergyDataGrid\Model\Config\ModelOptions
     */
    public function getOptions()
    {
        return $this->_options;
    }

    public function setOrmKey($orm_key)
    {
        $this->_orm_key = $orm_key;

        return $this;
    }

    public function getOrmKey()
    {
        return $this->_orm_key;
    }

    /**
     * @param $entity
     * @param $params
     *
     * @return mixed
     */
    public function populateEntity($entity, $params)
    {
        $message = null;
        $mapping = $this->getEntityManager()->getClassMetadata($this->getEntityClass());

        foreach ($params as $param => $value) {
            if (array_key_exists($param, $mapping->fieldMappings) or array_key_exists(
                    $param, $mapping->associationMappings
                )
            ) {

                $method = 'set' . ucfirst($param);
                $getter = 'get' . ucfirst($param);
                $value  = ($value == 'null' or (empty($value) and !is_numeric($value))) ? null : $value;

                if (isset($mapping->associationMappings[$param])) {
                    $target = $mapping->associationMappings[$param]['targetEntity'];

                    if ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::ONE_TO_MANY) {
                        $message = "OneToMany updates not supported: '{$param}' was not updated";
                    } elseif ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::MANY_TO_MANY) {
                        /** @var \Doctrine\Common\Collections\ArrayCollection $param */
                        if ($entity->$getter() and $entity->$getter()->count()) {
                            $entity->$getter()->clear();
                        } else {
                            $entity->$method(new ArrayCollection());
                        }
                        $value = explode(',', $value);
                        $value = array_unique(array_filter($value));

                        foreach ($value as $v) {
                            if ($foreignEntity = $this->getEntityManager()->find($target, $v)) {
                                $entity->$getter()->add($foreignEntity);
                            } else {
                                $message = "Unable to update join table: {$target} " . $param . '"';
                            }
                        }
                    } elseif ($mapping->associationMappings[$param]['type'] == ClassMetadataInfo::MANY_TO_ONE) {
                        if ($value and $foreignEntity = $this->getEntityManager()->find($target, $value)) {
                            $entity->$method($foreignEntity);
                        } else {
                            $entity->$method(null);
                        }
                    } elseif ($value) {
                        if ($foreignEntity = $this->getEntityManager()->find($target, $value)) {
                            $entity->$method($foreignEntity);
                        } else {
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
                            $message = 'Wrong date format for column "' . $param . '"';
                            break;
                        }
                    } elseif ($type == 'json_array' && is_string($value)) {
                        $value = array_filter(explode(',', $value));
                        $entity->$method($value);
                    } else {
                        $entity->$method($value);
                    }
                }
            }
        }

        if ($message) {
            $this->getLogger()->notice($message);
        }

        return $entity;
    }

    public function updateEntity($id, $data = array())
    {
        $entity = $this->findObject($id);
        if ($entity = $this->populateEntity($entity, $data)) {
            $entity = $this->save($entity);

            return $entity->getId();
        }

        return false;

    }

    /**
     * @param \SynergyCommon\Util\ErrorHandler $logger
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @return \SynergyCommon\Util\ErrorHandler
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->_sm;
    }
}
