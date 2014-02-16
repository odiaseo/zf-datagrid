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

use SynergyCommon\Controller\BaseRestfulController;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;

class BaseGridController
    extends BaseRestfulController
{

    public function _processRequest()
    {
        $options        = array();
        $serviceManager = $this->getServiceLocator();

        /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
        $grid = $serviceManager->get('jqgrid');

        /** @var $service \SynergyDataGrid\Service\GridService' */
        $service = $serviceManager->get('synergy\service\grid');

        $entityKey            = $this->params()->fromRoute('entity', null);
        $className            = $service->getClassnameFromEntityKey($entityKey);
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

    /**
     * Set variables required for nestedset models
     *
     * @param $className
     */
    protected function _setTreeGridVariables($className)
    {
        $rootId = $this->params()->fromPost('__root__', null);
        $nodeId = $this->params()->fromPost('nodeid', null);

        /** @var $request \Zend\Http\PhpEnvironment\Request */
        $request = $this->getRequest();

        if ($rootId and !$nodeId) {
            /** @var $baseModel \SynergyDataGrid\Model\BaseModel */
            $baseModel = $this->getServiceLocator()->get('synergydatagrid\model');
            if ($item = $baseModel->getRepository($className)->find($rootId)) {
                $post            = $request->getPost();
                $post['nodeid']  = $item->getId();
                $post["n_left"]  = $item->getLft();
                $post["n_right"] = $item->getRgt();
                $post["n_level"] = $item->getLevel();

                $request->setPost($post);
            }
        }
    }

}
