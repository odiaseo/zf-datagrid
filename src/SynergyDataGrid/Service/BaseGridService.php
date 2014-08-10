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
use Zend\ServiceManager\ServiceManager;

class BaseGridService
    extends BaseService
{
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

        if (isset($data['filters'])) {
            $filter = Json::decode($data['filters'], Json::TYPE_ARRAY);

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
        } elseif (isset($data['searchField'])) {

            // Single field filtering
            return array(
                'field'      => $data['searchField'],
                'value'      => trim($data['searchString']),
                'expression' => $this->_expression[$operation],
                'options'    => array()
            );
        }

        return $filters;

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
        if (!empty($data['sidx'])) {
            $sort[] = array(
                'sidx' => $data['sidx'],
                'sord' => isset($data['sord']) ? $data['sord'] : 'asc'
            );
        } else {
            $sort = array();
        }

        $options = array(
            'gridConfig' => $gridOptions,
            'grid'       => isset($data['grid']) ? $data['grid'] : null,
            'entity'     => isset($data['entity']) ? $data['entity'] : null,
            'filters'    => isset($data['filters']) ? $this->_processFilter($data) : null,
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