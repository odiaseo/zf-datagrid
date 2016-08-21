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
    protected $title = "argos";
    /**
     * @ORM\Column(type="text")
     */
    protected $description = 'argos super store';
    /**
     * @ORM\ManyToOne(targetEntity="TestBrand", cascade="persist", inversedBy="stores")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true)
     */
    protected $testBrands;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = 1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $counts = 0;
    /**
     * @var \datetime createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;
    /**
     * @var \datetime dateOfBirth
     *
     * @ORM\Column(type="date", name="dob", nullable=true)
     */
    protected $dateOfBirth;

    public function __construct()
    {
        $this->createdAt  = new \DateTime();
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

    /**
     * @return mixed
     */
    public function getTestBrands()
    {
        return $this->testBrands;
    }

    /**
     * @param mixed $testBrands
     */
    public function setTestBrands($testBrands)
    {
        $this->testBrands = $testBrands;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * @param mixed $counts
     */
    public function setCounts($counts)
    {
        $this->counts = $counts;
    }

    /**
     * @return \datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \datetime
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param \datetime $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }
}
