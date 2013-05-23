<?php
    use \SynergyDataGrid\Grid;

    /**
     * @backupGlobals disabled
     */
    class GridTest extends BaseTestClass
    {
        public function testGridFactory()
        {
            $grid = new Grid\JqGridFactory();

            $this->assertInstanceOf('\SynergyDataGrid\Grid\JqGridFactory', $grid, 'Invalid grid instance created');

            //$grid->setGridIdentity('page');
        }
    }