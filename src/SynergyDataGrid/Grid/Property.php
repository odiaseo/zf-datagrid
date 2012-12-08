<?php
namespace SynergyDataGrid\Grid;

/**
 * Property class to handle work with some JqGrid properties
 *
 * @author Pele Odiase
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
     * @param mixed $owner object owner of property
     * @param array $options array of property options
     * @return void
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
     * @return array
     */
    public function mergeOptions($options = array())
    {
        $merged = parent::mergeOptions($options);
        if ($this->getProperty() && $this->getOwner()) {
            $method = 'set' . ucfirst($this->getProperty());
            $this->getOwner()->$method($merged);
        }
        if (substr(get_class($this->getOwner()),-7) == '\Column') {
            $this->getOwner()->getGrid()->setColModel($this->getOwner()->getGrid()->getColumns());
        }
        return $merged;
    }
    
    /**
     * Get owner property
     * 
     * @return mixed
     */
    public function getOwner()
    {
        return $this->_owner;
    }
    
    /**
     * Set owner property
     * 
     * @param mixed $owner owner to set
     * @return \SynergyDataGrid\Property
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
     * @param string $proeprty Property name
     * @return \SynergyDataGrid\Property
     */
    public function setProperty($property)
    {
        $this->_property = $property;
        return $this;
    }
}