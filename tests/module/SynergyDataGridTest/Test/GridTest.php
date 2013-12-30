<?php
namespace SynergyDataGridTest\Test;

use Doctrine\ORM\QueryBuilder;
use SynergyDataGrid\Grid\GridType\BaseGrid;
use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
use SynergyDataGridTest\BaseTestClass;
use Zend\Http\PhpEnvironment\Request;
use Zend\Stdlib\Parameters;
use Zend\View\Renderer\PhpRenderer;

/**
 * @backupGlobals disabled
 */
class GridTest
    extends BaseTestClass
{
    /** @var \SynergyDataGrid\Grid\GridType\DoctrineORMGrid; */
    protected $_grid;

    public function setUp()
    {
        parent::setUp();
        $this->_grid = $this->_serviceManager->get('jqgrid');
        $this->_grid->setUrl('/grid-test');
    }

    public function testGridFactory()
    {
        $this->assertInstanceOf(
            '\SynergyDataGrid\Grid\GridType\DoctrineORMGrid', $this->_grid,
            'Invalid grid instance created'
        );
    }

    public function testGridIdentity()
    {
        $entityClassName = '\SynergyDataGridTest\Entity\TestBrand';
        $this->_grid->setGridIdentity($entityClassName);

        $service = $this->_grid->getModel();
        $this->assertInstanceOf('SynergyDataGrid\Model\BaseModel', $service);

        $className = $this->_grid->getEntity();
        $this->assertSame($className, $entityClassName);

        $em = $this->_grid->getObjectManager();
        $this->assertInstanceOf('\Doctrine\ORM\EntityManager', $em);

        $isTreeGrid = $this->_grid->getIsDetailGrid();
        $this->assertFalse($isTreeGrid);
    }

    public function testGridDisplay()
    {
        $entityClassName = '\SynergyDataGridTest\Entity\TestBrand';
        $this->_grid->setGridIdentity($entityClassName, 'testbrand');

        $config                        = $this->_grid->getConfig();
        $config['first_data_as_local'] = false;
        $this->_grid->setConfig($config);
        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('viewhelpermanager')->get('displayGrid');
        $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);

        $return = $viewHelper->initGrid($this->_grid);
        $this->assertTrue(count($return) > 0);
    }

    public function testSubGrid()
    {
        $entityClassName = '\SynergyDataGridTest\Entity\TestStore';
        $this->_grid->setGridIdentity($entityClassName);

        $config                        = $this->_grid->getConfig();
        $config['first_data_as_local'] = false;
        $this->_grid->setConfig($config);
        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('viewhelpermanager')->get('displayGrid');
        $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);

        $return = $viewHelper->initGrid($this->_grid);
        $this->assertTrue(count($return) > 0);

        $mapping = $this->_grid->getModel()
            ->getEntityManager()
            ->getClassMetadata($this->_grid->getEntity());

        $this->_grid->setGridColumns();

        $subGrid = $this->_grid->createSubGridAsGrid($mapping->associationMappings['testBrands']);
        $this->assertInstanceOf('\SynergyDataGrid\Grid\GridType\BaseGrid', $subGrid);

    }

    public function testGridData()
    {
        $entityClassName = '\SynergyDataGridTest\Entity\TestBrand';
        $this->_grid->setGridIdentity($entityClassName);

        $this->_grid->setCustomQueryBuilder(
            new QueryBuilder($this->_grid->getModel()->getEntityManager())
        );

        $this->_grid->setDatatype('local');
        $this->_grid->getOptions();

        $params = array(
            'page'                     => 1,
            'rows'                     => 25,
            BaseGrid::GRID_IDENTIFIER  => $this->_grid->getId(),
            BaseGrid::ENTITY_IDENTFIER => $this->_grid->getEntity()
        );

        $request    = new Request();
        $parameters = new Parameters($params);
        $request->setPost($parameters);


        $this->_grid->prepareGridData($request);

        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('viewhelpermanager')->get('displayGrid');

        $viewHelper->setView(new PhpRenderer());

    }

}