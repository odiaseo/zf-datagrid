<?php
    namespace SynergyDataGrid\View\Helper;

    use SynergyDataGrid\Grid\Toolbar;
    use Zend\Http\Request;
    use Zend\Json\Expr;
    use Zend\Stdlib\Parameters;
    use Zend\View\Helper\AbstractHelper;
    use SynergyDataGrid\Grid\JqGridFactory;
    use Zend\Json\Json;

    /**
     * View Helper to render jqGrid control
     *
     * @author  Pele Odiase
     * @package mvcgrid
     */
    class DisplayGrid extends AbstractHelper
    {
        /**
         * Grid Instance
         *
         * @param $grid \SynergyDataGrid\Grid\JqGridFactory
         *
         * @return string
         */
        public function __invoke(JqGridFactory $grid)
        {
            list($onLoad, $js, $html) = $this->initGrid($grid);
            $config = $grid->getConfig();

            $onLoadScript = ';jQuery(function(){' . implode("\n", $onLoad) . '});';

            if ($config['render_script_as_template']) {
                $this->getView()->headScript()
                    ->appendScript($onLoadScript, 'text/x-jquery-tmpl', array("id='grid-script'", 'noescape' => true))
                    ->appendScript(implode("\n", $js));
            } else {
                $this->getView()->headScript()
                    ->appendScript($onLoadScript)
                    ->appendScript(implode("\n", $js));
            }

            return implode("\n", $html);
        }

        public function initGrid(JqGridFactory $grid)
        {
            $html        = array();
            $js          = array();
            $onLoad      = array();
            $postCommand = array();

            $config = $grid->getConfig();
            $gridId = $grid->getId();


            if ($grid->getIsTreeGrid()) {
                $grid->setActionsColumn(false);
            } else {
                $grid->setActionsColumn($config['add_action_column']);
            }

            $grid->setGridColumns()
                ->setGridDisplayOptions()
                ->setAllowEditForm($config['allow_form_edit']);

            $onLoad[] = 'var ' . $grid->getLastSelectVariable() . '; ';
            $onLoad[] = sprintf('var %s = jQuery("#%s");', $gridId, $gridId);
            $onLoad[] = sprintf('%s.data("lastsel", 0);', $gridId);

            if (!$grid->getEditurl()) {
                $grid->setEditurl($grid->getUrl());
            }

            //get previous sort order from cookie if set
            $grid->prepareSorting();

            //get number per page if set in cookie
            $grid->preparePaging();

            //load first data for main grid and not subgrids
            if ($config['first_data_as_local'] and !$grid->getIsDetailGrid()) {
                $grid->setDatatype('local');

                $gridOptions = $grid->getOptions();
                $params      = array(
                    'page' => 1,
                    'rows' => $gridOptions['rowNum']
                );

                if ($grid->getIsTreeGrid()) {
                    $grid->setSortname('lft');

                    if (isset($gridOptions['postData'])) {
                        $params = array_merge($params, $gridOptions['postData']);
                    }
                }

                $request    = new Request();
                $parameters = new Parameters($params);
                $request->setPost($parameters);
                $initialData = $grid->getFirstDataAsLocal($request);

                $postCommand[] = sprintf('%s.jqGrid("setGridParam", {datatype:"json", treedatatype : "json"});',
                    $gridId, $grid->getEditurl());
                $postCommand[] = sprintf('%s[0].addJSONData(%s) ;', $gridId, Json::encode($initialData));

            }

            $grid->getJsCode()->prepareAfterInsertRow();
            $grid->getJsCode()->prepareAfterSaveRow();
            $grid->getJsCode()->prepareOnEditRow();
            $grid->getJsCode()->prepareAfterRestoreRow();

            if ($grid->getAllowResizeColumns()) {
                $grid->prepareColumnSizes();
            }


            $onLoad[] = $grid->getJsCode()->prepareSetColumnsOrderingCookie();
            $grid->reorderColumns();

            $onLoad[] = sprintf('%s.jqGrid(%s);', $gridId,
                Json::encode($grid->getOptions(), false, array('enableJsonExprFinder' => true)));

            $datePicker = $grid->getDatePicker()->prepareDatepicker();
            $js = array_merge($js, $datePicker);

            $html[] = '<table id="' . $gridId . '"></table>';
            if ($grid->getNavGridEnabled()) {
                if ($grid->getIsDetailGrid()) {
                    $grid->getNavGrid()->setSearch(false);
                }

                $options   = $grid->getNavGrid()->getOptions() ? : new \stdClass();
                $prmEdit   = $grid->getNavGrid()->getEditParameters() ? : new \stdClass();
                $prmAdd    = $grid->getNavGrid()->getAddParameters() ? : new \stdClass();
                $prmDel    = $grid->getNavGrid()->getDelParameters() ? : new \stdClass();
                $prmSearch = $grid->getNavGrid()->getSearchParameters() ? : new \stdClass();
                $prmView   = $grid->getNavGrid()->getViewParameters() ? : new \stdClass();

                $jsPager = sprintf('%s.jqGrid("navGrid","#%s",%s,%s,%s,%s,%s,%s);',
                    $gridId,
                    $grid->getPager(),
                    Json::encode($options, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmEdit, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmAdd, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmDel, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmSearch, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmView, false, array('enableJsonExprFinder' => true))
                );


                //display filter toolbar
                if ($config['filter_toolbar']['enabled']) {
                    $onLoad[] = sprintf('%s.jqGrid("filterToolbar",%s);',
                        $gridId,
                        Json::encode($config['filter_toolbar']['options'], false, array('enableJsonExprFinder' => true))
                    );
                }

                $navButtons = $grid->getNavButtons();

                if (is_array($navButtons)) {
                    foreach ($navButtons as $title => $button) {
                        $jsPager .= sprintf('%s.navButtonAdd("#%s",{
                            caption: "%s",
                            title: "%s",
                            buttonicon: "%s",
                            onClickButton: %s,
                            position: "%s",
                            cursor: "%s",
                            id: "%s"
                            });
                        ',
                            $gridId,
                            $grid->getPager(),
                            $button['caption'],
                            $title,
                            $button['icon'],
                            $button['action'],
                            $button['position'],
                            $button['cursor'],
                            $button['id']
                        );
                    }
                }
                $htmlPager = '<div id="' . $grid->getPager() . '"></div>';
            }

            $onLoad[] = $jsPager;
            $html[]   = $htmlPager;

            //setup inline navigation
            if ($grid->getInlineNavEnabled() and $grid->getInlineNav()) {
                $jsInline = sprintf('%s.jqGrid("inlineNav", "#%s",%s)',
                    $gridId,
                    $grid->getPager(),
                    Json::encode($grid->getInlineNav()->getOptions(), false, array('enableJsonExprFinder' => true))
                );
                $jsInline .= ';';
                $onLoad[] = $jsInline;
                $onLoad[] = $grid->getProcessAfterSubmit();
                if (!$htmlPager) {
                    $html[] = '<div id="' . $grid->getPager() . '"></div>';
                }
            }

            //add custom toolbar buttons
            list($toolbarEnabled, $toolbarPosition) = $grid->toolbar;
            if ($toolbarEnabled and $config['toolbar_buttons']) {

                if ($toolbarPosition == Toolbar::POSITION_BOTH) {
                    $toolbars[] = new Toolbar($grid, $config['toolbar_buttons'], Toolbar::POSITION_BOTTOM);
                    $toolbars[] = new Toolbar($grid, $config['toolbar_buttons'], Toolbar::POSITION_TOP);

                } elseif ($toolbarPosition == Toolbar::POSITION_BOTTOM) {
                    $toolbars[] = new Toolbar($grid, $config['toolbar_buttons'], Toolbar::POSITION_BOTTOM);
                } else {
                    $toolbars[] = new Toolbar($grid, $config['toolbar_buttons'], Toolbar::POSITION_TOP);
                }

                /** @var $toolbarButton \SynergyDataGrid\Grid\Toolbar\Item */
                foreach ($toolbars as $toolbar) {
                    $toolbarId       = $toolbar->getId();
                    $toolbarPosition = $toolbar->getPosition();
                    $onLoad[]        = sprintf("var %s = jQuery('#%s');", $toolbarId, $toolbarId);
                    $onLoad[]        = sprintf(";%s.data('grid', %s).addClass('grid-toolbar btn-group grid-toolbar-%s');", $toolbarId, $gridId, $toolbarPosition);

                    foreach ($toolbar->getItems() as $toolbarButton) {
                        $buttonPosition = $toolbarButton->getPosition();
                        if ($buttonPosition == Toolbar::POSITION_BOTH
                            or $buttonPosition == $toolbarPosition
                        ) {
                            $onLoad[] = sprintf("%s.append(\"<button data-toolbar-id='%s' id='%s' title='%s' class='%s' %s><i class='icon %s'></i> %s</button>\");
                                        jQuery('#%s', '#%s').bind('click', %s);",
                                $toolbarId,
                                $toolbarId,
                                $toolbarButton->getId(),
                                $toolbarButton->getTitle(),
                                $toolbarButton->getClass(),
                                $toolbarButton->getAttributes(),
                                $toolbarButton->getIcon(),
                                $toolbarButton->getTitle(),
                                $toolbarButton->getId(),
                                $toolbar->getId(),
                                Json::encode($toolbarButton->getCallback(), false, array('enableJsonExprFinder' => true))
                            );

                            if ($init = $toolbarButton->getOnLoad()) {
                                $onLoad[] = Json::encode($init, false, array('enableJsonExprFinder' => true));
                            }
                        }
                    }
                }
            }

            $onLoad = array_merge($onLoad, $postCommand);
            $onLoad = array_filter($onLoad);

            foreach ($grid->getJsCode()->getCustomScripts() as $script) {
                $onLoad[] = Json::encode($script, false, array('enableJsonExprFinder' => true));
            }

            $html   = array_merge($html, $grid->getHtml());
            $js     = array_merge($js, $grid->getJs());
            $onLoad = array_merge($onLoad, $grid->getOnload());

            if ($config['compress_script']) {
                $onLoad = $this->compressJavaScriptScript($onLoad);
                $js     = $this->compressJavaScriptScript($js);
            }

            return array($onLoad, $js, $html);

        }

        /**
         * Compress javascript code, removes whitespaces
         *
         * @param $script
         *
         * @return mixed
         */
        protected function compressJavaScriptScript($script)
        {
            $regex = array(
                "/(>|;|\"|\}|\,|\{|\.|\'|\|)( *)?(\r\n|\s)+/" => "$1 ",
                "/(\r\n){2,}/"                                => "\r\n", "/(\t| {2,})/" => ' '
            );

            return preg_replace(array_keys($regex), array_values($regex), $script);
        }
    }