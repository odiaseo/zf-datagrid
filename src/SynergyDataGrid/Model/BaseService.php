<?php
namespace SynergyDataGrid\Model;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;


/**
 * Class to handle base functionality to work with Doctrine Models
 *
 * @author Pele Odiase
 * @package mvcgrid
 */
class BaseService implements ServiceManagerAwareInterface
{
    /**
     * Entity manager
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;
    /**
     * Entity repository
     *
     * @var Doctrine\ORM\EntityRepository
     */
    protected $_repository;
    /**
     * Entity class name
     *
     * @var string
     */
    protected $_entityClass;
    /**
     * Model alias
     *
     * @var string
     */
    protected $_alias;
    /**
     * Class metadata
     *
     * @var Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $_classMetadata;
    /**
     * @var ServerManager
     */
    protected $_sm;

    /**
     * Set up base BaseService options
     *
     * @param string $entityClass entity class
     *
     * @return void
     */
    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     * @param ServiceManager $serviceManager
     *
     * @return BaseService
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->_sm = $serviceManager;
        return $this;
    }

    /**
     * Service Manager
     *
     * @return ServiceManager sm
     */
    public function getServiceManager()
    {
        return $this->_sm;
    }

    public function getService($entityClass)
    {
        $this->setEntityClass($entityClass);
        $this->setRepository($this->getEntityManager()->getRepository($entityClass));
        $this->setClassMetadata($this->getEntityManager()->getClassMetadata($entityClass));

        $alias = 'e'; //strtolower(basename($entityClass));
        $this->setAlias($alias);

        return $this;
    }

    /**
     * Find object by id in repository
     *
     * @param int @id id of an object
     *
     * @return Doctrine\ORM\Mapping\Entity
     */
    public function findObject($id = 0)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Remove record by id
     *
     * @param int @id id of an object
     *
     * @return bool
     */
    public function remove($id = 0)
    {
        $retv = false;
        if ($id) {
            $object = $this->findObject($id);
            if ($object) {
                try {
                    $this->getEntityManager()->remove($object);
                    $this->getEntityManager()->flush();
                    $retv = true;
                } catch (\Exception $e) {
                    $this->recoverEntityManager();
                    throw new \Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
        return $retv;
    }

    /**
     * Save given entity
     *
     * @param Doctrine\ORM\Mapping\Entity $entity entity to save
     *
     * @return Doctrine\ORM\Mapping\Entity
     */
    public function save($entity)
    {
        try {
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->recoverEntityManager();
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $entity;
    }

    /**
     * Since Doctrine closes the EntityManager after a Exception, we have to create
     * a fresh copy (so it is possible to save logs in the current request)
     *
     * @return void
     */
    private function recoverEntityManager()
    {
        $this->setEntityManager(\Doctrine\ORM\EntityManager::create(
            $this->getEntityManager()->getConnection(),
            $this->getEntityManager()->getConfiguration()
        ));
    }

    /**
     * Get entity repository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->_repository;
    }

    /**
     * Set entity repository
     *
     * @param Doctrine\ORM\EntityRepository $repository repository to set
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function setRepository($repository)
    {
        $this->_repository = $repository;
        return $this;
    }

    /**
     * Get entity class name
     *
     * @return sting
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * Set entity class name
     *
     * @param string $entityClass entity class name to set
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function setEntityClass($entityClass)
    {
        $this->_entityClass = $entityClass;
        return $this;
    }

    /**
     * Get entity manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    /**
     * Set entity manager
     *
     * @param Doctrine\ORM\EntityManager $em entity manager to set
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function setEntityManager($em)
    {
        $this->_em = $em;
        return $this;
    }

    /**
     * Get model alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Set model alias
     *
     * @param string $alias model alias to set
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function setAlias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }

    /**
     * Get class metadata
     *
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_classMetadata;
    }

    /**
     * Set class metadata
     *
     * @param Doctrine\ORM\Mapping\ClassMetadata $classMetadata class metadata
     *
     * @return \SynergyDataGrid\Model\BaseService
     */
    public function setClassMetadata($classMetadata)
    {
        $this->_classMetadata = $classMetadata;
        return $this;
    }

}