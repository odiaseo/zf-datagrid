<?php
namespace SynergyDataGrid\Grid;

    /**
     * This file is part of the Synergy package.
     *
     * (c) Pele Odiase <info@rhemastudio.com>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     *
     * @author  Pele Odiase
     * @license http://opensource.org/licenses/BSD-3-Clause
     *
     */
/**
 * Property class to handle work with some JqGrid properties
 *
 * @method getOptions()
 * @author  Pele Odiase
 * @package mvcgrid
 */
class Property extends Base
{
    /**
     * Base object for property
     *
     * @var mixed
     */
    protected $_owner;

    /**
     * Property name
     *
     * @var string
     */
    protected $_property;

    /**
     * Set up base Property options
     *
     * @param       $owner
     * @param array $options
     */
    public function __construct($owner, $options = array())
    {
        $this->setOwner($owner);
        $this->setOptions($options);
    }

    /**
     * Merge arrays of options for current property
     *
     * @param array $options array of property options
     *
     * @return mixed
     * @throws \Exception
     */
    public function mergeOptions(array $options = array())
    {
        $merged = parent::mergeOptions($options);
        $owner = $this->getOwner();

        if ($this->getProperty() && $owner) {
            $method = 'set' . ucfirst($this->getProperty());
            $owner->$method($merged);
        }

        if ($owner && substr(get_class($owner), -7) == '\Column') {
            $owner->getGrid()->setColModel($owner->getGrid()->getColumns());
        }

        return $merged;
    }

    /**
     * Get owner property
     *
     * @return \SynergyDataGrid\Grid\Property
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Set owner property
     *
     * @param mixed $owner owner to set
     *
     * @return \SynergyDataGrid\Grid\Property
     */
    public function setOwner($owner)
    {
        $this->_owner = $owner;

        return $this;
    }

    /**
     * Get Property name
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
     * Set Property name
     *
     * @param $property
     *
     * @return $this
     */
    public function setProperty($property)
    {
        $this->_property = $property;

        return $this;
    }
}