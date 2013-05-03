<?php
    namespace SynergyDataGrid\Grid\Toolbar;

    use SynergyDataGrid\Grid\Property;

    class Item extends Property
    {
        protected $_id;
        protected $_title;
        protected $_icon;
        protected $_callback;

        public function __construct(array $attributes)
        {
            $this->_title    = isset($attributes['title']) ? $attributes['title'] : '';
            $this->_icon     = isset($attributes['icon']) ? $attributes['icon'] : '';
            $this->_callback = isset($attributes['callback']) ? $attributes['callback'] : '';

            if (!isset($attributes['id'])) {
                $this->_id = 'btn_' . md5(json_encode($attributes));
            } else {
                $this->_id = $attributes['id'];
            }
        }

        public function setCallback($callback)
        {
            $this->_callback = $callback;

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

    }