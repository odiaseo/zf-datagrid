<?php
namespace SynergyDataGridTest\Test;


use SynergyDataGridTest\BaseTestClass;

/**
 * @backupGlobals disabled
 */
class CrudTest
    extends BaseTestClass
{
    public function testGetList()
    {
        /** @var $service \SynergyDataGrid\Service\GridService */
        $service = $this->_serviceManager->get('synergy\service\grid');
        $params  = array(
            'entity' => 'test-tree'
        );

        $payLoad = $service->getGridList($params);
        $this->assertArrayHasKey('error', $payLoad);

    }
}