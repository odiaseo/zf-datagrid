<?php
namespace SynergyDataGrid\Grid;

/*
 * This file is part of the Synergy package.
 *
 * (c) Pele Odiase <info@rhemastudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 *
 */
use SynergyDataGrid\Util\ArrayUtils;

/**
 * Base class for implementing functionality to work with all grid-related objects
 *
 * @method \SynergyDataGrid\Grid\GridType\BaseGrid getGrid()
 * @author  Pele Odiase
 * @package mvcgrid
 */
abstract class Base
{

    /**
     * An array of options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Override set to allow access to all possible options
     *
     * @param string $name option name
     * @param mixed $value option value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setOption($name, $value);
    }

    /**
     * Override get to allow access to all possible options
     *
     * @param string $name option name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getOption($name);
    }

    /**
     * Get array of all options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set all options at once
     *
     * @param array $options of all options
     *
     * @return mixed
     */
    public function setOptions($options = null)
    {
        if (is_array($options)) {
            foreach ($options as $k => $v) {
                $this->setOption($k, $v);
            }
        }

        return $this;
    }

    /**
     * Merge existing options with array of new options
     *
     * @param array $options array of all options
     *
     * @return mixed
     */
    public function mergeOptions(array $options = array())
    {
        $utils          = new ArrayUtils();
        $oldOptions     = $this->getOptions();
        $this->_options = $utils->arrayMergeRecursiveCustom($oldOptions, $options);

        return $this;
    }

    /**
     * Get single option
     *
     * @param string $name name of option
     *
     * @return mixed
     */
    public function getOption($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } else {
            return false;
        }
    }

    /**
     * Set single option
     *
     * @param string $name name of option
     * @param mixed $value value of option
     *
     * @return mixed
     */
    public function setOption($name, $value)
    {
        $this->_options[$name] = $value;

        return $this;
    }

    /**
     * Magic method to work with all object options without necessity to create tons of methods. Provide overloading features.
     *
     * @param $name
     * @param $arguments
     *
     * @return bool|mixed|Base
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $retv = true;
        // overload nonexistant set... methods to set particular object option
        if (substr($name, 0, 3) == 'set' && is_array($arguments) && count($arguments) == 1) {
            $value = $arguments[0];
            // overload nonexistant set...IfNotSet methods to set particular object option only if there is no such an option yet
            if (substr($name, -8) == 'IfNotSet') {
                $propertyName = lcfirst(substr($name, 3, strlen($name) - 11));
                $option       = $this->getOption($propertyName);
                if (!$option) {
                    $this->setOption($propertyName, $value);
                }
            } else {
                $propertyName = lcfirst(substr($name, 3));
                $this->setOption($propertyName, $value);
            }
            // update ColModel array of option for jqGrid object
            if (substr(get_class($this), -7) == '\Column') {
                $this->getGrid()->setColModel($this->getGrid()->getColumns());
            }
            $retv = $this;
            // overload nonexistant get... methods to get particular object option
        } elseif (substr($name, 0, 3) == 'get') {
            $retv = $this->getOption(lcfirst(substr($name, 3)));
            // overload nonexistant merge... methods to merge option arrays
        } elseif (substr($name, 0, 5) == 'merge' && is_array($arguments) && count($arguments) == 1) {
            $utils         = new ArrayUtils();
            $methodGet     = 'get' . ucfirst(substr($name, 5));
            $methodSet     = 'set' . ucfirst(substr($name, 5));
            $oldParameters = $this->$methodGet();
            if (gettype($oldParameters) == 'object') {
                /** @var $oldParameters \SynergyDataGrid\Grid\Property */
                $oldParameters = $oldParameters->getOptions();
            } else {
                if (!is_array($oldParameters)) {
                    $oldParameters = array();
                }
            }
            /** @var $oldParameters array */
            $this->$methodSet($utils->arrayMergeRecursiveCustom($oldParameters, $arguments[0]));
            // throw an exception if method name doesn't match any known pattern
        } elseif (!method_exists($this, $name)) {
            throw new \Exception(sprintf('The required method "%s" does not exist for %s', $name, get_class($this)));
        }

        return $retv;
    }

    /**
     * Utility method to get all object properties as an array
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}