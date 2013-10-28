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
             * When adding function use \Zend\Expr\Expr e.g. new \Zend\Json\Expr('function(){ alert("i am here");}')
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
             *
             * 'global'   => array(
             *      'help' => array(
             *          'title'    => 'Help',
             *          'icon'     => 'icon-info-sign',
             *          'position' => 'top',
             *          'class'    => 'btn btn-mini',
             *          'callback' => new \Zend\Json\Expr('function(){ alert("i am here");}')
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
             * e.g.  'afterSubmit' => new \Zend\Json\Expr("function() { alert('test'); }"),
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

            'grid_url_generator'                => '',
        )
    );
