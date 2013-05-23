<?php
    namespace SynergyDataGrid\Grid\Toolbar;

    use SynergyDataGrid\Grid\Property;

    class Item extends Property
    {
        protected $_id;
        protected $_title;
        protected $_icon;
        protected $_callback;
        protected $_position;
        protected $_class = 'toolbar-item';
        protected $_attributes = '';

        public function __construct(array $options)
        {
            $this->_title    = isset($options['title']) ? $options['title'] : '';
            $this->_icon     = isset($options['icon']) ? $options['icon'] : 'ui-icon-document';
            $this->_callback = isset($options['callback']) ? $options['callback'] : '';
            $this->_position = isset($options['position']) ? $options['position'] : '';
            $this->_class .= isset($options['class']) ? ' ' . $options['class'] : '';


            if (!isset($options['id'])) {
                $this->_id = 'btn_' . md5(json_encode($options));
            } else {
                $this->_id = 'btn_' . $options['id'];
            }

            if (isset($options['attributes'])) {
                foreach ($options['attributes'] as $k => $v) {
                    $attr[] = "{$k}='{$v}'";
                }
                $this->_attributes = implode(' ', $attr);
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

        public function setAttributes($attributes)
        {
            $this->_attributes = $attributes;
            return $this;
        }

        public function getAttributes()
        {
            return $this->_attributes;
        }

        public function setClass($class)
        {
            $this->_class = $class;
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


    }