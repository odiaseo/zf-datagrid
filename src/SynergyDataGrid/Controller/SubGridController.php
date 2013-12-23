<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
        $service = $this->getServiceLocator()->get('sub_grid_service');
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
        $service = $this->getServiceLocator()->get('sub_grid_service');
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
        $service = $this->getServiceLocator()->get('sub_grid_service');

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
