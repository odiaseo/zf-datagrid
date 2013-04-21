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
         * Map database base columns to grid types e.g. array(description => textarea, 'image' => 'file')
         */
        'column_type_mapping' => array(

        ),
        /* If set to true, entities with nullable=false would be required */
        'enforce_required_fields' => false,
    ),
);