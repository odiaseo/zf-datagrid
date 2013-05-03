<?php
    namespace SynergyDataGrid\Grid;

    use SynergyDataGrid\Grid\Toolbar\Item;

    class Toolbar extends Property
    {
        const TOOLBAR_PREFIX_BUTTOM = 'tb_';
        const TOOLBAR_PREFIX_TOP    = 't_';
        protected $_items = array();
        protected $_grid;
        private $_id;

        /**
         * Set up base NavGrid options
         *
         * @param \SynergyDataGrid\Grid\JqGridFactory $grid    JqGrid instance
         *
         * @return void
         */
        public function __construct($grid, $buttons = array())
        {
            $this->_id = self::TOOLBAR_PREFIX_TOP . $grid->getId();
            $this->setGrid($grid);

            $gridIdentity = str_replace(JqGridFactory::ID_PREFIX, '', $grid->getId());
            $global       = isset($buttons['global']) ? $buttons['global'] : array();
            $specific     = isset($buttons['specific'][$gridIdentity]) ? $buttons['specific'][$gridIdentity] : array();

            $tools = array_merge($global, $specific);

            foreach ($tools as $item) {
                $this->addToolbarItem($item);
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

        public function addToolbarItem($data)
        {
            $this->_items[] = new Item($data);
        }
    }