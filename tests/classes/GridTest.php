<?php
    namespace SynergyDataGrid\Test\Classes;

    use Doctrine\ORM\QueryBuilder;
    use SynergyDataGrid\Grid\Adapter\ORMQueryAdapter;
    use SynergyDataGrid\Grid\GridType\BaseGrid;
    use SynergyDataGrid\Grid\GridType\DoctrineORMGrid;
    use SynergyDataGrid\Test\BaseTestClass;
    use SynergyDataGrid\Test\TestAdapter;
    use Zend\Http\PhpEnvironment\Request;
    use Zend\Stdlib\Parameters;
    use Zend\View\Model\ViewModel;
    use Zend\View\Renderer\PhpRenderer;

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
            $this->_grid = $this->_sm->get('jqgrid');
            $this->_grid->setUrl('/grid-test');
        }

        public function testGridFactory()
        {
            $this->assertInstanceOf('\SynergyDataGrid\Grid\GridType\DoctrineORMGrid', $this->_grid,
                'Invalid grid instance created');
        }

        public function testGridIdentity()
        {
            $entityClassName = '\SynergyDataGrid\Test\Entity\TestBrand';
            $this->_grid->setGridIdentity($entityClassName);

            $service = $this->_grid->getService();
            $this->assertInstanceOf('\SynergyDataGrid\Model\BaseService', $service);

            $className = $this->_grid->getEntity();
            $this->assertSame($className, $entityClassName);

            $em = $this->_grid->getObjectManager();
            $this->assertInstanceOf('\Doctrine\ORM\EntityManager', $em);

            $isTreeGrid = $this->_grid->getIsDetailGrid();
            $this->assertFalse($isTreeGrid);
        }

        public function testGridDisplay()
        {
            $entityClassName = '\SynergyDataGrid\Test\Entity\TestBrand';
            $this->_grid->setGridIdentity($entityClassName, 'testbrand');

            $config                        = $this->_grid->getConfig();
            $config['first_data_as_local'] = false;
            $this->_grid->setConfig($config);

            $viewHelper = $this->_sm->get('viewhelpermanager')->get('displayGrid');
            $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);

            list($onLoad, $js, $html) = $viewHelper->initGrid($this->_grid);
        }

        public function testSubGrid()
        {
            $entityClassName = '\SynergyDataGrid\Test\Entity\TestStore';
            $this->_grid->setGridIdentity($entityClassName);

            $config                        = $this->_grid->getConfig();
            $config['first_data_as_local'] = false;
            $this->_grid->setConfig($config);

            $viewHelper = $this->_sm->get('viewhelpermanager')->get('displayGrid');
            $this->assertInstanceOf('\SynergyDataGrid\View\Helper\DisplayGrid', $viewHelper);

            list($onLoad, $js, $html) = $viewHelper->initGrid($this->_grid);

            $mapping = $this->_grid->getService()
                ->getEntityManager()
                ->getClassMetadata($this->_grid->getEntity());

            $this->_grid->setGridColumns();

            $subGrid = $this->_grid->createSubGridAsGrid($mapping->associationMappings['testBrands']);
            $this->assertInstanceOf('\SynergyDataGrid\Grid\GridType\BaseGrid', $subGrid);

        }

        public function testGridData()
        {
            $entityClassName = '\SynergyDataGrid\Test\Entity\TestBrand';
            $this->_grid->setGridIdentity($entityClassName);

            $this->_grid->setCustomQueryBuilder(
                new QueryBuilder($this->_grid->getService()->getEntityManager())
            );

            $this->_grid->setDatatype('local');
            $gridOptions = $this->_grid->getOptions();
            $params      = array(
                'page'                     => 1,
                'rows'                     => 25,
                BaseGrid::GRID_IDENTIFIER  => $this->_grid->getId(),
                BaseGrid::ENTITY_IDENTFIER => $this->_grid->getEntity()
            );

            $request    = new Request();
            $parameters = new Parameters($params);
            $request->setPost($parameters);


            $this->_grid->prepareGridData($request);

            $viewHelper = $this->_sm->get('viewhelpermanager')->get('displayGrid');

            $viewHelper->setView(new PhpRenderer());

        }

    }