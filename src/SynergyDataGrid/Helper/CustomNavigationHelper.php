<?php
namespace SynergyDataGrid\Helper;

use Laminas\Json\Expr;

/**
 * Class CustomNavigationHelper
 *
 * Adds custom navigation buttons
 * supports closures and Json expression finder
 *
 * @package SynergyDataGrid\Helper
 */
class CustomNavigationHelper
    extends BaseConfigHelper
{
    public function execute(array $parameters)
    {
        list($gridId,) = $parameters;

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
    }
}