<?php
    namespace SynergyDataGrid\View\Helper;

    use SynergyDataGrid\Grid\Toolbar;
    use Zend\Http\Request;
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
            $html   = array();
            $js     = array();
            $onLoad = array();

            $config = $grid->getConfig();

            if ($grid->getIsTreeGrid()) {
                $grid->setActionsColumn(false);
            } else {
                $grid->setActionsColumn($config['add_action_column']);
            }
            $grid->setGridColumns()
                ->setGridDisplayOptions()
                ->setAllowEditForm(!$config['add_action_column']);


            $onLoad[] = 'var ' . $grid->getLastSelectVariable() . '; ';

            if (!$grid->getEditurl()) {
                $grid->setEditurl($grid->getUrl());


            }
            $grid->getJsCode()->prepareAfterInsertRow();
            $grid->getJsCode()->prepareAfterSaveRow();
            $grid->getJsCode()->prepareOnEditRow();
            $grid->getJsCode()->prepareAfterRestoreRow();

            if ($grid->getAllowResizeColumns()) {
                $grid->prepareColumnSizes();
            }
            $grid->prepareSorting();
            $grid->preparePaging();

            $onLoad[] = $grid->getJsCode()->prepareSetColumnsOrderingCookie();
            $grid->reorderColumns();

            $onLoad[] = sprintf('jQuery("#%s").jqGrid(%s);',
                $grid->getId(), Json::encode($grid->getOptions(), false, array('enableJsonExprFinder' => true)));

            $datePicker = $grid->getDatePicker()->prepareDatepicker();
            $js         = array_merge($js, $datePicker);

            $html[] = '<table id="' . $grid->getId() . '"></table>';
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

                $jsPager = sprintf('jQuery("#%s").jqGrid("navGrid","#%s",%s,%s,%s,%s,%s,%s)',
                    $grid->getId(),
                    $grid->getPager(),
                    Json::encode($options, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmEdit, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmAdd, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmDel, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmSearch, false, array('enableJsonExprFinder' => true)),
                    Json::encode($prmView, false, array('enableJsonExprFinder' => true))
                );

                $navButtons = $grid->getNavButtons();

                if (is_array($navButtons)) {
                    foreach ($navButtons as $title => $button) {
                        $jsPager .= sprintf('.navButtonAdd("#%s",{
                            caption: "%s",
                            title: "%s",
                            buttonicon: "%s",
                            onClickButton: %s,
                            position: "%s",
                            cursor: "%s",
                            id: "%s"
                            })
                        ',
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

                $jsPager .= ';';
                $htmlPager = '<div id="' . $grid->getPager() . '"></div>';
            }

            $onLoad[] = $jsPager;
            $html[]   = $htmlPager;

            if ($grid->getInlineNavEnabled() and $grid->getInlineNav()) {
                $jsInline = sprintf('jQuery("#%s").jqGrid("inlineNav", "#%s",%s)',
                    $grid->getId(),
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

            if ($grid->toolbar) {
                $config  = $grid->getConfig();
                $toolbar = new Toolbar($grid, $config['toolbar_buttons']);

                /** @var $toolbarButton \SynergyDataGrid\Grid\Toolbar\Item */
                foreach ($toolbar->getItems() as $toolbarButton) {
                    $onLoad[] = sprintf("jQuery('#%s').append(\"<span id='%s' title='%s' class='toolbar-item'><i class='icon %s'></i></span>\");
                                        jQuery('#%s', '#%s').bind('click', %s);",
                        $toolbar->getId(),
                        $toolbarButton->getId(),
                        $toolbarButton->getTitle(),
                        $toolbarButton->getIcon(),
                        $toolbarButton->getId(),
                        $toolbar->getId(),
                        Json::encode($toolbarButton->getCallback(), false, array('enableJsonExprFinder' => true))
                    );
                }
            }

            $onLoad[] = sprintf(";jQuery(window).bind('resize', function(){
                var gw = jQuery('#%s').parents('.grid-data').width();
                jQuery('#%s').jqGrid('setGridWidth',gw)
                });
            ",
                $grid->getId(),
                $grid->getId());


            //$onLoad[] = $grid->getJsCode()->renderActionsFormatter();

            $html   = array_merge($html, $grid->getHtml());
            $js     = array_merge($js, $grid->getJs());
            $onLoad = array_merge($onLoad, $grid->getOnload());

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

    }