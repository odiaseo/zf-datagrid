<?php
namespace SynergyDataGridTest\Grid;

use SynergyDataGridTest\BaseTestClass;

/**
 * @backupGlobals disabled
 */
class CrudTest extends BaseTestClass
{
    public function testGetList()
    {
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->_serviceManager->get('synergy\service\grid');
        $params  = array(
            'entity' => 'test-tree',
            'page'   => 1,
            'rows'   => 25,
            'sord'   => 'asc',
            'sidx'   => 'title',
        );

        $payLoad = $service->getGridList($params);
        $this->assertArrayHasKey('error', $payLoad);
    }

    public function testGridInstance()
    {
        /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
        $grid = $this->_serviceManager->get('jqgrid');
        $this->assertInstanceOf('\SynergyDataGrid\Grid\GridType\DoctrineORMGrid', $grid);
    }
}