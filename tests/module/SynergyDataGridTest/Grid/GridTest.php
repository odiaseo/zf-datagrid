<?php
namespace SynergyDataGridTest\Grid;

use Doctrine\ORM\QueryBuilder;
use SynergyDataGrid\Grid\GridType\BaseGrid;
use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
use SynergyDataGrid\View\Helper\DisplayGrid;
use SynergyDataGridTest\BaseTestClass;
use SynergyDataGridTest\Entity\TestBrand;
use SynergyDataGridTest\Entity\TestStore;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Stdlib\Parameters;
use Laminas\View\Renderer\PhpRenderer;

/**
 * @backupGlobals disabled
 */
class GridTest extends BaseTestClass
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
        $this->assertInstanceOf(DoctrineORMGrid::class, $this->_grid, 'Invalid grid instance created');
    }

    public function testGridIdentity()
    {
        $entityClassName = TestBrand::class;
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
        $entityClassName = TestBrand::class;
        $this->_grid->setGridIdentity($entityClassName, 'testbrand');

        $config                        = $this->_grid->getConfig();
        $config['first_data_as_local'] = false;
        $this->_grid->setConfig($config);
        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('ViewHelperManager')->get('displayGrid');
        $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);

        $return = $viewHelper->initGrid($this->_grid);
        $this->assertTrue(count($return) > 0);
    }

    public function testGridDisplayWithLocalData()
    {
        $entityClassName = TestBrand::class;
        $this->_grid->setGridIdentity($entityClassName, 'testbrand');

        $config                        = $this->_grid->getConfig();
        $config['first_data_as_local'] = true;
        $this->_grid->setConfig($config);
        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('ViewHelperManager')->get('displayGrid');
        $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);
        $return = $viewHelper->__invoke($this->_grid, true);
        $this->assertInternalType('string', $return);
    }

    public function testSubGrid()
    {
        $entityClassName = TestStore::class;
        $this->_grid->setGridIdentity($entityClassName);

        $config                        = $this->_grid->getConfig();
        $config['first_data_as_local'] = false;
        $this->_grid->setConfig($config);
        /** @var $viewHelper DisplayGrid */
        $viewHelper = $this->_serviceManager->get('ViewHelperManager')->get('displayGrid');
        $this->assertInstanceOf(DisplayGrid::class, $viewHelper);

        $return = $viewHelper->initGrid($this->_grid);
        $this->assertTrue(count($return) > 0);

        $mapping = $this->_grid->getModel()
            ->getEntityManager()
            ->getClassMetadata($this->_grid->getEntity());

        $this->_grid->setGridColumns();

        $subGrid = $this->_grid->createSubGridAsGrid($mapping->associationMappings['testBrands']);
        $this->assertInstanceOf(BaseGrid::class, $subGrid);
    }

    public function testGridData()
    {
        $entityClassName = TestBrand::class;
        $this->_grid->setGridIdentity($entityClassName);

        $this->_grid->setCustomQueryBuilder(
            new QueryBuilder($this->_grid->getModel()->getEntityManager())
        );

        $this->_grid->setDatatype('local');
        $this->_grid->getOptions();

        $params = array(
            'page'                     => 1,
            'rows'                     => 25,
            'sord'                     => 'asc',
            'sidx'                     => 'id',
            BaseGrid::GRID_IDENTIFIER  => $this->_grid->getId(),
            BaseGrid::ENTITY_IDENTFIER => $this->_grid->getEntity()
        );

        $request    = new Request();
        $parameters = new Parameters($params);
        $request->setPost($parameters);

        $this->_grid->prepareGridData($request);

        /** @var $viewHelper \SynergyDataGrid\View\Helper\DisplayGrid */
        $viewHelper = $this->_serviceManager->get('ViewHelperManager')->get('displayGrid');

        $viewHelper->setView(new PhpRenderer());
        $data = $viewHelper($this->_grid, false);

        $this->assertArrayHasKey('html', $data);
        $this->assertArrayHasKey('js', $data);
        $this->assertArrayHasKey('onLoad', $data);
    }

}