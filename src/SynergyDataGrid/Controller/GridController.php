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

use Zend\Http\Response;

/**
 * Class GridController
 * @package SynergyDataGrid\Controller
 */
class GridController extends BaseGridController
{
    public function getList()
    {
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->getServiceLocator()->get('synergy\service\grid');
        $params  = array_merge(
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->getGridList($params);

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
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->getServiceLocator()->get('synergy\service\grid');
        $params  = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->createRecord($params);

        return $this->_sendPayload($payLoad);
    }

    public function deleteList($data)
    {
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->getServiceLocator()->get('synergy\service\grid');
        $params  = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->deleteRecord($params);

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
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->getServiceLocator()->get('synergy\service\grid');
        $params  = array_merge(
            $data,
            $this->params()->fromQuery(),
            $this->params()->fromRoute()
        );

        $payLoad = $service->updateRecord($params);

        return $this->_sendPayload($payLoad);
    }
}
