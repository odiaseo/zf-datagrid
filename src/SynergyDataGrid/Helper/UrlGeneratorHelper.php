<?php
namespace SynergyDataGrid\Helper;

use SynergyDataGrid\Grid\GridType\BaseGrid;
use Zend\Json\Expr;

/**
 * Class UrlGeneratorConfig
 *
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
 * @package                     SynergyDataGrid\Helper
 */
class UrlGeneratorHelper extends BaseConfigHelper
{
    public function execute(array $parameters)
    {
        list($entity, $fieldName, , $urlType) = $parameters;

        /** @var $helper \Zend\View\Helper\Url */
        $helper = $this->_serviceManager->get('ViewHelperManager')->get('url');
        $config = $this->_serviceManager->get('config');
        if (!empty($config['jqgrid']['api_domain'])) {
            $prefix = rtrim($config['jqgrid']['api_domain'], '/');
            $concat = '&';
        } else {
            $prefix = '';
            $concat = '?';
        }

        /** @var $service \SynergyDataGrid\Service\GridService' */
        $service = $this->_serviceManager->get('synergy\service\grid');

        $entityKey = $service->getEntityKeyFromClassname($entity);

        switch ($urlType) {
            case BaseGrid::DYNAMIC_URL_TYPE_ROW_EXPAND:
            case BaseGrid::DYNAMIC_URL_TYPE_SUBGRID:
                $url = $helper(
                    'synergydatagrid\subgrid',
                    array(
                        'entity'    => $entityKey,
                        'fieldName' => $fieldName
                    )
                );

                $return = new Expr("'{$prefix}$url{$concat}subgridid='+row_id");
                break;

            default:
                $return = $prefix . $helper('synergydatagrid', array('entity' => $entityKey));
        }

        return $return;
    }
}
