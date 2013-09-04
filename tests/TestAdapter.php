<?php
    namespace SynergyDataGrid\Test;

    use SynergyDataGrid\Grid\Adapter\ORMQueryAdapter;

    class TestAdapter extends ORMQueryAdapter
    {


        public function count()
        {
            return count($this->getItems(0, 20));
        }

        public function getItems($offset, $itemCountPerPage)
        {
            $this->createQuery();

            return array(
                (object)array('id' => 1, 'title' => 'Argos', 'description' => 'my Store description for argos'),
                (object)array('id' => 2, 'title' => 'Tesco', 'description' => 'my Store description for Tesco'),
                (object)array('id' => 3, 'title' => 'Little Woods', 'description' => 'my Store description for Little woods'),
                (object)array('id' => 4, 'title' => 'Amazon', 'description' => 'description for Amazon'),
            );
        }
    }