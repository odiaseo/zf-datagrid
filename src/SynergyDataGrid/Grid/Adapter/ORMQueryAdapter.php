<?php
namespace SynergyDataGrid\Grid\Adapter;

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
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @author  Pele Odiase
 * @see     http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
 * @package mvcgrid
 */
class ORMQueryAdapter extends QueryAdapter
{
    /**
     * Doctrine Query Builder
     *
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $_qb;

    /**
     * Create query based on current settings
     *
     * @param null $offset
     * @param null $itemCountPerPage
     *
     * @return QueryBuilder
     */
    public function createQuery($offset = null, $itemCountPerPage = null)
    {
        $service = $this->getService();
        $alias   = $service->getAlias();
        $entity  = $service->getEntityClass();

        //gets custom query if set
        if (!$this->_qb = $this->getGrid()->getCustomQueryBuilder()) {
            $this->_qb = $this->getService()->getEntityManager()->createQueryBuilder();
        }

        $this->_qb->select($alias);
        $this->_qb->from($entity, $alias);

        if ($itemCountPerPage) {
            $this->_qb->setMaxResults($itemCountPerPage);
        }

        if ($offset) {
            $this->_qb->setFirstResult($offset);
        }

        $filter = $this->getFilter();

        if (is_array($filter)
            && array_key_exists('field', $filter)
            && array_key_exists('value', $filter)
            && array_key_exists('expression', $filter)
            && array_key_exists('options', $filter)
        ) {
            $this->filter($filter['field'], $filter['value'], $filter['expression'], $filter['options']);
        }

        if ($treeFilter = $this->getTreeFilter()) {
            $this->buildTreeFilterQuery($treeFilter);
        }

        $sort = $this->getSort();

        if (is_array($sort)) {
            $c = 0;
            foreach ($sort as $s) {
                if (isset($s['sidx'])) {
                    $field     = $s['sidx'];
                    $direction = isset($s['sord']) ? $s['sord'] : 'asc';
                    if ($c) {
                        $this->_qb->addOrderBy($this->getService()->getAlias() . '.' . $field, $direction);
                    } else {
                        $this->_qb->orderBy($this->getService()->getAlias() . '.' . $field, $direction);
                    }
                    $c++;
                }
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
            $this->_qb->orderBy($this->getService()->getAlias() . '.' . $field, $direction);
        }
    }

    /**
     * @param array $options
     */
    protected function buildTreeFilterQuery($options = array())
    {
        $gridOptions   = $this->getGrid()->getConfig();
        $readerCols    = $gridOptions['tree_grid_options']['treeReader'];
        $filterColumns = array_values($gridOptions['tree_grid_options']['treeReader']);
        $alias         = $this->getService()->getAlias();

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
                $this->getService()->getAlias() . '.' . $field . ' '
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
            if ($boolean == 'OR') {
                $this->_qb->orWhere(
                    $this->getService()->getAlias() . '.' . $rule['field']
                        . ' ' . str_replace('?', ':' . $rule['field'], $this->_operator[$rule['expression']])
                );
            } else {
                $this->_qb->andWhere(
                    $this->getService()->getAlias() . '.' . $rule['field']
                        . ' ' . str_replace('?', ':' . $rule['field'], $this->_operator[$rule['expression']])
                );
            }
            $this->_qb->setParameter($rule['field'], $this->_setWildCardInValue($rule['expression'], $rule['value']));
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

}