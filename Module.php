<?php
namespace SynergyDataGrid;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Handler\RotatingFileHandler;
use SynergyCommon\Util\ErrorHandler;
use SynergyDataGrid\Grid\GridType\BaseGrid;
use SynergyDataGrid\Service\GridService;
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

                /**
                 * Generates the edit url e.g. for sub grid which returns the subgrid data
                 * Replace with a callback function, closure etc,
                 *
                 *      $sm = servicelLocator;
                 *      $entity = The current entity (FQCN)
                 *      $fieldName = the field name of the join column
                 *      $targetEntity = FQCN of the target entity
                 *      $urlTypes:
                 *          const DYNAMIC_URL_TYPE_GRID       = 1; //this is the url to get data for the main grid
                 *          const DYNAMIC_URL_TYPE_EDIT       = 2; // the editurl for main grid
                 *          const DYNAMIC_URL_TYPE_SUBGRID    = 3; // th edit url for the subgrid for CRUD
                 *          const DYNAMIC_URL_TYPE_ROW_EXPAND = 4; //row expand url for subgridAsGrid to load data
                 *
                 *   Example:
                 *
                 *  'grid_url_generator'           => function ($sm, $entity, $fieldName, $targetEntity, $urlType) {
                 *      switch($urlType){
                 *         .....
                 *         case \SynergyDataGrid\Grid\GridType\BaseGrid::DYNAMIC_URL_TYPE_ROW_EXPAND:
                 *
                 * @var $helper \Zend\View\Helper\Url
                 *          $helper = $sm->get('viewhelpermanager')->get('url');
                 *          $url    = $helper('your_route_name',
                 *                              array(
                 *                                      'your_parameters',
                 *                                      'fieldName' => $fieldName
                 *                              )
                 *                      );
                 *
                 *              return new \Zend\Json\Expr("'$url?subgridid='+row_id");
                 *          break;
                 *          .......
                 *        }
                 *     )
                 *
                 */
                'grid_url_generator'           => function ($sm, $entity, $fieldName, $targetEntity, $urlType) {
                    /** @var $sm\Zend\ServiceManager\ServiceManager */
                    /** @var $helper \Zend\View\Helper\Url */
                    $helper = $sm->get('viewhelpermanager')->get('url');


                    /** @var $service \SynergyDataGrid\Service\GridService' */
                    $service = $sm->get('synergy\service\grid');

                    $entityKey = $service->getEntityKeyFromClassname($entity);

                    switch ($urlType) {
                        case BaseGrid::DYNAMIC_URL_TYPE_ROW_EXPAND:
                        case BaseGrid::DYNAMIC_URL_TYPE_SUBGRID :
                            $url    = $helper(
                                'synergydatagrid\subgrid',
                                array(
                                     'entity'    => $entityKey,
                                     'fieldName' => $fieldName
                                )
                            );
                            $return = new Expr("'$url?subgridid='+row_id");
                            break;

                        default:
                            $return = $helper('synergydatagrid', array('entity' => $entityKey));
                    }

                    return $return;
                },
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
                'SynergyDataGrid\Grid\AbstractGridFactory'
            ),
            'factories'          => array(
                'logger' => function () {
                    $filename = 'data/logs/' . __NAMESPACE__ . '-app.log';
                    $stream   = new RotatingFileHandler($filename, 5);
                    $logger   = new ErrorHandler(__NAMESPACE__, array($stream));

                    return $logger;
                },
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
