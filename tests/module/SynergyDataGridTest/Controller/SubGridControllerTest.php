<?php
namespace SynergyDataGridTest\Controller;

use SynergyDataGrid\Controller\SubGridController;
use SynergyDataGridTest\Bootstrap;
use SynergyDataGridTest\Entity\TestBrand;
use SynergyDataGridTest\Entity\TestStore;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\Console\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Laminas\View\Variables;

/**
 * Class SubGridControllerTest
 * @package SynergyDataGridTest\Controller
 */
class SubGridControllerTest extends \PHPUnit\Framework\TestCase
{
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServicemanager();
        $this->createRecord();
    }

    public function testControllerInstance()
    {
        /** @var SubGridController $controller */
        $controller = $this->serviceManager->get('ControllerManager')->get(SubGridController::class);
        $this->assertInstanceOf(SubGridController::class, $controller);
    }

    /**
     * @dataProvider getRestMethods
     */
    public function testRestMethods($method, $id, $data = [])
    {

        /** @var SubGridController $controller */
        $controller = $this->serviceManager->get('ControllerManager')->get(SubGridController::class);

        $params = [
            'entity'    => 'test-brand',
            'fieldName' => 'stores',
            'subgridid' => 1
        ];

        if ($id) {
            $params['id'] = $id;
        }

        $request = new Request();
        $router  = new RouteMatch($params);
        $request->setMethod($method);

        if (empty($id) or is_array($id)) {
            $request->setRequestUri('/synergydatagrid/crud/test-brand');
        } else {
            $request->setRequestUri('/synergydatagrid/crud/test-brand/' . $id);
        }

        if ($data) {
            $request->setContent(http_build_query($data));
        }

        $controller->getEvent()->setRouteMatch($router);
        $model = $controller->dispatch($request);

        /** @var Variables $vars */
        $data = $model->getVariables();

        $this->assertInstanceOf(ViewModel::class, $model);

        if ('GET' == $method) {
            $this->assertArrayHasKey('page', $data);
            $this->assertArrayHasKey('total', $data);
            $this->assertArrayHasKey('records', $data);
            $this->assertArrayHasKey('rows', $data);
        }
    }

    private function createRecord()
    {
        $manager = $this->serviceManager->get('doctrine.entitymanager.orm_default');
        $store   = new TestStore();
        $brand   = new TestBrand();
        $brand->setStores($store);

        $manager->persist($store);
        $manager->flush();
    }

    public function getRestMethods()
    {
        return [
            ['GET', ''],
            ['POST', ''],
            ['POST', '1', ['title' => 'test item', 'description' => 'phpunit']],
            ['PUT', '', ['title' => 'new title']],
            ['PUT', '1', ['title' => 'new title']],
            ['DELETE', ''],
            ['DELETE', '1', []],
        ];
    }
}
