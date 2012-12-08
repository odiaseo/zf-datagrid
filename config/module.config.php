<?php
// From within a configuration file
return array(
    'view_manager' => array(
        'helper_map' => array(
            'displayGrid' => 'SynergyDataGrid\View\Helper\DisplayGrid',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'jqgrid' => 'SynergyDataGrid\Grid\JqGridFactory',
        ),
    ),
    'jqgrid' => array(
        'excluded_columns' => array(

        ),
        'non_editable_columns' => array(

        )
    ),
);