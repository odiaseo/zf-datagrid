<?php
namespace SynergyDataGrid\Grid\Toolbar;

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

use SynergyDataGrid\Grid\Property;
use Zend\Json\Expr;

/**
 * Class Item
 *
 * @package SynergyDataGrid\Grid\Toolbar
 */
class Item extends Property
{
    protected $_id;
    protected $_title;
    protected $_icon = 'ui-icon-document';
    protected $_callback;
    protected $_position;
    protected $_class = 'toolbar-item';
    protected $_attributes = '';
    protected $_onLoad;
    protected $_order = 1;

    public function __construct($parentId, array $options)
    {

        if (!isset($options['id'])) {
            $this->_id = 'btn_' . md5($parentId . json_encode($options));
        } else {
            $this->_id = 'btn_' . $parentId . '_' . $options['id'];
        }

        $this->setOptions($options);
    }

    public function setOptions($options = null)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public function setCallback($callback)
    {
        $this->_callback = is_string($callback) ? new Expr($callback) : $callback;

        return $this;
    }

    public function getCallback()
    {
        return $this->_callback;
    }

    public function setIcon($icon)
    {
        $this->_icon = $icon;

        return $this;
    }

    public function getIcon()
    {
        return $this->_icon;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setAttributes($attributes)
    {
        $attr = array();
        foreach ($attributes as $k => $v) {
            $attr[] = "{$k}='{$v}'";
        }
        $this->_attributes = implode(' ', $attr);

        return $this;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setClass($class)
    {
        $this->_class = trim($class . ' ' . 'toolbar-item');

        return $this;
    }

    public function getClass()
    {
        return $this->_class;
    }

    public function setPosition($position)
    {
        $this->_position = $position;

        return $this;
    }

    public function getPosition()
    {
        return $this->_position;
    }

    public function setOnLoad($onLoad)
    {
        if (is_callable($onLoad)) {
            $this->_onLoad = $onLoad($this->_id);
        } else {
            $this->_onLoad = $onLoad;
        }

        return $this;
    }

    public function getOnLoad()
    {
        return $this->_onLoad;
    }
}
