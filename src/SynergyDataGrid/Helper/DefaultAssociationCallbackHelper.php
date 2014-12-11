<?php
namespace SynergyDataGrid\Helper;

use SynergyDataGrid\Grid\GridType\BaseGrid;

/**
 * Class DefaultAssociationCallbackHelper
 * This is the default association mapping callbach function
 * Returns formatted string for rendering select options
 * You can specify difference callback functions for each mapped field e.d. to specify
 * a callback function for a field myField
 * add the myfield index to the array  myField => youCallbackFunction
 *
 * @See     * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editable
 * @package SynergyDataGrid\Helper
 */
class DefaultAssociationCallbackHelper
    extends BaseConfigHelper
{
    protected static $_list = array();

    public function execute(array $parameters)
    {
        $hash = md5(json_encode($parameters));

        if (isset(static::$_list[$hash])) {
            return static::$_list[$hash];
        }

        list($entity, $mappedBy) = $parameters;
        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $values = array(':Select');
        /** @var $em \Doctrine\Orm\EntityManager */
        $em = $this->_serviceManager->get('doctrine.entitymanager.orm_default');
        try {
            if ($mappedBy) {
                $qb    = $em->createQueryBuilder();
                $query = $qb->select('m.id, m.title')
                    ->from($entity, 'e')
                    ->innerJoin('e.' . $mappedBy, 'm')
                    ->orderBy('m.title')
                    ->getQuery();
                $list  = $query->execute();
            } else {
                $qb   = $em->createQueryBuilder();
                $list = $qb->select('e.id, e.title')
                    ->from($entity, 'e')
                    ->orderBy('e.title')
                    ->getQuery()
                    ->execute();

            }

            foreach ($list as $item) {
                $values[] = $item['id'] . ':' . str_replace(array('&amp;', '&'), ' and ', $item['title']);
            }

            static::$_list[$hash] = $values;
        } catch (\Exception $e) {
            //@TODO fix this
        }

        return $values;
    }
}