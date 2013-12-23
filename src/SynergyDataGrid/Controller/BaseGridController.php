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
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class BaseGridController extends AbstractRestfulController
{
    /**
     * Accept header criteria
     *
     * @var array
     */
    protected $_acceptCriteria
        = array(
            'Zend\View\Model\JsonModel' => array(
                'application/json',
                'application/jsonp',
                'application/javascript'
            ),
            'Zend\View\Model\ViewModel' => array(
                '*/*'
            ),
        );

    public function _processRequest()
    {
        $options        = array();
        $serviceManager = $this->getServiceLocator();

        /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
        $grid = $serviceManager->get('jqgrid');

        $entityKey            = $this->params()->fromRoute('entity', null);
        $className            = $grid->getClassnameFromEntityKey($entityKey);
        $options['fieldName'] = $this->params()->fromRoute('fieldName', $this->params()->fromQuery('fieldName', null));

        if ($className) {
            $grid->setGridIdentity(
                $className,
                $entityKey,
                $this->params()->fromPost('__root__', null),
                $this->params()->fromPost('displayTree', false)
            );

            if ($grid->getIsTreeGrid()) {
                $this->_setTreeGridVariables($className);
            }

            $payLoad = $grid->prepareGridData($this->getRequest(), $options);
        } else {
            $payLoad = array(
                'error'   => true,
                'message' => 'Invalid entity found'
            );
        }

        return $this->_sendPayload($payLoad);
    }

    protected function _setTreeGridVariables($className)
    {
        $rootId = $this->params()->fromPost('__root__', null);
        $nodeId = $this->params()->fromPost('nodeid', null);

        if ($rootId and !$nodeId) {
            /** @var $baseModel \SynergyDataGrid\Model\BaseModel */
            $baseModel = $this->getServiceLocator()->get('synergydatagrid\model');
            if ($item = $baseModel->getRepository($className)->find($rootId)) {
                $post            = $this->getRequest()->getPost();
                $post['nodeid']  = $item->getId();
                $post["n_left"]  = $item->getLft();
                $post["n_right"] = $item->getRgt();
                $post["n_level"] = $item->getLevel();

                $this->getRequest()->setPost($post);
            }
        }
    }

    /**
     * Render output
     *
     * @param $payload
     *
     * @return \Zend\View\Model\ModelInterface
     */
    protected function _sendPayload($payload)
    {
        $viewModel = $this->acceptableViewModelSelector($this->_acceptCriteria);
        $viewModel->setVariables($payload);

        if (isset($payload['error']) and $payload['error'] == true) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
        }

        return $viewModel;
    }
}
