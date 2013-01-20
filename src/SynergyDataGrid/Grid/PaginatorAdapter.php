<?php
namespace SynergyDataGrid\Grid;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * Pagination Adapter to paginate results for JqGrid
 *
 * @author Pele Odiase
 * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
 * @package mvcgrid
 */
class PaginatorAdapter implements AdapterInterface
{
    /**
     * Doctrine Query Builder
     *
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $_qb;
    /**
     * Doctrine Service Model
     *
     * @var \SynergyDataGrid\Model\BaseService
     */
    protected $_service;
    /**
     * Filtering parameters, to apply before fetching
     *
     * @var array
     */
    protected $_filter;
    /**
     * Sorting parameters, to apply before fetching
     *
     * @var array
     */
    protected $_sort;
    /**
     * Mapping human-readable constants to DQL operatores
     *
     * @var array
     */
    protected $_operator = array(
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
     * JqGrid object instance
     *
     * @var \SynergyDataGrid\Grid\JqGridFactory
     */
    protected $_grid;

    /**
     * Set up base PaginatorAdapter options
     *
     * @param \SynergyDataGrid\Model\BaseService $service Doctrine Service Model
     * @param array $filter array of filter options
     * @param array $sort array of sort options
     * @param \SynergyDataGrid\Grid\JqGridFactory $grid JqGrid instance
     *
     * @return void
     */
    public function __construct($service, $filter = false, $sort = false, $grid = null)
    {
        $this->setService($service);
        $this->setFilter($filter);
        $this->setSort($sort);
        $this->setGrid($grid);
    }

    /**
     * Get items for selected page, based on offset and itemCountPerPage
     *
     * @param int $offset offset to start fetching
     * @param int $itemCountPerPage limit to fetch
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->_createQuery(false, $offset, $itemCountPerPage);
        return $this->_qb->getQuery()->getArrayResult();
    }

    /**
     * Get items count for current query
     *
     * @return int
     */
    public function count()
    {
        $this->_createQuery(true);
        return $this->_qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create query based on current settings
     *
     * @param bool $countOnly do we need query for count or full one
     * @param int $offset start position for query
     * @param int $itemCountPerPage limit for query
     *
     * @return void
     */
    public function _createQuery($countOnly = false, $offset = null, $itemCountPerPage = null)
    {
        $service   = $this->getService();
        $this->_qb = $service->getEntityManager()->createQueryBuilder();
        $alias     = $service->getAlias();
        $entity    = $service->getEntityClass();
        $debug     = false;

        if ($countOnly) {
            $this->_qb->select('count(' . $alias . ')');
        } else {
            //$this->_qb->select($this->_getFields($alias));
            $this->_qb->select($alias);
            //$this->_qb->innerJoin('Application\Entity\Site', 'u');
           // $debug = true;
        }

        $this->_qb->from($entity, $alias);
        if ($itemCountPerPage) {
            $this->_qb->setMaxResults($itemCountPerPage);
        }
        if ($offset) {
            $this->_qb->setFirstResult($offset);
        }
        $filter = $this->getFilter();
        if (is_array($filter) && array_key_exists('field', $filter) && array_key_exists('value', $filter) && array_key_exists('expression', $filter) && array_key_exists('options', $filter)) {
            $this->filter($filter['field'], $filter['value'], $filter['expression'], $filter['options']);
        }
        $sort = $this->getSort();
        if (!$countOnly && is_array($sort) && array_key_exists('sidx', $sort) && array_key_exists('sord', $sort)) {
            $this->sort($sort['sidx'], $sort['sord']);
        }

        if ($debug) {
            $dql = $this->_qb->getDQL();
            die($dql);
        }

    }

    /**
     * Sort the result set by a specified column.
     *
     * @param string $field Column name
     * @param string $direction Ascending (ASC) or Descending (DESC)
     *
     * @return void
     */
    public function sort($field, $direction)
    {
        if (isset($field)) {
            $this->_qb->orderBy($this->getService()->getAlias() . '.' . $field, $direction);
        }
    }

    /**
     * Filter the result set based on criteria.
     *
     * @param mixed $field column name or array of column names if search is multiple
     * @param mixed $value value to filter result set or array of values if search is multiple
     * @param mixed $expression search expression or array of expressions if search is multiple
     * @param array $options array of search options
     *
     * @return void
     */
    public function filter($field, $value, $expression, $options = array())
    {
        if (isset($options['multiple'])) {
            if (is_array($field) && is_array($value) && is_array($expression) && count($field) == count($value) && count($field) == count($expression)) {
                $rules = array();
                for ($i = 0; $i < count($field); $i++) {
                    $rules[] = array('field'      => $field[$i],
                                     'value'      => $value[$i],
                                     'expression' => $expression[$i]);
                }
                $this->_multiFilter($rules, $options);
            }
        } elseif (is_string($expression) && array_key_exists($expression, $this->_operator)) {
            $this->_qb->where($this->getService()->getAlias() . '.' . $field . ' ' . str_replace('?', '?1', $this->_operator[$expression]));
            $this->_qb->setParameter(1, $this->_setWildCardInValue($expression, $value));
        }
    }

    /**
     * Multiple filtering
     *
     * @param array $rules array of rules fore multiple filtering
     * @param array $options array of search options
     *
     * @return void
     */
    private function _multiFilter($rules, $options = array())
    {
        $boolean     = strtoupper($options['boolean']);
        $paramNumber = 1;
        foreach ($rules as $rule) {
            if ($boolean == 'OR') {
                $this->_qb->orWhere($this->getService()->getAlias() . '.' . $rule['field'] . ' ' . str_replace('?', '?' . $paramNumber, $this->_operator[$rule['expression']]));
            } else {
                $this->_qb->andWhere($this->getService()->getAlias() . '.' . $rule['field'] . ' ' . str_replace('?', '?' . $paramNumber, $this->_operator[$rule['expression']]));
            }
            $this->_qb->setParameter($paramNumber++, $this->_setWildCardInValue($rule['expression'], $rule['value']));
        }
    }

    /**
     * Place wildcard filtering in value
     *
     * @param string $expression expression to filter
     * @param string $value value to add wildcard to
     *
     * @return string
     */
    private function _setWildCardInValue($expression, $value)
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

    /**
     * Get fields for query
     *
     * @param string $alias alias to add to fields
     *
     * @return array
     */
    public function _getFields($alias = '')
    {
        $columns  = $this->getGrid()->getColumns();
        $metadata = $this->getService()->getClassMetadata();
        $fields   = array();

        foreach ($columns as $column) {
            if ($column->getSelectable()) {
                $fields[] = $column->getName();
            }
        }
        if ($alias) {
            foreach ($fields as $item) {
                if (isset($metadata->associationMappings[$item])) {

                } else {
                    $result[] = $alias . '.' . $item . ' AS ' . $alias . '__' . $item;
                }
            }
        } else {
            $result = $fields;
        }
        return $result;
    }

    /**
     * Get service model instance
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Set service model instance
     *
     * @param \SynergyDataGrid\Model\BaseService $service Doctrine Service Model
     *
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Get filter setting
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Set filter settings
     *
     * @param array $filter filter option to apply to current query
     *
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function setFilter($filter)
    {
        $this->_filter = $filter;
        return $this;
    }

    /**
     * Get sort setting
     *
     * @return array
     */
    public function getSort()
    {
        return $this->_sort;
    }

    /**
     * Set sort settings
     *
     * @param array $sort sort option to apply to current query
     *
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function setSort($sort)
    {
        $this->_sort = $sort;
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
     *
     * @param \SynergyDataGrid\Grid\JqGridFactory
     *
     * @return \SynergyDataGrid\Grid\JqGridFactory
     */
    public function setGrid($grid)
    {
        $this->_grid = $grid;
        return $this;
    }

}