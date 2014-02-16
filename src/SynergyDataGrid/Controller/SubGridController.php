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

namespace SynergyDataGrid\Controller;

use Zend\Http\Response;

class SubGridController extends BaseGridController
{
    /**
     * Get sub grid data
     *
     * @return \Zend\View\Model\ModelInterface
     */
    public function getList()
    {

        /** @var $service \SynergyDataGrid\Service\SubGridService */
        $service = $this->getServiceLocator()->get('synergy\service\subgrid');
        $params  = array_merge(
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->getSubGridList($params);

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
        /** @var $service \SynergyDataGrid\Service\SubGridService */
        $service = $this->getServiceLocator()->get('synergy\service\subgrid');
        $params  = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->updateSubGridRecord($params);

        return $this->_sendPayload($payLoad);

    }

    public function create($data)
    {
        /** @var $service \SynergyDataGrid\Service\SubGridService */
        $service = $this->getServiceLocator()->get('synergy\service\subgrid');

        $params = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        if ($this->params()->fromPost('oper') == 'del') {
            $payLoad = $service->deleteRecord($params);
        } else {
            $payLoad = $service->createSubGridRecord($params);
        }

        return $this->_sendPayload($payLoad);

    }
}
