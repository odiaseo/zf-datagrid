[![Latest Stable Version](https://poser.pugx.org/synergy/synergydatagrid/v/stable)](https://packagist.org/packages/synergy/synergydatagrid)
[![Total Downloads](https://poser.pugx.org/synergy/synergydatagrid/downloads)](https://packagist.org/packages/synergy/synergydatagrid)
[![composer.lock](https://poser.pugx.org/synergy/synergydatagrid/composerlock)](https://packagist.org/packages/synergy/synergydatagrid)
[![Build Status](https://travis-ci.org/odiaseo/zf2-datagrid.svg?branch=master)](https://travis-ci.org/odiaseo/zf2-datagrid)
[![Coverage Status](https://coveralls.io/repos/github/odiaseo/zf2-datagrid/badge.svg)](https://coveralls.io/github/odiaseo/zf2-datagrid)

#SynergyDataGrid (ZF2 module)
##Introduction
SynergyDataGrid is a [Zend Framework 2](http://framework.zend.com/zf2) module facilitating usage of [JqGrid](http://www.trirand.com/blog/) in ZF2 applications.
It provides basic CRUD functions for editing  database tables as an AJAX-based grid.
For use all available jqGrid plugin and library features please see jqGrid documentation at <http://www.trirand.com/jqgridwiki/doku.php>

##Dependencies
+ [Zend Framework 2](http://framework.zend.com/),
+ [Doctrine 2.0](http://www.doctrine-project.org/),
+ [jQuery >= 1.4.2](http://jquery.com),
+ [jQuery UI 1.8.9](http://jqueryui.com/),
+ [jqGrid plugin >= 4.3.1](http://www.trirand.com/blog/),

### Optional
+ [Doctrine2 Behavioural Extensions](https://github.com/l3pp4rd/DoctrineExtensions)

###Future Development Plans include:
- Doctrine ODM Grid
- Zend DB Grid

#Installation

##Manual Installation
   1. Go to your project's directory.
   2. Clone this project into your `./vendor/synergy/` directory as a `synergydatagrid` module:
   >   `git clone https://github.com/odiaseo/zf2-datagrid.git`
   3. Copy all files in public directory to your project's public folder

##With Composer
   1. Go to your project's directory.
   2. Run the command `composer require "synergy/synergydatagrid"` 
   4. Follow the Post installation steps bellow

#Post Installation steps
 Currently, this module supports only  Doctrine ORM entities so ensure that DoctrineORM is configured correctly.

#Usage
+ In your controller class:
```php
         public function gridAction() {
               //replace {Entity_Name} with your entity name e.g. 'Application\Entity\User'

               $serviceManager = $this->getServiceLocator() ;
               $grid = $serviceManager->get('jqgrid')->setGridIdentity({Entity_Name});

               $grid->setUrl('your_custom_crun_url'); //optional, if not set the default CRUD controller would be used

               return array('grid' => $grid);
         }
```
+ In your view script:
```php
        echo $this->displayGrid($this->grid);
```
   - By default the javaScript code would be appended to the head section of the page using the headScript view helper and executed on document ready
   - If you do not want the script executed on load set `render_script_as_template` option to true. The code would be wrapped in a `script` tag with type `text/x-jquery-tmpl`
   - If you want to get access to the html and javascript pass false as the second parameter i.e. `$params = $this->displayGrid($this->grid, false);`. An associative array of the `html`, `js` and `onLoad` script would be returned. Useful if you are makking AJAX requests to generate the grid.
+ In head section of your layout:
```php
             $this->headLink()->appendStylesheet('/jqGrid/css/ui.jqgrid.css')
                     ->appendStylesheet('/css/jquery.ui.datepicker.css')
                     ->appendStylesheet('/plugins/ui.multiselect.css') ;



             $this->headScript()->prependFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js', 'text/javascript')
                    ->prependFile('http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js', 'text/javascript')
                    ->appendFile('/jqGrid/js/i18n/grid.locale-en.js', 'text/javascript')
                    ->appendFile('/plugins/ui.multiselect.js', 'text/javascript')
                    ->appendFile('/jqGrid/js/jquery.jqGrid.min.js', 'text/javascript') ;
```

#Setting grid options
You can use/set any of the jqgrid options (see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options)
e.g. to set the "datatype" to local, in your controller add the code
```php
          $grid->setDatatype('local');
```
> The logic is append 'set' to any of the options and it would be added to the grid.

#Adding ColModel Options
All column model options can be added to the grid (see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options)
In your grid configuration file or module.config.php, specify the the model options e.g.
```php
    return array(
        .......
        'jqgrid' => array (
            'column_model'  => array(
                'my_column'       => array(
                    'align'       => 'center',
                    'formatter'   => new Zend\Json\Expr('your code goes here'),
                    'unformat'    => new Zend\Json\Expr('your code goes here'),
                    'edittype'    => 'custom',
                    'editoptions' => array(
                        'custom_element' => new Zend\Json\Expr('your code goes here'),
                        'custom_value'   => new Zend\Json\Expr('your code goes here')
                    )
                ),
        )
    );
```
#Adding Custom Javascript
If you want to add additional javaScript to be rendered along with the grid script, you can do this:
```php
             $grid->getJsCode()->addCustomScript( new \Zend\Json\Expr(" and your script here" ));
```
or
```php
             $grid->getJsCode()->addCustomScript("add your script here");
```

#Adding Subgrid
You can add a sub grid as either
+ subgrid (http://www.trirand.com/jqgridwiki/doku.php?id=wiki:subgrid)
+ subgrid as grid (http://www.trirand.com/jqgridwiki/doku.php?id=wiki:subgrid_as_grid)

1. From the Doctrine entity file, note the class attribute you want to be displayed as a subgrid. It should be an association field e.g ManyToOne etc
Modify you grid configuration file or add this to your module.config.php

 * We want to display the field my_field as a subgrid
```php
             return array(
                 .......
                 'jqgrid' => array (
                        'column_model'  => array(
                            'my_field'       => array(
                                'isSubGrid' => true
                             ),
                        )
                  );
```
 * We want to display  my_field as a subgrid_as_grid
```php
                  return array(
                                 .......
                         'jqgrid' => array (
                                  'column_model'  => array(
                                       'my_field'       => array(
                                           'isSubGridAsGrid' => true
                                       ),
                                 )
                         );
```
2. In the controller action that returns the grid data, you need to pass an array as the second parameter to the prepareGridData function.
The array should have a 'fieldName' key which points to the entity field from which to retrieve the data from. The fieldName is the
name of the joinColumn on the main entity class.
```php
           public function crudAction(){              ......
               $className = 'your_class_name';
               $options['fieldName'] = $this->params()->fromRoute('fieldName', null);
               $serviceManager = $this->getServiceLocator();
               $grid = $serviceManager->get('jqgrid');
               $grid->setGridIdentity($className);
               $response = $grid->prepareGridData($this->getRequest(), $options);
             .....
           }
```
3. You  would need to specify a callback function to return the subgrid editUrl to that grid. The default is blank. Thee arguments passed to the callback function are:

  + `$sm` = servicelLocator;
  + ` $entity` = The current entity (FQCN)
  + `$fieldName` = the field name of the join column
  + `$targetEntity` = FQCN of the target entity
  + `$urlTypes` :
       - `const DYNAMIC_URL_TYPE_GRID = 1; `//this is the url to get data for the main
       - `const DYNAMIC_URL_TYPE_EDIT = 2;` // the editurl for main grid
       - `const DYNAMIC_URL_TYPE_SUBGRID = 3;` // th edit url for the subgrid for CRUD
      - `const DYNAMIC_URL_TYPE_ROW_EXPAND = 4;` //row expand url for subgridAsGrid to load data

4. Your route should cater for the fieldName parameter which would be picked up in your CRUD action.  Note that the "subgridid" is appended as a query parameter to the url. the "row_id" is a javaScript
variable that  would be replaced in the script when the subgrid editUrl is returned so just append it as shown in the example.
```php
         'jqgrid' => array(
              ........
              'grid_url_generator'  => function ($sm, $entity, $fieldName, $targetEntity, $urlType) {
                   switch($urlType){
                        .....
                         case  BaseGrid::DYNAMIC_URL_TYPE_ROW_EXPAND:
                          //@var $helper \Zend\View\Helper\Url
                           $helper = $sm->get('viewhelpermanager')->get('url');
                           $url    = $helper('your_route_name',
                                     array(
                                             'your_parameters',
                                             'fieldName' => $fieldName
                                     )
                             );
                          return new \Zend\Json\Expr("'$url?subgridid='+row_id");
                      }
                )
```
#Grid Specific Options (Multiple Grids)
If you have multiple grids with different config requirements you can have grid specific option set for each grid.

1. Add a unique ID for the grid by specifying the second parameter to the setGridIdentity() method in your controller action e.g.
```php
          ........
          $gridId = 'my_unique_id ;
          $grid = $serviceManager->get('jqgrid')->setGridIdentity( $className, $gridId);
          ......
```
2. In your module.config.php file add the grid specific options as follows:
```php
         return array(
              .......
              'jqgrid' => array (
                     ......
                     'my_unique_id' => array(
                                      .............
                      )
               )
         );
```
> The grid specific options would be merged into the main grid options for grids with that ID.

#Tree Grid
To render the tree grid, set the third parameter of the setGridIdentity() method to true in your controller action. This depends on the [Gedmo extension for doctrine](https://github.com/l3pp4rd/DoctrineExtensions). The entity class should implement the required Gedmo tree annotations for this to work;

#Custom Queries
The grid by default loads data into the grid from the specified entity without any WHERE clauses.
If you want to specify a WHERE clause to be used when populating the grid, you can do so by creating
a  custom QueryBuilder builder and setting it on the grid as shown below:

In Your controller:

```php
       
           ......
           $grid  = $serviceManager->get('jqgrid');

           $qb    = $grid->getEntityManager()->createQueryBuilder();
           $alias = $grid->getModel()->getAlias();

           $qb->where($alias . '.your_field', ':param')
                   ->setParameter(':param', 'your_value');

           $grid->setCustomQueryBuilder($qb);
           ........
```
Other filter parameters would be added to the QueryBuilder as normal. The only difference is that instead of creating a
new QueryBuilder, the modules uses the custom QueryBuilder. You also need to add the filter parameters to the url so that
paging on the grid, the filters would be applied. You do this by add custom filters to the grid when setting the grids
identity as show below:


```php
            
            $queryParam  = [
                'customFilters' => json_encode(
                    [
                        'groupOp' => 'AND',
                        'rules'   => [
                            [
                                'field' => 'isActive',
                                'op'    => 'eq',
                                'data'  => 1
                            ],
                            [
                                'field' => 'offerCount',
                                'op'    => 'gt',
                                'data'  => 0
                            ],
                            [
                                'field' => 'logo',
                                'op'    => 'eq',
                                'data'  => ''
                            ],
                        ]
                   ]
            )
        ];
     
        $grid = $this->getServiceLocator()->get('jqgrid');
        $grid->setGridIdentity($className, $entityKey, null, false, $queryParam);
     
#Configuration Options


```php

        'jqgrid'       => array(
                /**
                 * Allow retrieval of data from a different domain
                 * All CRUD request would be prefixed with the api_domain value
                 * e.g. http://www.example.com
                 */
                'api_domain'                        => '',
                /**
                 * Location where entity classname to entity key mappings are stored
                 */
                'entity_cache'                      => array(
                    'orm' => 'data/SynergyDataGrid/cache/orm_entity_classmap.php',
                    'odm' => 'data/SynergyDataGrid/cache/odm_entity_classmap.php',
                ),

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
            ),
```
