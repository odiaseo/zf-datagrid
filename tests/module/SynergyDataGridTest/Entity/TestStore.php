<?php
    namespace SynergyDataGridTest\Entity;

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * TestStore
     *
     * @ORM\Entity
     * @ORM\Table(name="Test_Store")
     *
     */
    class TestStore
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
         * @ORM\Column(type="text")
         */
        protected $description = '';
        /**
         * @ORM\ManyToOne(targetEntity="TestBrand")
         * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true)
         */
        protected $testBrands;

        /**
         * @ORM\Column(type="boolean")
         */
        protected $active;

        /**
         * @ORM\Column(type="integer")
         */
        protected $counts;
        /**
         * @var \datetime createdAt
         *
         * @ORM\Column(type="datetime", name="created_at")
         */
        protected $createdAt;
        /**
         * @var \datetime dateOfBirth
         *
         * @ORM\Column(type="date", name="dob")
         */
        protected $dateOfBirth;

        public function __construct()
        {
            $this->testBrands = new ArrayCollection();
        }

    }