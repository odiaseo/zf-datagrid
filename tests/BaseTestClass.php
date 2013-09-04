<?php
    namespace SynergyDataGrid\Test;

    use DoctrineORMModule\Options\EntityManager;

    class BaseTestClass extends \PHPUnit_Framework_TestCase
    {
        public $_sm;
        /** @var $app \Zend\Mvc\Application */
        protected $_app;

        protected $_em;

        public function setUp()
        {
            $this->_app = $GLOBALS['application'];
            $this->_sm  = $this->_app->getServiceManager();

        }
    }