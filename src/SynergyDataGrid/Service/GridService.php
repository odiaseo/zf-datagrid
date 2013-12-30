<?php
namespace SynergyDataGrid\Service;

use Zend\Json\Json;
use Zend\ServiceManager\ServiceManager;

class GridService
    extends BaseService
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

            $model = $this->_getModel($className, $data, $grid->getConfig());

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
            $model     = $this->_getModel($className, $data);
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
            $className = $this->getClassnameFromEntityKey($data['entity']);
            $model     = $this->_getModel($className, $data);
            $entity    = new $className();

            $entity = $model->populateEntity($entity, $data);
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
            $model     = $this->_getModel($className);
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

}