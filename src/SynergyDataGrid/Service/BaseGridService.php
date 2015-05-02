<?php
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

namespace SynergyDataGrid\Service;

use SynergyCommon\Service\BaseService;
use SynergyDataGrid\Model\Config\ModelOptions;
use Zend\Json\Json;

/**
 * Class BaseGridService
 *
 * @package SynergyDataGrid\Service
 */
class BaseGridService extends BaseService
{
    const PROXY_PREFIX = 'DoctrineORMModule\Proxy\__CG__\\';
    protected $_expression
        = array(
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

    protected function _processFilter($data)
    {

        $filters   = array();
        $operation = isset($data['searchOper']) ? $data['searchOper'] : 'eq';

        if (isset($data['customFilters'])) {
            $filters = $this->processSearchFilters($data['customFilters'], $filters);

        }
        if (isset($data['filters'])) {
            $filters = $this->processSearchFilters($data['filters'], $filters);
        }

        if (isset($data['searchField'])) {
            // Single field filtering
            $rules = array(
                array(
                    'field' => $data['searchField'],
                    'data'  => trim($data['searchString']),
                    'op'    => $operation,
                )
            );

            $filters = $this->processSearchFilters(array('rules' => $rules), $filters);
        }

        return $filters;
    }

    /**
     * @param       $params
     * @param array $combined
     *
     * @return array
     */
    private function  processSearchFilters($params, $combined = array())
    {
        $filter = Json::decode($params, Json::TYPE_ARRAY);

        if (isset($filter['rules']) and count($filter['rules']) > 0) {
            if (empty($combined['rules'])) {
                $combined['rules'] = array();
            }
            foreach ($filter['rules'] as $rule) {
                $combined['field'][]      = $rule['field'];
                $combined['value'][]      = $rule['data'];
                $combined['expression'][] = $this->_expression[$rule['op']];
                $combined['rules'][]      = $rule;
            }

            $combined['options']['multiple'] = true;
            $combined['options']['boolean']  = (isset($filter['groupOp'])) ? $filter['groupOp'] : 'AND';

            return $combined;
        }

        return $combined;
    }

    /**
     * Get domain model
     *
     * @param       $data
     * @param       $className
     * @param array $gridOptions
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    public function getModel($className, $data = array(), $gridOptions = array())
    {
        $sort = array();
        if (strpos($className, self::PROXY_PREFIX) === 0) {
            $className = str_replace(self::PROXY_PREFIX, '', $className);
        }
        if (!empty($data['sidx'])) {
            $sort[] = array(
                'sidx' => $data['sidx'],
                'sord' => isset($data['sord']) ? $data['sord'] : 'asc'
            );
        } else {
            $sort = array();
        }

        if (isset($data['filters']) || isset($data['customFilters'])) {
            $filters = $this->_processFilter($data);
        } else {
            $filters = array();
        }
        $options = array(
            'gridConfig' => $gridOptions,
            'filters'    => $filters,
            'grid'       => isset($data['grid']) ? $data['grid'] : null,
            'entity'     => isset($data['entity']) ? $data['entity'] : null,
            'page'       => isset($data['page']) ? $data['page'] : null,
            'rows'       => isset($data['rows']) ? $data['rows'] : null,
            'sord'       => isset($data['sord']) ? $data['sord'] : null,
            'sidx'       => isset($data['sidx']) ? $data['sidx'] : null,
            'presets'    => isset($data['presets']) ? $data['presets'] : array(),
            'sortOrder'  => $sort,
        );

        /** @var $model \SynergyDataGrid\Model\BaseModel */
        $model = $this->_serviceManager->get('synergydatagrid\model');
        $model->setEntityClass($className);
        $modelOptions = new ModelOptions($options);
        $model->setOptions($modelOptions);

        /** @var $entityManager \Doctrine\Orm\EntityManager */
        $entityManager = $this->_serviceManager->get($model->getOrmKey());
        $model->setEntityManager($entityManager);

        return $model;
    }
}
