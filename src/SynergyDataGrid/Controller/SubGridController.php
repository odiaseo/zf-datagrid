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

use SynergyDataGrid\Service\SubGridService;

/**
 * Class SubGridController
 * @package SynergyDataGrid\Controller
 */
class SubGridController extends BaseGridController
{
    /**
     * Get sub grid data
     *
     * @return \Zend\View\Model\ModelInterface
     */
    public function getList()
    {
        $params = array_merge(
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $this->_getService()->getSubGridList($params);

        return $this->_sendPayload($payLoad);
    }

    /**
     * Update subgrid record
     *
     * @param mixed $data
     *
     * @return mixed|\Zend\View\Model\ModelInterface
     */
    public function replaceList($data)
    {
        $params = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $this->_getService()->updateSubGridRecord($params);

        return $this->_sendPayload($payLoad);
    }

    public function create($data)
    {
        $params = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        if ($this->params()->fromPost('oper') == 'del') {
            $payLoad = $this->_getService()->deleteRecord($params);
        } else {
            $payLoad = $this->_getService()->createSubGridRecord($params);
        }

        return $this->_sendPayload($payLoad);
    }

    /**
     * @param null $serviceKey
     *
     * @return SubGridService
     */
    protected function _getService($serviceKey = null)
    {
        return $this->getServiceLocator()->get('synergy\service\subgrid');
    }
}
