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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

class SubGridService
    extends BaseService
{

    /**
     * Get records for a subgrid
     *
     * @param $data
     *
     * @return array
     */
    public function getSubGridList($data)
    {
        try {
            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->_getModel($className, $data);
            $field     = $data['fieldName'];

            $row       = $model->getEntityManager()->getRepository($className)->find($data['subgridid']);
            $method    = 'get' . ucfirst($field);
            $refObject = $row->$method();

            if ($refObject instanceof PersistentCollection) {
                $mapping      = $refObject->getMapping();
                $targetEntity = $mapping['targetEntity'];
            } else {
                $targetEntity = get_class($refObject);
            }

            $subGridModel = $this->_getModel($targetEntity, $data);
            $parentMeta   = $model->getEntityManager()->getClassMetadata($className);


            if ($mappedBy = $parentMeta->associationMappings[$field]['mappedBy']) {
                $subGridFilter = array($mappedBy => $data['subgridid']);
                $subGridModel->getOptions()->setSubGridFilter($subGridFilter);
                $paginator = $subGridModel->getPaginator();
                $childRows = $paginator->getIterator();
                $total     = $paginator->count();
                $rowNum    = $paginator->getQuery()->getMaxResults();
            } else {
                $childRows = $refObject;
                $total     = count($refObject);
                $rowNum    = $subGridModel->getOptions()->getRows();
            }

            if (!$childRows) {
                $childRows = new ArrayCollection();
            }


            /** @var $subGrid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
            $subGrid = $this->_serviceManager->get('jqgrid');
            $subGrid->setGridIdentity($targetEntity, $field);


            $subGrid->reorderColumns();
            $columns = $subGrid->setGridColumns()->getColumns();

            $record = array(
                'page'    => $subGridModel->getOptions()->getPage() ? : 1,
                'total'   => ceil($total / $rowNum),
                'records' => $total,
                'rows'    => $subGrid->formatGridData($childRows, $columns)
            );

            return $record;

        } catch (\Exception $exception) {
            $this->getLogger()->logException($exception);
            $record = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $record;
    }

    public function updateSubGridRecord($data)
    {
        try {

            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->_getModel($className, $data);
            $field     = $data['fieldName'];

            $mapping = $model->getEntityManager()->getClassMetadata($className);
            $target  = $mapping->associationMappings[$field]['targetEntity'];
            $rowId   = isset($data['id']) ? $data['id'] : null;

            $entity = $model->getEntityManager()->getRepository($target)->find($rowId);

            $subGridModel = $this->_getModel($target, $data);
            $entity       = $subGridModel->populateEntity($entity, $data);

            $entity = $model->save($entity);
            $id     = $entity->getId();

            $record = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully updated', $id)
            );
        } catch (\Exception $exception) {
            $this->getLogger()->logException($exception);
            $record = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $record;
    }

    public function createSubGridRecord($data)
    {
        try {

            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->_getModel($className, $data);
            $field     = $data['fieldName'];

            $mapping = $model->getEntityManager()->getClassMetadata($className);
            $target  = $mapping->associationMappings[$field]['targetEntity'];

            /** @var $row \SynergyCommon\Entity\BaseEntity */
            $row    = $model->findObject($data['subgridid']);
            $entity = new $target;

            $subGridModel = $this->_getModel($target, $data);
            $entity       = $subGridModel->populateEntity($entity, $data);

            $row->$field->add($entity);
            $model->save($row);
            $id = $row->getId();

            $record = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully updated', $id)
            );
        } catch (\Exception $exception) {
            $this->getLogger()->logException($exception);
            $record = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $record;
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
            $model     = $this->_getModel($className, $data);
            $field     = $data['fieldName'];

            $mapping = $model->getEntityManager()->getClassMetadata($className);
            $target  = $mapping->associationMappings[$field]['targetEntity'];

            $subGridModel = $this->_getModel($target, $data);
            $subGridModel->remove($data['id']);

            $record = array(
                'error'   => false,
                'message' => sprintf('Record #%d successfully deleted', $data['id'])
            );
        } catch (\Exception $exception) {
            $this->getLogger()->logException($exception);
            $record = array(
                'error'   => true,
                'message' => $exception->getMessage()
            );
        }

        return $record;
    }
}