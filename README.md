SynergyDataGrid (ZF2 module)
===========================

-------------------------------------------------

Introduction
------------


SynergyDataGrid is a [Zend Framework 2](http://framework.zend.com/zf2) module facilitating usage of [JqGrid](http://www.trirand.com/blog/) in ZF2 applications.

It provide basic CRUD functionalities for editing  database tables as an AJAX-based grid.

To use all available plugin and library features please read jqGrid documentation at http://www.trirand.com/jqgridwiki/doku.php

Dependencies .
---------------
    Zend Framework 2 (http://framework.zend.com/),
    Doctrine 2.0 (http://www.doctrine-project.org/),
    jQuery 1.4.2 (http://jquery.com),
    jQuery UI 1.8.9 (http://jqueryui.com/),
    jqGrid plugin 4.3.1 (http://www.trirand.com/blog/),

Installation - manual
---------------------

1.   Go to your project's directory.
2.   Clone this project into your `./vendor/synergy/` directory as a `synergydatagrid` module:

     `git clone https://github.com/odiaseo/zf2-datagrid.git`
3. Copy all files in public directory to your project's public folder

Installation - with Composer
----------------------------

1.   Go to your project's directory.
2.   Edit your `composer.json` file and add `"synergy/synergydatagrid": "dev-master"` into `require` section.
3.   Run `php composer.phar install` (or `php composer.phar update`).
4.   Follow the Post installation steps bellow

Post Installation steps
------------------------------
Ensure that DoctrineORM is configured correctly

Usage.
--------------------
    In your controller class:

     <?php
	 use  SynergyDataGrid\Grid\JqGridFactory ;

        public function gridAction()
        {
			//replace {Entity_Name} with your entity name e.g. 'Application\Entity\User'

            $serviceManager = $this->getServiceLocator() ;
            $grid = $serviceManager->get('jqgrid')
                                   ->create({Entity_Name}, {grid_id});

            $grid->toolbar = array(true, 'bottom'); //optional
            $grid->toppager = true;
            $grid->altRows  = true;
            $grid->gridview = true;

            $grid->prepareGrid();

			return array('grid' => $grid);

        }
     ?>

     In your view script:
     <?php echo $this->displayGrid($this->grid); ?>

    In head section of your layout:
    <?php
    $this->headLink()->appendStylesheet('/jqGrid/css/ui.jqgrid.css')
                     ->appendStylesheet('/css/jquery.ui.datepicker.css')
                     ->appendStylesheet('/plugins/ui.multiselect.css') ;


    $this->headScript()->prependFile('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js', 'text/javascript')
      ->prependFile('http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js', 'text/javascript')
      ->appendFile('/jqGrid/js/i18n/grid.locale-en.js', 'text/javascript')
      ->appendFile('/plugins/ui.multiselect.js', 'text/javascript')
      ->appendFile('/jqGrid/js/jquery.jqGrid.min.js', 'text/javascript') ;
    ?>