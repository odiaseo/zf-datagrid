<?php
namespace SynergyDataGridTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
    protected $title = 'test';
    /**
     * @ORM\Column(type="string")
     */
    protected $description = 'sample description';
    /**
     * @ORM\OneToMany(targetEntity="TestStore", cascade="persist", mappedBy="testBrands")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=true)
     */
    protected $stores;

    public function __construct()
    {
        $this->stores = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        return $this->stores;
    }

    /**
     * @param mixed $stores
     */
    public function setStores($stores)
    {
        $this->stores = $stores;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function add($item)
    {
        $this->stores->add($item);
    }
}
