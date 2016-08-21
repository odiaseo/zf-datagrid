<?php
namespace SynergyDataGridTest\Controller;

use SynergyDataGrid\Controller\GridController;
use SynergyDataGridTest\Bootstrap;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Console\Router\RouteMatch;
use Zend\View\Model\ViewModel;

/**
 * Class GridControllerTest
 * @package SynergyDataGridTest\Controller
 */
class GridControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $serviceManager;

    public function setUp()
    {

        $this->serviceManager = Bootstrap::getServicemanager();
    }

    public function testControllerInstance()
    {
        /** @var GridController $controller */
        $controller = $this->serviceManager->get('ControllerManager')->get(GridController::class);
        $this->assertInstanceOf(GridController::class, $controller);
    }

    /**
     * @dataProvider getRestMethods
     */
    public function testRestMethods($method, $id, $data = [])
    {

        /** @var GridController $controller */
        $controller = $this->serviceManager->get('ControllerManager')->get(GridController::class);

        $params = [
            'entity' => 'test-brand',
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

        $this->assertInstanceOf(ViewModel::class, $model);
    }

    public function getRestMethods()
    {
        return [
            ['GET', ''],
            ['POST', ''],
            ['POST', '1', []],
            ['PUT', '', []],
            ['POST', '1', ['title' => 'test item', 'description' => 'phpunit']],
            ['PUT', '1', ['title' => 'new title']],
            ['PUT', '', ['title' => 'new title']],
            ['DELETE', ''],
            ['DELETE', '1'],
        ];
    }
}
