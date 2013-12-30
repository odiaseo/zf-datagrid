<?php
    namespace SynergyDataGridTest\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Gedmo\Mapping\Annotation as Gedmo;

    /**
     * TestBrand
     *
     * @ORM\Entity
     * @ORM\Table(name="Test_Brand")
     *
     */
    class TestBrand
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer");
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;
        /**
         * @ORM\Column(type="string")
         */
        protected $title;
        /**
         * @ORM\Column(type="string")
         */
        protected $description = '';

    }