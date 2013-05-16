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
    jQuery >= 1.4.2 (http://jquery.com),
    jQuery UI 1.8.9 (http://jqueryui.com/),
    jqGrid plugin >= 4.3.1 (http://www.trirand.com/blog/),

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
            $grid = $serviceManager->get('jqgrid')->setGridIdentity({Entity_Name});
            /**
             * this is the url where CRUD operations would be done via ajax
             * :entity in the editurl could be any identifier or id.  You would need to
             * retrieve this and get the FQCN for use by the entity manager
             * e.g. :entity = $this->getEntityKey({Entity_Name});
             * @ see crudAction()
             */
            $url  = /ajax/:entity;
            $grid->setUrl($url);
            $grid->setCaption('My Caption'); //optional

			return array('grid' => $grid);

        }
         public function crudAction()
         {
            $response  = '';
            /**
             * Assumes that the entity can be retrieved from the ajax request
             * e.g /ajax/:entity
             * implement function to get the FQCN from :entity
             */
            $entity = $this->params()->fromRoute('entity', null);
            $className = $this->getClassname($entity);

            if ( $className) {
                $serviceManager = $this->getServiceLocator();
                $grid = $serviceManager->get('jqgrid')->setGridIdentity( $className);
                $response = $grid->prepareGridData();
            }

            return new JsonModel($response);
        }

        public function getEntityClassname($entityKey){
            //@TODO implement as required
            //return $entityClassname ;
        }

        public function getEntityKey($className){
         //@TODO implement as required ;
         //return $entity;
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