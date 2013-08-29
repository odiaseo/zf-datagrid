<?php


    //copy this file to autoload config and rename to jqgrid.global.dist
    return array(
        'view_manager' => array(
            'template_path_stack' => array(
                __DIR__ . '/../view',
            ),
        ),

        'jqgrid'       => array(
            /**
             * settings for customising plugins e.g. jquery datepicker
             */
            'plugins'                           => array(
                'date_picker' => array(
                    'dateFormat' => 'D, d M yy',
                    'timeFormat' => 'hh:mm'
                )
            ),
            /**
             * The default value of this property is:
             * {page:“page”,rows:“rows”, sort:“sidx”, order:“sord”, search:“_search”, nd:“nd”, id:“id”, oper:“oper”, editoper:“edit”, addoper:“add”, deloper:“del”, subgridid:“id”, npage:null, totalrows:“totalrows”}
             * This customizes names of the fields sent to the server on a POST request. For example, with this setting, you can change the sort order element from sidx to mysort by setting prmNames: {sort: “mysort”}. The string that will be POST-ed to the server will then be myurl.php?page=1&rows=10&mysort=myindex&sord=asc rather than myurl.php?page=1&rows=10&sidx=myindex&sord=asc
             * So the value of the column on which to sort upon can be obtained by looking at $POST['mysort'] in PHP. When some parameter is set to null, it will be not sent to the server. For example if we set prmNames: {nd:null} the nd parameter will not be sent to the server. For npage option see the scroll option.
             * These options have the following meaning and default values:
             *  page: the requested page (default value page)
             * rows: the number of rows requested (default value rows)
             * sort: the sorting column (default value sidx)
             * order: the sort order (default value sord)
             * search: the search indicator (default value _search)
             * nd: the time passed to the request (for IE browsers not to cache the request) (default value nd)
             * id: the name of the id when POST-ing data in editing modules (default value id)
             * oper: the operation parameter (default value oper)
             * editoper: the name of operation when the data is POST-ed in edit mode (default value edit)
             * addoper: the name of operation when the data is posted in add mode (default value add)
             * deloper: the name of operation when the data is posted in delete mode (default value del)
             * totalrows: the number of the total rows to be obtained from server - see rowTotal (default value totalrows)
             * subgridid: the name passed when we click to load data in the subgrid (default value id)
             *
             *  override the default by adding your params to this options in your config
             */
            'prmNames'                          => array(),
            /**
             * If set to true, the grid will be loaded with data from the database onload.
             * If false, the grid will be loaded with data after page load via ajax
             */
            'first_data_as_local'               => true,
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
            'render_script_as_template'         => false,
            /**
             * If set to true, whitespaces would be remove from the generated javascript code
             * experimental!
             */
            'compress_script'                   => false,
            /**
             * If true, it adds a additional column to every row with edit/delete buttons
             */

            'add_action_column'                 => true,
            'allow_form_edit'                   => true,
            /**
             * When the action column id added to the grid, by default the normal form buttons are not
             * displayed on the nav toolbar. If set to true, the buttons will be displayed in addition
             * to the
             */

            /**
             * These are options specific to tree grids
             * The gedmo/doctrine-extensions module is required for this to work
             */

            /**loads the entire tree on first load. Set to false to load on the top level
             * deeper levels would be load on click via ajax
             *
             */
            'tree_load_all'                     => true,
            'tree_grid_options'                 => array(
                'gridview'          => false,
                'treeGridModel'     => 'nested',
                'ExpandColumn'      => 'title',
                'ExpandColumnWidth' => 120,
                'treeReader'        => array(
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
            'grid_options'                      => array(
                'datatype'           => 'json',
                'mtype'              => 'POST',
                'viewrecords'        => true,
                'height'             => 'auto',
                'allowResizeColumns' => true,
                'sortable'           => true,
                'viewsortcols'       => array(true, 'vertical', true),
                'rowList'            => array(5, 10, 25, 50, 100),
                'rowNum'             => 25,
                'rows'               => true,
                'autowidth'          => false,
                'forceFit'           => true,
                'gridview'           => true,
                'multiselect'        => true,
                'multiboxonly'       => false,
                'rownumbers'         => false,

            ),
            /**
             * When rendering the grid, columns with these attributes would not be displayed at all
             *
             */
            'excluded_columns'                  => array(),

            /**
             * These columns would not be editable
             *
             * @deprecated see column_model option
             */
            'non_editable_columns'              => array(),

            /**
             * These columns would be hidden in the grid
             *
             * @deprecated see column_model option
             */
            'hidden_columns'                    => array(),

            /**
             * Use these configuration to map columns to input types
             * e.g. 'description' => 'textarea'
             *
             * @deprecated see column_model option
             */
            'column_type_mapping'               => array(),

            /**
             * Custom toolbar buttons
             *
             * Configure toolbars buttons to be added to all grids or to specific grids
             * Add configuration in the specific section. Change table_name_here to the table name
             */
            'toolbar_buttons'                   => array( /*                'example' => array(
                    'title'    => 'Test',
                    'icon'     => 'icon-edit',
                    'callback' => new \Zend\Json\Expr('function(){ alert("i am here");}')
                )*/
            ),

            /**
             *
             * Specify column models for columns. This will overwrite the default settings
             *
             * @since 27-05-2013
             * @see   http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
             */
            'column_model'                      => array(
                'actions'  => array(
                    'viewable' => false
                ),
                'id'       => array(
                    'editable'  => false,
                    'editrules' => array(
                        'edithidden' => false
                    ),
                    'formatter' => 'integer'
                ),
                'password' => array(
                    'editable' => false,
                    'viewable' => false
                ),
                'email'    => array(
                    'formatter' => 'email',
                    'editrules' => array(
                        'email' => true,
                    ),
                ),
                'url'      => array(
                    'formatter' => 'link',
                    'editrules' => array(
                        'url' => true,
                    ),
                )
            ),
            /**
             * @See http://www.trirand.com/jqgridwiki/doku.php?id=wiki:toolbar_searching&s[]=filtertoolbar
             */
            'filter_toolbar'                    => array(
                'enabled' => true,
                'options' => array(
                    'searchOperators' => true,
                    'autosearch'      => true,
                    'stringResult'    => true,
                    'defaultSearch'   => 'cn',
                )
            ),

            /**
             * Navgrid options
             *
             * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:navigator
             */
            'nav_grid'                          => array(
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
            'edit_parameters'                   => array(
                'reloadAfterSubmit' => true,
                'jqModal'           => true,
                'closeOnEscape'     => false,
                'recreateForm'      => true,
                'bottominfo '       => 'Fields marked with (*) are required'
            ),

            'add_parameters'                    => array(
                'reloadAfterSubmit' => true,
                'jqModal'           => true,
                'closeOnEscape'     => false,
                'recreateForm'      => true,
                'checkOnSubmit'     => true,
                'closeAfterAdd'     => true,

            ),

            'view_parameters'                   => array(
                'reloadAfterSubmit' => true,
                'modal'             => true,
                'jqModal'           => true,
                'closeOnEscape'     => false
            ),

            'search_parameters'                 => array(
                'closeOnEscape'  => false,
                'sFilter'        => 'filters',
                'multipleSearch' => true

            ),

            'delete_parameters'                 => array(
                'height' => 'auto'
            ),

            'inline_nav'                        => array(
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
                    )
                )
            ),

            'form_options'                      => array(
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
            'custom_nav_buttons'                => function ($gridId) {
                return array(
                    'column-chooser' => array(
                        'id'       => 'column_chooser',
                        'icon'     => 'ui-icon-folder-open',
                        'action'   => new \Zend\Json\Expr("function (){ jQuery('#" . $gridId . "').jqGrid('columnChooser');  }"),
                        'title'    => "Reorder Columns",
                        'caption'  => "",
                        'position' => 'last'
                    ),
                    'filter-toolbar' => array(
                        'id'      => 'search_filter',
                        'caption' => "",
                        'title'   => "Toggle Search Toolbar",
                        'icon'    => 'ui-icon-pin-s',
                        'action'  => new \Zend\Json\Expr("jQuery('#" . $gridId . "')[0].toggleToolbar(); ")
                    ),

                );
            },

            /**
             * This is the default association mapping callbach function
             * Returns formatted string for rendering select options
             *
             * You can specify difference callback functions for each mapped field e.d. to specify a callback function for a field myField
             * add the myfield index to the array  myField => youCallbackFunction
             *
             * @See * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:common_rules#editable
             *
             */
            'association_mapping_callback'      => array(
                '__default__' => function ($serviceManager, $entity) {
                    $values = array(':Select');
                    try {
                        $em   = $serviceManager->get('doctrine.entitymanager.orm_default');
                        $qb   = $em->createQueryBuilder();
                        $list = $qb->select('e.id, e.title')
                            ->from($entity, 'e')
                            //->orderBy('e.title')
                            ->getQuery()
                            ->execute();

                        foreach ($list as $item) {
                            $values[] = $item['id'] . ':' . str_replace(array('&amp;', '&'), ' and ', $item['title']);
                        }
                    } catch (\Exception $e) {
                        //@TODO fix this
                    }

                    return $values;
                }

            ),
            /**
             * This is the default ID field when displaying join table records in the selects
             */
            'default_association_mapping_id'    => 'id',
            /*
             * This is the default label/title field when displaying join table records in selects
             */
            'default_association_mapping_label' => 'title',

            /**
             * Generates the edit url for sub grid which returns the subgrid data
             * Replace with a callback function, closure etc where
             *
             *      $sm = servicelLocator;
             *      $entity = The current entity (FQCN)
             *      $fieldName = the field name of the join column
             *
             *  'grid_url_generator'           => function ($sm, $entity, $fieldName) {
             *
             *  @var $helper \Zend\View\Helper\Url
             *
             *  $helper = $sm->get('viewhelpermanager')->get('url');
             *  $url    = $helper('your_route_name',
             *                     array(
             *                        'your_parameters',
             *                         'fieldName' => $fieldName
             *                    )
             *                );
             *
             *                 return new \Zend\Json\Expr("'$url?subgridid='+row_id");
             *        }
             *  )
             */

            'grid_url_generator'                => '',

        )
    );