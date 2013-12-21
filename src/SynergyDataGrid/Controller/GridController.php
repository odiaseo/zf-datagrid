<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace SynergyDataGrid\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class GridController extends AbstractActionController
{
    /**
     * CRUD operations for Grid Models
     *
     * @return JsonModel
     */
    public function crudAction()
    {
        $response       = '';
        $options        = array();
        $serviceManager = $this->getServiceLocator();

        /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
        $grid = $serviceManager->get('jqgrid');

        $entityKey            = $this->params()->fromRoute('entity', null);
        $className            = $grid->getClassnameFromEntityKey($entityKey);
        $options['fieldName'] = $this->params()->fromRoute('fieldName', $this->params()->fromQuery('fieldName', null));

        if ($className) {
            $request = $this->getRequest();
            $grid->setGridIdentity(
                $className,
                $entityKey,
                $this->params()->fromPost('__root__', null),
                $this->params()->fromPost('displayTree', false)
            );

            if ($grid->getIsTreeGrid()) {

                $rootId = $this->params()->fromPost('__root__', null);
                $nodeId = $this->params()->fromPost('nodeid', null);

                if ($rootId and !$nodeId) {
                    /** @var $baseModel \SynergyDataGrid\Model\BaseModel */
                    $baseModel = $serviceManager->get('synergydatagrid\model');
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

            $response = $grid->prepareGridData($request, $options);
        }

        return new JsonModel($response);
    }
}
