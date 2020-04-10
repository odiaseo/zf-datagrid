<?php
/*
 * This file is part of the Synergy package.
 *
 * (c) Pele Odiase <info@rhemastudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 *
 */
return array(

    'router'       => array(
        'routes' => array(
            'synergydatagrid'         => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/synergydatagrid/crud/:entity[/:id]',
                    'defaults'    => array(
                        '__NAMESPACE__' => 'SynergyDataGrid\Controller',
                        'controller'    => \SynergyDataGrid\Controller\GridController::class,
                    ),
                    'constraints' => array(
                        'entity' => '[a-zA-Z\-0-9]+'
                    ),
                ),
            ),
            'synergydatagrid\subgrid' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'       => '/synergydatagrid/crud/:entity/subgrid/:fieldName[/:id]',
                    'defaults'    => array(
                        '__NAMESPACE__' => 'SynergyDataGrid\Controller',
                        'controller'    => \SynergyDataGrid\Controller\SubGridController::class,
                    ),
                    'constraints' => array(
                        'entity'    => '[a-zA-Z\-0-9]+',
                        'fieldName' => '[a-zA-Z\-]+',
                    ),
                ),
            ),
        ),
    ),
    'jqgrid'       => array(
        /**
         * Specify factory class which should return an array config daa. This would be merged with
         * the jqgrid config in the order specified
         */
        'factories'                         => array(),
        /**
         * Allow retrieval of data from a different domain
         * All CRUD request would be prefixed with the api_domain value
         * e.g. http://www.example.com
         */
        'api_domain'                        => '',
        /**
         * settings for customising plugins e.g. jquery datepicker
         */
        'plugins'                           => array(
            'date_picker' => array(
                'dateFormat' => 'yy-mm-dd',
                'timeFormat' => 'hh:mm'
            )
        ),
        /**
         * The default value of this property is:
         * {page:“page”,rows:“rows”, sort:“sidx”, order:“sord”, search:“_search”, nd:“nd”, id:“id”, oper:“oper”, editoper:“edit”, addoper:“add”, deloper:“del”, subgridid:“id”,
         * npage:null, totalrows:“totalrows”}
         * This customizes names of the fields sent to the server on a POST request. For example, with this setting,
         * you can change the sort order element from sidx to mysort by setting prmNames: {sort: “mysort”}. The string that will be POST-ed to the server will then be
         * myurl.php?page=1&rows=10&mysort=myindex&sord=asc rather than myurl.php?page=1&rows=10&sidx=myindex&sord=asc
         * So the value of the column on which to sort upon can be obtained by looking at $POST['mysort'] in PHP.
         * When some parameter is set to null, it will be not sent to the server. For example if we set
         * prmNames: {nd:null} the nd parameter will not be sent to the server. For npage option see the scroll option.
         * These options have the following meaning and default values:
         *
         * page: the requested page (default value page)
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
         *
         * useful if you use plugins like require.js. You can then eval() the script in the callback function e.g.
         * define([jquery], function($){ var gridScript = $('script[type="text/x-jquery-tmpl"]').text();
         * $.globalEval(gridScript);
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
        /**
         * When the action column id added to the grid, by default the normal form buttons are not
         * displayed on the nav toolbar. If set to true, the buttons will be displayed in addition
         * to the
         */
        'allow_form_edit'                   => true,
        /**
         * loads the entire tree on first load. Set to false to load on the top level
         * deeper levels would be load on click via ajax
         *
         */
        'tree_load_all'                     => true,
        /**
         * These are options specific to tree grids
         * The gedmo/doctrine-extensions module is required for this to work
         *
         * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:treegrid&s[]=treegrid
         */
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
         * When adding function use \Zend\Expr\Expr e.g. new \Laminas\Json\Expr('function(){ alert("i am here");}')
         *
         * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options
         */
        'grid_options'                      => array(
            'regional'           => 'en',
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
            //add buttons to display on each row (action column)
            'rowActionButtons'   => array()

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
         *
         * 'global'   => array(
         *      'help' => array(
         *          'title'    => 'Help',
         *          'icon'     => 'icon-info-sign',
         *          'position' => 'top',
         *          'class'    => 'btn btn-mini',
         *          'callback' => new \Laminas\Json\Expr('function(){ alert("i am here");}')
         *     )
         * ),
         *
         *
         *  'specific' => array(
         *      'themes'    => array( // grid ID
         *          'layout-manager' => array(  // toolbar ID
         *              'id'         => 'layman',
         *              'class'      => 'btn btn-mini',
         *              'title'      => 'Layout Manager',
         *              'icon'       => 'icon-th-large',
         *              'position'   => 'bottom',
         *              'onLoad'     => '',
         *              'callback'   => new Expr("function(){  your_callback_onclick_function ; }"),
         *              'attributes' => array(
         *                  data-id => 'any_attricute to add to the tag'
         *              )
         *          )
         *      )
         */
        'toolbar_buttons'                   => array(
            'global'   => array(), //global toolbars to appear on all grids
            'specific' => array() // grid specific toolbars, index is the gridId
        ),
        /**
         * Specify default values for new records. The values epcified in the global array would be applied to all entities.
         * For entity specific defaults, use the specifiy
         * The array key is the entity field name and the value would be the default value
         *
         * If a string  values if provided, the service manager would be used to get the values which should return an array
         * with the same structure as specified
         *
         * example
         * ========
         * 'default_values'                    => array(
         *      'global'   => array(
         *          'currency' => 'EUR'
         *      ),
         *      'specific' => array(
         *          'site' => array(
         *              'currency' => 'GBP'
         *          )
         *      )
         * ),
         *
         * In the example above the default value for currency is EUR. This would be applied to all currency fields
         * But the default for the Site entity/table would be GBP
         */
        'default_values'                    => array(
            'global'   => array(),
            'specific' => array()
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
            'enabled'    => true,
            'showOnLoad' => true,
            'options'    => array(
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
        /**
         * Edit parameters
         * e.g.  'afterSubmit' => new \Laminas\Json\Expr("function() { alert('test'); }"),
         *
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
         * This is the default ID field when displaying join table records in the selects
         */
        'default_association_mapping_id'    => 'id',
        /**
         * This is the default label/title field when displaying join table records in selects
         */
        'default_association_mapping_label' => 'title',
        'association_mapping_callback'      => array(
            '__default__' => 'synergy\helper\defaultAssociationCallback',
        ),
        'custom_nav_buttons'                => 'synergy\helper\customNavigation',
        'grid_url_generator'                => 'synergy\helper\urlGenerator',
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'synergy'      => array(
        /**
         * Location where entity classname to entity key mappings are stored
         */
        'entity_cache'          => array(
            'orm' => 'data/SynergyDataGrid/orm_entity_classmap.php',
            'odm' => 'data/SynergyDataGrid/odm_entity_classmap.php',
        ),
        'check_entity_cache_file' => true,
        'entity_cache_lifetime' => 24 * 60 * 60,
        'config_helpers'        => array(
            'urlGenerator'               => 'SynergyDataGrid\Helper\UrlGeneratorHelper',
            'defaultAssociationCallback' => 'SynergyDataGrid\Helper\DefaultAssociationCallbackHelper',
            'customNavigation'           => 'SynergyDataGrid\Helper\CustomNavigationHelper',
        )
    ),

);
