<?php
// From within a configuration file
    return array(
        'view_manager' => array(
            'template_path_stack' => array(
                __DIR__ . '/../view',
            ),
        ),

        'jqgrid'       => array(
            /**
             * These columns would not be displayed on the grid
             */
            'excluded_columns'        => array(),
            /*
             * these database columns would be non-editable
             */
            'non_editable_columns'    => array(),

            /*
             * Map database base columns to grid types e.g. array(description => textarea, 'image' => 'file')
             */
            'column_type_mapping'     => array(),

            /* If set to true, entities with nullable=false would be required */
            'enforce_required_fields' => false,

            /* Custom toolbar buttons
               Specify buttons to be displayed on the grids toolbar
               buttons in the global section would appear on all grids
               buttons in the specify section would only be added to the grid with

                e.g.
                'toolbar_buttons'      => array(
                    'specific' => array(
                        'menu' => array(
                            array(
                                'title'    => 'Test',
                                'icon'     => 'icon-edit',
                                'callback' => new \Zend\Json\Expr('function(){ alert("i am here");}')
                            )
                        )
                    )
                )
                where menu is the ID of the grid
            */
            'toolbar_buttons'         => array(
                'global'     => array(),
                'specific' => array()
            )
        ),
    );