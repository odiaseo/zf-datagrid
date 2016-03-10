<?php
namespace SynergyDataGrid\Grid;

use SynergyDataGrid\Grid\GridType\BaseGrid;
use SynergyDataGrid\Grid\Toolbar\Item;

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
class Toolbar extends Property
{
    const TOOLBAR_PREFIX_BOTTOM = 'tb_';
    const TOOLBAR_PREFIX_TOP    = 't_';
    const POSITION_BOTH         = 'both';
    const POSITION_TOP          = 'top';
    const POSITION_BOTTOM       = 'bottom';
    public static $toolbarItemCount = 0;
    protected $_items = array();
    protected $_grid;
    private $_id;
    private $_position;

    /**
     * Set up base NavGrid options
     *
     * @param BaseGrid $grid
     * @param array $buttons
     * @param string $position
     * @param string $configPosition
     */
    public function __construct(
        BaseGrid $grid, $buttons = array(), $position = self::POSITION_BOTTOM, $configPosition = self::POSITION_BOTTOM
    )
    {
        switch ($position) {
            case self::POSITION_BOTTOM:
                $prefix = ($configPosition == self::POSITION_BOTTOM) ? self::TOOLBAR_PREFIX_TOP
                    : self::TOOLBAR_PREFIX_BOTTOM;
                break;
            default:
                $prefix = self::TOOLBAR_PREFIX_TOP;
        }

        $this->_position = $position;
        $this->_id       = $prefix . $grid->getId();
        $this->setGrid($grid);

        $entityId = $grid->getEntityId();
        foreach ($buttons['global'] as $item) {
            $this->addToolbarItem($item);
        }

        if (isset($buttons['specific'][$entityId])) {
            foreach ($buttons['specific'][$entityId] as $item) {
                $this->addToolbarItem($item);
            }
        }
    }

    public function setGrid($grid)
    {
        $this->_grid = $grid;

        return $this;
    }

    public function getGrid()
    {
        return $this->_grid;
    }

    public function setItems($items)
    {
        $this->_items = $items;
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getId()
    {
        return $this->_id;
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

    public function addToolbarItem($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = 'item_' . self::$toolbarItemCount;
        }
        self::$toolbarItemCount++;
        $this->_items[] = new Item($this->_id, $data);
    }
}
