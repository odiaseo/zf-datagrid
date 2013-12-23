<?php
namespace SynergyDataGrid\Service;

use SynergyDataGrid\Model\Config\ModelOptions;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Json\Json;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class BaseService
    implements ServiceManagerAwareInterface
{
    protected $_expression
        = array(
            'eq' => 'EQUAL',
            'ne' => 'NOT_EQUAL',
            'lt' => 'LESS_THAN',
            'le' => 'LESS_THAN_OR_EQUAL',
            'gt' => 'GREATER_THAN',
            'ge' => 'GREATER_THAN_OR_EQUAL',
            'bw' => 'BEGIN_WITH',
            'bn' => 'NOT_BEGIN_WITH',
            'in' => 'IN',
            'ni' => 'NOT_IN',
            'ew' => 'END_WITH',
            'en' => 'NOT_END_WITH',
            'cn' => 'CONTAIN',
            'nc' => 'NOT_CONTAIN'
        );


    /** @var \Zend\ServiceManager\ServiceManager */
    protected $_serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->_serviceManager = $serviceManager;
    }

    /**
     * Get key from class name map
     *
     * @param $className
     *
     * @return mixed
     */
    public function getEntityKeyFromClassname($className)
    {
        $filename = $this->getEntityCacheFile();
        $cache    = include "$filename";

        return array_search($className, $cache);
    }

    /**
     * Get classname from key map
     *
     * @param $entityKey
     *
     * @return null
     */
    public function getClassnameFromEntityKey($entityKey)
    {
        $filename = $this->getEntityCacheFile();
        $cache    = include "$filename";

        return isset($cache[$entityKey]) ? $cache[$entityKey] : null;
    }

    public function getEntityCacheFile()
    {
        $config   = $this->_serviceManager->get('config');
        $filename = $config['jqgrid']['entity_cache']['orm'];

        if (!file_exists($filename)) {
            $this->_createEntityCache($filename);
        }

        return $filename;
    }

    /**
     * Create cache file if it does not exist
     *
     * @param $filename
     *
     * @return bool|int
     */
    protected function _createEntityCache($filename)
    {
        $output = array();
        /** @var $grid  \SynergyDataGrid\Grid\GridType\DoctrineORMGrid */
        $grid = $this->_serviceManager->get('jqgrid');

        $cmf     = $grid->getObjectManager()->getMetadataFactory();
        $classes = $cmf->getAllMetadata();

        $filter = new CamelCaseToDash();

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $class */
        foreach ($classes as $class) {
            $name         = str_replace($class->namespace . '\\', '', $class->getName());
            $key          = strtolower($filter->filter($name));
            $output[$key] = $class->getName();
        }

        $data = '<?php return ' . var_export($output, true) . ';';

        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0755, true);
        }

        return file_put_contents($filename, $data);
    }

    protected function _processFilter($data)
    {

        $filters   = array();
        $operation = isset($data['searchOper']) ? $data['searchOper'] : 'eq';

        if (isset($data['filters'])) {
            $filter = Json::decode($data['filters'], Json::TYPE_ARRAY);

            if (count($filter['rules']) > 0) {

                foreach ($filter['rules'] as $rule) {
                    $filters['field'][]      = $rule['field'];
                    $filters['value'][]      = $rule['data'];
                    $filters['expression'][] = $this->_expression[$rule['op']];
                }

                $filters['options']['multiple'] = true;
                $filters['options']['boolean']  = (isset($filter['groupOp'])) ? $filter['groupOp'] : 'AND';

                return $filters;
            }
        } elseif (isset($data['searchField'])) {

            // Single field filtering
            return array(
                'field'      => $data['searchField'],
                'value'      => trim($data['searchString']),
                'expression' => $this->_expression[$operation],
                'options'    => array()
            );
        }

        return $filters;

    }

    /**
     * Get domain model
     *
     * @param       $data
     * @param       $className
     * @param array $gridOptions
     *
     * @return \SynergyDataGrid\Model\BaseModel
     */
    protected function _getModel($className, $data = array(), $gridOptions = array())
    {
        if (isset($data['sidx'])) {
            $sort = array(
                'sidx' => $data['sidx'],
                'sord' => isset($data['sord']) ? $data['sord'] : 'asc'
            );
        } else {
            $sort = array();
        }

        $options = array(
            'gridConfig' => $gridOptions,
            'grid'       => isset($data['grid']) ? $data['grid'] : null,
            'entity'     => isset($data['entity']) ? $data['entity'] : null,
            'filters'    => isset($data['filters']) ? $this->_processFilter($data) : null,
            'page'       => isset($data['page']) ? $data['page'] : null,
            'rows'       => isset($data['rows']) ? $data['rows'] : null,
            'sord'       => isset($data['sord']) ? $data['sord'] : null,
            'sort'       => $sort
        );

        /** @var $model \SynergyDataGrid\Model\BaseModel */
        $model = $this->_serviceManager->get('synergydatagrid\model');
        $model->setEntityClass($className);
        $modelOptions = new ModelOptions($options);
        $model->setOptions($modelOptions);

        /** @var $entityManager \Doctrine\Orm\EntityManager */
        $entityManager = $this->_serviceManager->get($model->getOrmKey());
        $model->setEntityManager($entityManager);

        return $model;
    }
}