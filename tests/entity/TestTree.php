<?php
    namespace SynergyDataGrid\Test\Entity;

    use Gedmo\Mapping\Annotation as Gedmo,
        Doctrine\ORM\Mapping as ORM;

    /**
     *
     * @ORM\MappedSuperclass(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
     * @Gedmo\Tree(type="nested")
     *
     */
    class TestTree
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
        protected $label = '';
        /**
         * @ORM\Column(type="string")
         */
        protected $description = '';
        /**
         * @Gedmo\Slug(fields={"title"})
         * @ORM\Column(name="slug", type="string")
         */
        protected $slug;
        /**
         * @Gedmo\TreeLeft
         * @ORM\Column(name="lft", type="integer")
         */
        protected $lft;
        /**
         * @Gedmo\TreeRight
         * @ORM\Column(name="rgt", type="integer")
         */
        protected $rgt;
        /**
         * @Gedmo\TreeLevel
         * @ORM\Column(type="integer")
         */
        protected $level;
        /**
         * @Gedmo\TreeRoot
         * @ORM\Column(name="root", type="integer", nullable=true)
         */
        protected $root;
    }