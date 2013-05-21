<?php
    //copy this file to autoload config and rename to jqgrid.global.dist
    return array(
        'jqgrid' => array(
            /**
             * The JavaScript code to generate the grid is added to the headScript.
             * If set to true, the type attribute of the script table would be set to text/x-jquery-tmpl
             * instead of text/javascript e.g. headScript()->appendScript($onLoadScript, 'text/x-jquery-tmpl', array("id='ID'", 'noescape' => true))
             * useful if you use plugins like require.js. You can then eval() the script in the callback function e.g.
             * define([jquery], function($){
            var gridScript = $('script[type="text/x-jquery-tmpl"]').text();
            $.globalEval(gridScript);
             * }}
             */
            'render_script_as_template'    => false,
            /**
             * If true, it adds a additional column to every row with edit/delete buttons
             */
            'add_action_column'            => true,

            /**
             * When the action column id added to the grid, by default the normal form buttons are not
             * displayed on the nav toolbar. If set to true, the buttons will be displayed in addition
             * to the
             */
            'allow_edit_form' => false,
            /**
             * These are options specific to tree grids
             * The gedmo/doctrine-extensions module is required for this to work
             */
            'tree_grid_options'            => array(
                'gridview'      => false,
                'treeGridModel' => 'nested',
                'ExpandColumn'  => 'title',
                'treeReader'    => array(
                    'level_field'    => 'level',
                    'left_field'     => 'lft',
                    'right_field'    => 'rgt',
                    'leaf_field'     => 'isLeaf',
                    'expanded_field' => 'expanded',
                )
            ),

            /**
             * Grid options
             * All valid jqgrid options can be added here
             *
             * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options
             */
            'grid_options'                 => array(
                'datatype'           => 'json',
                'mtype'              => 'POST',
                'viewrecords'        => true,
                'height'             => 'auto',
                'allowResizeColumns' => true,
                'sortable'           => true,
                'viewsortcols'       => true,
                'rowNum'             => 25,
                'rows'               => true,
                'autowidth'          => false,
                'forceFit'           => true,
                'gridview'           => true,
                'multiselect'        => true,
                'multiboxonly'       => false,
                'rownumbers'         => false

            ),
            /**
             * When rendering the grid, columns with these attributes would not be displayed
             * e.g. password
             */
            'excluded_columns'             => array(),

            /**
             * These columns would not be editable
             */
            'non_editable_columns'         => array(),

            /**
             * These columns would be hidden in the grid
             */
            'hidden_columns'               => array(),

            /**
             * Use these configuration to map columns to input types
             * e.g. 'description' => 'textarea'
             */
            'column_type_mapping'          => array(),

            /**
             * Custom toolbar buttons
             *
             * Configure toolbars buttons to be added to all grids or to specific grids
             * Add configuration in the specific section. Change table_name_here to the table name
             */
            'toolbar_buttons'              => array(
                'global'   => array(),
                'specific' => array(
                    'table_name_here' => array(
                        array(
                            'title' => 'Test',
                            'icon'  => 'icon-edit',
                            // 'callback' => new \Zend\Json\Expr('function(){ alert("i am here");}')
                        )
                    )
                )
            ),

            /**
             * Navgrid options
             *
             * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
             */
            'nav_grid'                     => array(
                'edit'       => true,
                'add'        => true,
                'del'        => true,
                'view'       => true,
                'refresh'    => true,
                'search'     => true,
                'cloneToTop' => false
            ),

            /*
             * Edit parameters
             * e.g.  'afterSubmit' => new \Zend\Json\Expr("function() { alert('test'); }"),
             * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
             */
            'edit_parameters'              => array( //'afterSubmit' => new Expr("function() { alert('test'); }"),
            ),

            'add_parameters'               => array(),

            'view_parameters'              => array(),

            'search_parameters'            => array(),

            'delete_parameters'            => array(),

            'inline_nav'                   => array(
                'add'       => false,
                'del'       => false,
                'edit'      => false,
                'cancel'    => false,
                'save'      => false,
                'addParams' => array(
                    'useFormatter' => true,
                    'addRowParams' => array(
                        'keys'              => true,
                        'restoreAfterError' => true,
                        //'oneditfunc'        => new Expr(" function() { dp_picker(new_row);  }")
                    )
                )
            ),

            'form_options'                 => array(
                'closeAfterAdd'     => true,
                'reloadAfterSubmit' => true,
                'jqModal'           => true,
                'closeOnEscape'     => false,
                'modal'             => true,
                'recreateForm'      => true,
                'checkOnSubmit'     => true,
                'bottominfo'        => 'Fields marked with (*) are required'
            ),

            /**
             * Adds custom navigation buttons
             * supports closures and Json expression finder
             */
            'custom_nav_buttons'           => function ($gridId) {
                return array(
                    'column-chooser' => array(
                        'icon'     => 'icon-folder-open icon-white',
                        // 'action'   => new \Zend\Json\Expr("function (){ jQuery('#" . $gridId . "').jqGrid('columnChooser');  }"),
                        'title'    => "Reorder Columns",
                        'caption'  => "Columns",
                        'position' => 'last'
                    )
                );
            },

            /**
             * Returns formatted string for rendering select options
             *
             * @See * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editable
             *
             */
            'association_mapping_callback' => function ($serviceManager, $entity) {
                $values = array(':select');
                $em     = $serviceManager->get('doctrine.entitymanager.orm_default');
                $qb     = $em->createQueryBuilder();
                $list   = $qb->select('e.id, e.title')
                    ->from($entity, 'e')
                    ->getQuery()
                    ->execute();

                foreach ($list as $item) {
                    $values[] = $item['id'] . ':' . $item['title'];
                }

                return $values;
            }

        )
    );
