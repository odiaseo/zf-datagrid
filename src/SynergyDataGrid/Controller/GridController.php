<?php
/**
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

namespace SynergyDataGrid\Controller;

use SynergyDataGrid\Service\GridService;

/**
 * Class GridController
 * @package SynergyDataGrid\Controller
 */
class GridController extends BaseGridController
{

    public function getList()
    {
        $params = array_merge(
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $this->_getService()->getGridList($params);

        return $this->_sendPayload($payLoad);
    }

    /**
     * Create a new row
     *
     * @param mixed $data
     *
     * @return \Zend\View\Model\ModelInterface
     */
    public function create($data)
    {
        $params = array_merge(
            (array)$data,
            (array)$this->params()->fromQuery(),
            (array)$this->params()->fromRoute()
        );

        if (isset($params['oper']) and $params['oper'] == 'edit') {
            $payLoad = $this->_getService()->updateRecord($params);
        } else {
            $payLoad = $this->_getService()->createRecord($params);
        }

        return $this->_sendPayload($payLoad);
    }

    public function deleteList($data)
    {
        $params = array_merge(
            (array)$data,
            (array)$this->params()->fromQuery(),
            (array)$this->params()->fromRoute()
        );

        $payLoad = $this->_getService()->deleteRecord($params);

        return $this->_sendPayload($payLoad);
    }

    /**
     * Update grid record
     *
     * @param mixed $data
     *
     * @return mixed|\Zend\View\Model\ModelInterface
     */
    public function replaceList($data)
    {
        $params = array_merge(
            (array)$data,
            (array)$this->params()->fromQuery(),
            (array)$this->params()->fromRoute()
        );

        $payLoad = $this->_getService()->updateRecord($params);

        return $this->_sendPayload($payLoad);
    }

    /**
     * @param null $serviceKey
     * @return GridService
     */
    protected function _getService($serviceKey = null)
    {
        return $this->getServiceLocator()->get('synergy\service\grid');
    }
}
