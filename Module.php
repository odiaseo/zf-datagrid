<?php
namespace SynergyDataGrid;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SynergyDataGrid\Grid\GridType\BaseGrid;
use Zend\Json\Expr;
use Zend\ServiceManager\ServiceManager;

/**
 * Module
 *
 * @package   Synergy
 * @copyright Pele Odiase (c) - http://www.peleodiase.com
 * @license   http://www.zfdaily.com/code/license New BSD License
 * @link      http://www.zfdaily.com
 * @link      https://bitbucket.org/dlu/dlutwbootstrap
 */
class Module
{
    public function init()
    {
        $lib = 'vendor/gedmo/doctrine-extensions/lib';
        AnnotationRegistry::registerAutoloadNamespace('Gedmo\Mapping\Annotation', $lib);
    }

    /* ********************** METHODS ************************** */

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        $data   = array(
            'jqgrid' => array(
                /**
                 * This is the default association mapping callbach function
                 * Returns formatted string for rendering select options
                 *
                 * You can specify difference callback functions for each mapped field e.d. to specify
                 * a callback function for a field myField
                 * add the myfield index to the array  myField => youCallbackFunction
                 *
                 * @See * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editable
                 *
                 */
                'association_mapping_callback' => array(

                    '__default__' => function ($serviceManager, $entity, $mappedBy = null) {
                        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
                        $values = array(':Select');
                        /** @var $em \Doctrine\Orm\EntityManager */
                        $em = $serviceManager->get('doctrine.entitymanager.orm_default');
                        try {
                            if ($mappedBy) {
                                $qb    = $em->createQueryBuilder();
                                $query = $qb->select('m.id, m.title')
                                    ->from($entity, 'e')
                                    ->innerJoin('e.' . $mappedBy, 'm')
                                    ->getQuery();
                                $list  = $query->execute();
                            } else {
                                $qb   = $em->createQueryBuilder();
                                $list = $qb->select('e.id, e.title')
                                    ->from($entity, 'e')
                                    ->getQuery()
                                    ->execute();
                            }
                            foreach ($list as $item) {
                                $values[]
                                    = $item['id'] . ':' . str_replace(array('&amp;', '&'), ' and ', $item['title']);
                            }
                        } catch (\Exception $e) {
                            //@TODO fix this
                        }

                        return $values;
                    }

                ),

                /**
                 * Adds custom navigation buttons
                 * supports closures and Json expression finder
                 */
                'custom_nav_buttons'           => function ($gridId) {
                    return array(
                        'column-chooser' => array(
                            'id'       => 'column_chooser',
                            'icon'     => 'ui-icon-folder-open',
                            'action'   => new Expr(
                                "function (){ jQuery('#" . $gridId . "').jqGrid('columnChooser');  }"),
                            'title'    => "Reorder Columns",
                            'caption'  => "",
                            'position' => 'last'
                        ),
                        'filter-toolbar' => array(
                            'id'      => 'search_filter',
                            'caption' => "",
                            'title'   => "Toggle Search Toolbar",
                            'icon'    => 'ui-icon-pin-s',
                            'action'  => new Expr("jQuery('#" . $gridId . "')[0].toggleToolbar(); ")
                        ),

                    );
                },

                'grid_url_generator'           => 'synergy\helper\urlGenerator',
            )
        );
        $config = include __DIR__ . '/config/module.config.php';
        $merged = array_merge_recursive($config, $data);

        return $merged;
    }

    public function getServiceConfig()
    {
        return array(
            'aliases'            => array(
                'synergy\service\grid'    => 'SynergyDataGrid\Service\GridService',
                'synergy\service\subgrid' => 'SynergyDataGrid\Service\SubGridService',
            ),
            'invokables'         => array(
                'SynergyDataGrid\Service\GridService'    => 'SynergyDataGrid\Service\GridService',
                'SynergyDataGrid\Service\SubGridService' => 'SynergyDataGrid\Service\SubGridService',
            ),
            'shared'             => array(
                'jqgrid'                => false,
                'synergydatagrid\model' => false,
            ),
            'abstract_factories' => array(
                'SynergyDataGrid\Model\AbstractModelFactory',
                'SynergyDataGrid\Grid\AbstractGridFactory',
                'SynergyDataGrid\Config\AbstractGridConfigFactory'
            ),
            'factories'          => array(
                'logger' => 'SynergyCommon\Service\LoggerFactory',
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'displayGrid' => 'SynergyDataGrid\View\Helper\DisplayGrid',
            )
        );
    }
}
