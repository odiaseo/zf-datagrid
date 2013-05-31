<?php
    namespace SynergyDataGrid\Grid;

    use SynergyDataGrid\Grid\Toolbar\Item;

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
         * @param \SynergyDataGrid\Grid\JqGridFactory $grid    JqGrid instance
         *
         * @return void
         */
        public function __construct(JqGridFactory $grid, $buttons = array(), $position = self::POSITION_BOTTOM)
        {
            switch ($position) {
                case self::POSITION_BOTTOM:
                    $prefix = self::TOOLBAR_PREFIX_BOTTOM;
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