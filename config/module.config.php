<?php
// From within a configuration file
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    'jqgrid' => array(
        /**
         * These columns would not be displayed on the grid
         */
        'excluded_columns' => array(

        ),
        /*
         * these database columns would be non-editable
         */
        'non_editable_columns' => array(

        ),

        /*
         * Map database base columns to grid types e.g. description => textarea
         */
        'column_type_mapping' => array(

        )
    ),
);