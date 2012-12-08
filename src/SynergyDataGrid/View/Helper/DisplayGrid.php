<?php
namespace SynergyDataGrid\View\Helper;

use Zend\Http\Request;
use Zend\View\Helper\AbstractHelper;
use SynergyDataGrid\Grid\JqGrid;
use Zend\Json\Json;

/**
 * View Helper to render jqGrid control
 *
 * @author Pele Odiase
 * @package mvcgrid
 */
class DisplayGrid extends AbstractHelper
{
    /**
     * Grid Instance
     *
     * @param \SynergyDataGrid\Grid\JqGrid $grid
     *
     * @return string
     */
    public function __invoke(JqGrid $grid)
    {
        $view = $this->getView();
        $html = array();
        $js = array();
        $onLoad = array();
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
        $js = array_merge($js, $datePicker);

        $html[] = '<table id="' . $grid->getId() . '"></table>';
        if ($grid->getNavGridEnabled()) {
            if ($grid->getIsDetailGrid()) {
                $grid->getNavGrid()->setSearch(false);
            }
            $jsPager = sprintf('jQuery("#%s").jqGrid("navGrid","#%s",%s,%s,%s,%s,%s,%s)',
                $grid->getId(),
                $grid->getPager(),
                Json::encode($grid->getNavGrid()->getOptions(), false, array('enableJsonExprFinder' => true)),
                Json::encode($grid->getNavGrid()->getEditParameters(), false, array('enableJsonExprFinder' => true)),
                Json::encode($grid->getNavGrid()->getAddParameters(), false, array('enableJsonExprFinder' => true)),
                Json::encode($grid->getNavGrid()->getDelParameters(), false, array('enableJsonExprFinder' => true)),
                Json::encode($grid->getNavGrid()->getSearchParameters(), false, array('enableJsonExprFinder' => true)),
                Json::encode($grid->getNavGrid()->getViewParameters(), false, array('enableJsonExprFinder' => true))
            );

            $navButtons = $grid->getNavButtons();

            if (is_array($navButtons)) {
                foreach ($navButtons as $title => $button) {
                    $jsPager .= sprintf('
                            .navButtonAdd("#%s",{
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
        $html[] = $htmlPager;

        if ($grid->getInlineNavEnabled()) {
            $jsInline = sprintf('jQuery("#%s").jqGrid("inlineNav", "#%s",%s)',
                $grid->getId(),
                $grid->getPager(),
                Json::encode($grid->getInlineNav()->getOptions(), false, array('enableJsonExprFinder' => true))
            );
            $jsInline .= ';';
            $onLoad[] = $jsInline;
            $onLoad[] = $grid->getProcessAfterSubmit();
            if (!$htmlPager) {
                $htmlPager = '<div id="' . $grid->getPager() . '"></div>';
            }
        }

        $onLoad[] = $grid->getJsCode()->renderActionsFormatter();

        $html = array_merge($html, $grid->getHtml());
        $js = array_merge($js, $grid->getJs());
        $onLoad = array_merge($onLoad, $grid->getOnload());

        $onLoadScript = 'jQuery(function(){' . implode("\n", $onLoad) . '});';

        $view->headScript()->offsetSetScript(100, $onLoadScript);
        $view->headScript()->offsetSetScript(105, implode("\n", $js));

        return implode("\n", $html);
    }

}