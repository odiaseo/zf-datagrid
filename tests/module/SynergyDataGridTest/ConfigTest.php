<?php

namespace SynergyDataGridTest;

use SynergyDataGrid\Model\Config\ModelOptions;

/**
 * Class run generic tests on entites. Verifies simple getters/setters
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected $serviceManager;
    protected $stack = [];

    public function setUp()
    {
        $this->stack = [
            ModelOptions::class,
        ];

        $this->serviceManager = Bootstrap::getServicemanager();
    }

    public function testGenericConfigObjects()
    {
        foreach ($this->stack as $declaredClass) {
            $reflectionClass = new \ReflectionClass($declaredClass);

            if ($reflectionClass->IsInstantiable()) {
                $class      = new $declaredClass;
                $methods    = $reflectionClass->getMethods();
                $methodList = [];
                /** @var \ReflectionMethod $method */
                foreach ($methods as $method) {
                    $methodName   = $method->getName();
                    $methodParams = $method->getParameters();

                    if (preg_match('/^set/', $methodName)) {
                        $attr               = lcfirst(substr($methodName, 3));
                        $methodList [$attr] = 'testdata ';
                        if (count($methodParams) === 1) {
                            /** @var \ReflectionParameter $param */
                            $param = current($methodParams);

                            if ($param->allowsNull()) {
                                $class->$methodName([]);
                                $this->assertTrue(true);
                            }
                        }
                    }

                    if (preg_match('/^(get|is)/', $methodName)) {
                        $this->assertTrue(true);
                        $class->$methodName('test');
                    }
                }
            }
        }
    }
}
