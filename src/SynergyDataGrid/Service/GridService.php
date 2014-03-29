<?php
namespace SynergyDataGrid\Service;

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

use Zend\Json\Json;
use Zend\ServiceManager\ServiceManager;

class GridService
    extends BaseGridService
{
    /**
     * Get paginated list
     *
     * @param $data
     *
     * @return array
     */
    public function getGridList($data)
    {
        try {
            $className = $this->getClassnameFromEntityKey($data['entity']);

            /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
            $grid = $this->_serviceManager->get('jqgrid');
            $grid->setGridIdentity($className, $data['entity']);

            $model = $this->getModel($className, $data, $grid->getConfig());

            $paginator = $model->getPaginator();
            $rows      = $paginator->getIterator();

            $grid->reorderColumns();
            $columns = $grid->setGridColumns(true)->getColumns();

            $total  = $paginator->count();
            $rowNum = $paginator->getQuery()->getMaxResults();

            $return = array(
                'page'    => $model->getOptions()->getPage() ? : 1,
                'total'   => ceil($total / $rowNum),
                'records' => $total,
                'rows'    => $grid->formatGridData($rows, $columns)
            );


        } catch (\Exception $exception) {

            $this->getLogger()->logException($exception);

            $return = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $return;
    }

    /**
     * Update an existing record
     *
     * @param $data
     *
     * @return array
     */
    public function updateRecord($data)
    {
        try {
            $id        = $data['id'];
            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->getModel($className, $data);
            $model->updateEntity($id, $data);

            $return = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully updated', $id)
            );
        } catch (\Exception $exception) {

            $this->getLogger()->logException($exception);

            $return = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $return;

    }

    /**
     * Create a new record
     *
     * @param $data
     *
     * @return array
     */
    public function createRecord($data)
    {
        try {
            unset($data['id']);
            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->getModel($className, $data);

            /** @var $entity \SynergyCommon\Entity\BaseEntity */
            $entity  = new $className();
            $mapping = $model->getEntityManager()->getClassMetadata($className);
            $reflection = new \ReflectionClass($mapping->customRepositoryClassName);

            if ('Gedmo\Tree\Entity\Repository\NestedTreeRepository' == $mapping->customRepositoryClassName
                || $reflection->isSubClassOf('Gedmo\Tree\Entity\Repository\NestedTreeRepository')
            ) {
                if (isset($data['title'])) {
                    $entity->setTitle($data['title']);
                }
                /** @var $repo \Gedmo\Tree\Entity\Repository\NestedTreeRepository */
                $repo = $model->getRepository();
                if (isset($data['parent']) and $parent = $model->findObject($data['parent'])) {
                    $repo->persistAsFirstChildOf($entity, $parent);
                } else {
                    $repo->persistAsLastChild($entity);
                }

                $model->getEntityManager()->flush();
                $model->getEntityManager()->clear();

                $entity = $model->findObject($entity->getId());
                $entity = $model->populateEntity($entity, $data);
                if (method_exists($entity, 'setCreatedAt')) {
                    $entity->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                }
            } else {
                $entity = $model->populateEntity($entity, $data);
            }

            $entity = $model->save($entity);

            $return = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully created', $entity->getId())
            );
        } catch (\Exception $exception) {
            $this->getLogger()->logException($exception);
            $return = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $return;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function deleteRecord($data)
    {
        try {
            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->getModel($className);
            $model->remove($data['id']);

            $return = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully deleted', $data['id'])
            );
        } catch (\Exception $exception) {

            $this->getLogger()->logException($exception);

            $return = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $return;
    }

    public function getEntityManager()
    {
        /** @var $grid \SynergyDataGrid\Grid\GridType\BaseGrid */
        $grid = $this->_serviceManager->get('jqgrid');

        return $grid->getObjectManager();
    }

}