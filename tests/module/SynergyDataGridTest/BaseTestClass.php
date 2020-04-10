<?php
namespace SynergyDataGridTest;

use SynergyDataGrid\Controller\GridController;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;

class BaseTestClass extends \PHPUnit\Framework\TestCase
{
    /** @var \Laminas\ServiceManager\ServiceManager */
    protected $_serviceManager;

    /** @var \Laminas\Mvc\Application */
    protected $_app;

    /** @var \Doctrine\Orm\EntityManager */
    protected $_em;

    protected $controller;

    /** @var \Laminas\Http\PhpEnvironment\Request $request */
    protected $request;

    protected $response;

    /** @var  \Laminas\Router\RouteMatch */
    protected $routeMatch;

    /** @var  \Laminas\Mvc\MvcEvent */
    protected $event;

    public function setUp()
    {
        parent::setUp();
        $this->_serviceManager = Bootstrap::getServiceManager();

        $this->controller = new GridController($this->_serviceManager);
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array());
        $this->event      = new MvcEvent();
        $config           = $this->_serviceManager->get('Config');
        $routerConfig     = isset($config['router']) ? $config['router'] : array();
        $router           = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($this->_serviceManager);
    }
}