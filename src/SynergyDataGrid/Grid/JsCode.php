<?php
namespace SynergyDataGrid\Grid;

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
use SynergyDataGrid\Grid\GridType\BaseGrid;
use Laminas\Json\Encoder;
use Laminas\Json\Expr;

/**
 *
 * JsCode class is needed to prepare all javascript code for jqGrid plugin
 *
 * @author  Pele Odiase
 * @package mvcgrid
 */
class JsCode extends Base
{
    /**
     * JqGrid instance
     *
     * @var \SynergyDataGrid\Grid\GridType\BaseGrid
     */
    private $grid;
    /** user defined scripts
     *
     * @var array
     */
    protected $_customScripts = array();
    /**
     * CSS class applied to the grids container.
     *
     * @var string
     */
    protected $_containerClass = 'grid-data';

    protected $_padding;

    /**
     * @param BaseGrid $grid
     * @param int $padding
     */
    public function __construct(BaseGrid $grid = null, $padding = 0)
    {
        $this->grid     = $grid;
        $this->_padding = $padding;
    }

    public function addAutoResizeScript($gridId)
    {
        return $this->addCustomScript(
            new Expr(
                sprintf(
                    "jQuery(window).on('resize', function(){
                                                jQuery('#%s:visible').each(function(){
                                                    synergyResizeGrid(this, '.%s');
                                                });
                                        }); ",
                    $gridId,
                    $this->getContainerClass()
                )
            )
        );
    }

    /**
     * Add actions column to the grid with base controls (edit or editform, delete)
     *
     * @return void
     */
    public function addActionsColumn()
    {
        $options   = $this->grid->getConfig();
        $numCustom = count($this->grid->getRowActionButtons());
        $width     = 60 + ($numCustom * 15);

        $this->grid->addColumn(
            'Actions',
            array(
                'name'          => 'myac',
                'width'         => $width,
                'fixed'         => true,
                'sortable'      => false,
                'resizable'     => false,
                'formatter'     => $numCustom ? new Expr($this->getActionFunctioName()) : 'actions',
                'search'        => false,
                'classes'       => 'action-column',
                'viewable'      => false,
                'editable'      => false,
                'editrules'     => array(
                    'edithidden' => false
                ),
                'formatoptions' => array(
                    'keys'           => false,
                    'editbutton'     => $options['nav_grid']['edit'],
                    'editformbutton' => $this->grid->getAllowEditForm(),
                    'delbutton'      => $options['nav_grid']['del'],
                    'delOptions'     => $options['delete_parameters'],
                    'editOptions'    => $options['edit_parameters'],
                    'onError'        => new Expr("function(rowid,response) {
                                                                    var json = eval('(' + response.responseText + ')');
                                                                   alert('Error saving row: ' + json.message);
                                                                   jQuery('#" . $this->grid->getGridId() . "').restoreAfterError = false;
                                                                         return true;
                                                                   }
                                                                   ")
                ))
        );
    }

    public function getActionFunctioName()
    {
        return $this->grid->getId() . '_actions';
    }

    protected function getCustomButtons($rowId)
    {
    }

    /**
     * Prepare formatter and attach standard buttons for every row, as well as custom buttons, if needed
     * Code partially taken from jquery.jqGrid.src.js and must be updated if switching to next jqGrid verison
     *
     * @return string
     */
    public function renderActionsFormatter()
    {
        $btns = $this->grid->getRowActionButtons() ?: new \stdClass();
        $btns = Encoder::encode($btns);

        $formatterCode
            = <<<ACTION

               ;function {$this->getActionFunctioName()}(cellval,opts, rwd) {
                    var rowid = opts.rowId ;
                    if(rowid === undefined || $.fmatter.isEmpty(rowid)) {
                        return "";
                    }
                    var ctm = {$btns};
                    var str = $.fn.fmatter.actions(cellval,opts) ;
                    var res = $(str);

                    if(ctm){
                        var saveBtn = res.find('[id^="jSaveButton"]');
                        var addStr = '';
                        for(var b in ctm){
                            addStr += "<div title='"+ctm[b]['name']+"' style='float:left;cursor:pointer;' class='"+ctm[b]['class']+"' id='jButton_"+rowid+"' onmouseover=jQuery(this).addClass('ui-state-hover');  onmouseout=jQuery(this).removeClass('ui-state-hover'); onclick="+ctm[b]['action']+" data-rowid='"+rowid+"'><span class='"+ctm[b]['icon']+"'></span></div>"
                        }
                        saveBtn.before(addStr);
                    }
                    return res.wrap('<div />').html() ;
                 }
ACTION;

        return $formatterCode;
    }

    /**
     * Prepare javscript code to show custom row button
     *
     * @param string $id custom button id
     *
     * @return string
     */
    public function getCustomButtonsShow($id = '')
    {
        $customButtonsShow = "";
        $rowActionButtons  = $this->grid->getRowActionButtons();
        foreach ($rowActionButtons as $button) {
            if (!$id) {
                $customButtonsShow
                    .= "
                    id = (typeof elem == 'undefined') ? rowid : elem;
                    jQuery('tr#' + id + ' div." . $button['class'] . "').show();
                    ";
            } else {
                $customButtonsShow .= "
                    jQuery('tr#$id div." . $button['class'] . "').show();
                    ";
            }
        }

        return $customButtonsShow;
    }

    /**
     * Prepare javscript code to hide custom row button
     *
     * @param string $id custom button id
     *
     * @return string
     */
    public function getCustomButtonsHide($id = '')
    {
        $customButtonsHide = "";
        $rowActionButtons  = $this->grid->getRowActionButtons();
        foreach ($rowActionButtons as $button) {
            if (!$id) {
                $customButtonsHide
                    .= "
                    id = (typeof elem == 'undefined') ? rowid : elem;
                    jQuery('tr#' + id + ' div." . $button['class'] . "').hide();
                    ";
            } else {
                $customButtonsHide .= "
                    jQuery('tr#$id div." . $button['class'] . "').hide();
                    ";
            }
        }

        return $customButtonsHide;
    }

    /**
     * Prepare javscript code for afterInsertRow jqGrid event (show/hide buttons)
     *
     * @return $this
     */
    public function prepareAfterInsertRow()
    {
        $this->grid->setAfterInsertRow(
            new Expr("
            function(rowid,rowdata,rowelem) { 
                if (rowid == 'new_row') {
                    jQuery('tr#new_row div.ui-inline-edit, tr#new_row div.ui-inline-del').hide();
                    jQuery('tr#new_row div.ui-inline-save, tr#new_row div.ui-inline-cancel').show();
                    " . $this->getCustomButtonsHide('new_row') . "
                }    
        }
        ")
        );

        return $this;
    }

    /**
     *  Prepare javscript code for afterSaveRow jqGrid event (show/hide row buttons and bind needed events to them)
     *
     * @return $this
     */
    public function prepareAfterSaveRow()
    {
        // process successfull row adding and editing
        if ($actionColumn = $this->grid->getColumn('myac')) {
            $actionColumn->mergeFormatOptions(
                array('afterSave' => new Expr("function(rowid, response) {
                    var json = eval('(' + response.responseText + ')');
                    if (json.success && json.id != 'new_row' && rowid == 'new_row') {
                        //change id for saved row
                        jQuery('#" . $this->grid->getId() . "').jqGrid('setRowData',rowid,{id:json.id});
                        jQuery('#" . $this->grid->getId() . "_iladd').removeClass('ui-state-disabled');
                        //swap buttons when exiting from edit mode
                        jQuery('tr#new_row div.ui-inline-edit, tr#new_row div.ui-inline-del').show();
                        jQuery('tr#new_row div.ui-inline-save, tr#new_row div.ui-inline-cancel').hide();
                        " . $this->getCustomButtonsShow() . "
                        //remove old inine events for a new row
                        jQuery('#new_row > td > div > div.ui-inline-edit').unbind('click').removeAttr('onclick');
                        jQuery('#new_row > td > div > div.ui-inline-del').unbind('click').removeAttr('onclick');
                        jQuery('#new_row > td > div > div.ui-inline-save').unbind('click').removeAttr('onclick');
                        jQuery('#new_row > td > div > div.ui-inline-cancel').unbind('click').removeAttr('onclick');
                        //change id for saved row
                        jQuery('#new_row').attr('id', json.id).removeClass('jqgrid-new-row');
                        //bind inine events for a new row
                        jQuery('#' + json.id + ' > td > div > div.ui-inline-del').bind('click',function() {jQuery.fn.fmatter.rowactions(json.id,'categories','del',0)});
                        jQuery('#' + json.id + ' > td > div > div.ui-inline-edit').bind('click',function() {jQuery.fn.fmatter.rowactions(json.id,'categories','edit',0)});
                        jQuery('#' + json.id + ' > td > div > div.ui-inline-save').bind('click',function() {jQuery.fn.fmatter.rowactions(json.id,'categories','save',0)});
                        jQuery('#' + json.id + ' > td > div > div.ui-inline-cancel').bind('click',function() {jQuery.fn.fmatter.rowactions(json.id,'categories','cancel',0)});
                    } else if (rowid != 'new_row') {
                        " . $this->getCustomButtonsShow() . "
                    }
                    if (!json.success && rowid == 'new_row') {
                        jQuery('#" . $this->grid->getId() . "').jqGrid('setRowData',rowid,{id:0});  
                    }        
                    return false;
                    }   
                "))
            );
        }

        return $this;
    }

    /**
     * Prepare javscript code for onEditRow jqGrid event (setup DatePicker and hide row buttons)
     *
     * @return $this
     */
    public function prepareOnEditRow()
    {
        if ($actionColumn = $this->grid->getColumn('myac')) {
            if ($datePicker = $this->grid->getDatePicker()) {
                $datePickerFunctionName = $datePicker->getFunctionName();
                // this function will work only onEdit, but not on add row
                $actionColumn->mergeFormatOptions(
                    array('onEdit' => new Expr("function (elem){ " . $datePickerFunctionName . "(elem); " .
                        $this->getCustomButtonsHide() . " }  "))
                );
            }
        }

        return $this;
    }

    /**
     * Prepare javscript code for afterRestoreRow jqGrid event (show row buttons, and enable add row button)
     *
     * @return $this
     */
    public function prepareAfterRestoreRow()
    {
        // enable "add row" button
        if ($actionColumn = $this->grid->getColumn('myac')) {
            $actionColumn->mergeFormatOptions(
                array('afterRestore' => new Expr("function(rowid,response) {
            jQuery('#" . $this->grid->getId() . "_iladd').removeClass('ui-state-disabled');
            " . $this->getCustomButtonsShow() . "    
        } 
        "))
            );
        }

        return $this;
    }

    /**
     * Prepare javscript code for save columns size data in cookies
     *
     * @return Expr
     */
    public function prepareSetColumnSizeCookie()
    {
        return new Expr("
          function(newwidth, index) {
                var colModel = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','colModel');
                var columnName = colModel[index].name;
                var columnSizesCookieName = '" . BaseGrid::COOKIE_COLUMNS_SIZES_PREFIX .
            $this->grid->getId() . "';
                columnSizesCookieName = columnSizesCookieName.toLowerCase().replace(/\//g,'_');
                var currentValues = jQuery.cookie(columnSizesCookieName);
                var found = false;
                var newValue = '';
                var colInfo = [];
                if (currentValues) {
                    var valuesArray = currentValues.split(';');
                    for (i = 0; i < valuesArray.length; i++) {
                        colInfo = valuesArray[i].split(':');
                        if (colInfo[0] == columnName) {
                            found = true;
                            newValue += colInfo[0] + ':' + newwidth + ';';
                        } else {
                            newValue += colInfo[0] + ':' + colInfo[1] + ';';
                        }
                    }
                }
                if (!found) {
                    newValue += columnName + ':' + newwidth;
                } else {
                    newValue = newValue.substr(0, newValue.length - 1);
                }
                jQuery.cookie(columnSizesCookieName, newValue, { expires: 30, path: '/' }); 
                " . ($this->grid->getReloadAfterResize() ? "window.document.location.reload();" : "") . "
          }
            ");
    }

    /**
     * Prepare javscript code for save information about columns sorting in cookies
     *
     * @return Expr
     */
    public function prepareSetSortingCookie()
    {
        return new Expr("
          function(index,iCol,sortorder) {
                var sortingCookieName = '" . BaseGrid::COOKIE_SORTING_PREFIX . $this->grid->getId() . "';
                sortingCookieName = sortingCookieName.toLowerCase().replace(/\//g,'_');
                var newValue = index + ':' + sortorder;
                jQuery.cookie(sortingCookieName, newValue, { expires: 30, path: '/' }); 
          }
            ");
    }

    /**
     * Prepare javscript code for save pagination settings in cookies
     *
     * @return Expr
     */
    public function prepareSetPagingCookie()
    {
        return new Expr("
          function(pgButton) {
                var pagingCookieName = '" . BaseGrid::COOKIE_PAGING_PREFIX . $this->grid->getId() . "';
                pagingCookieName = pagingCookieName.toLowerCase().replace(/\//g,'_');
                var newValue = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','rowNum');
                jQuery.cookie(pagingCookieName, newValue, { expires: 30, path: '/' }); 
          }
            ");
    }

    /**
     * Prepare javscript code for save information about columns ordering in cookies
     *
     * @return Expr
     */
    public function prepareSetColumnsOrderingCookie()
    {
        return new Expr("
         jQuery('body').delegate('#gbox_' + '" . $this->grid->getId() . "', 'sortstop', 
            function(event, ui) {
                var orderingCookieName = '" . BaseGrid::COOKIE_COLUMNS_ORDERING_PREFIX . $this->grid->getId() . "';
                orderingCookieName = orderingCookieName.toLowerCase().replace(/\//g,'_');
                var colModel = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','colModel');
                var newValue = '';
                for (i = 0; i < colModel.length; i++) {
                    newValue += colModel[i].name + ':';
                }
                newValue = newValue.substr(0, newValue.length - 1);
                jQuery.cookie(orderingCookieName, newValue, { expires: 30, path: '/' }); 
                " . ($this->grid->getReloadAfterChangeColumnsOrdering() ? "window.document.location.reload();" : "") . "
            });
            ");
    }

    /**
     * Prepare javscript code for detail grid loading
     *
     * @param string $detailGridId id of details grid
     * @param string $detailFieldName name of field (in detail grid) to connect master and detail grids
     * @param string $captionPrefix prefix of caption for detail grid
     *
     * @return string
     */
    public function prepareDetailCode($detailGridId = '', $detailFieldName = '', $captionPrefix = '')
    {
        $retv = '';
        if ($detailGridId && $detailFieldName) {
            $retv = new Expr("
                            function(ids) { 
                                url = jQuery('#" . $detailGridId . "').jqGrid('getGridParam','url');
                                if (ids == null) { 
                                    ids=0; 
                                    if (jQuery('#" . $detailGridId . "').jqGrid('getGridParam','records') > 0 ) { 
                                        jQuery('#" . $detailGridId
                . "').jqGrid('setGridParam',{url: url + '?_search=true&page=1&searchField=" . $detailFieldName . "&searchOper=eq&searchString='+ids,page:1});
                                        jQuery('#" . $detailGridId . "').jqGrid('setCaption','" . $captionPrefix . ": '+ids).trigger('reloadGrid');
                                    } 
                                } 
                                else 
                                { 
                                    jQuery('#" . $detailGridId
                . "').jqGrid('setGridParam',{url: url + '?_search=true&page=1&searchField=" . $detailFieldName . "&searchOper=eq&searchString='+ids,page:1});
                                    jQuery('#" . $detailGridId . "').jqGrid('setCaption','" . $captionPrefix . ": '+ids);
                                    setTimeout(\"jQuery('#" . $detailGridId . "').trigger('reloadGrid')\", 100);
                                } 
                            }                     
             ");
        }

        return $retv;
    }

    /**
     * Prepare javscript code for details grid for gridComplete event (select first row to load corresponding details)
     *
     * @return Expr
     */
    public function prepareDetailCodeGridComplete()
    {
        return new Expr("
                    function() { 
                        gridIds = jQuery('#" . $this->grid->getId() . "').jqGrid('getDataIDs');
                        if (gridIds.length > 0) {
                            jQuery('#" . $this->grid->getId() . "').jqGrid('setSelection', gridIds[0]);
                        }    
                    }
                ");
    }

    /**
     * @param array $customScripts
     *
     * @return $this
     */
    public function setCustomScripts(array $customScripts)
    {
        $this->_customScripts = $customScripts;

        return $this;
    }

    /**
     * @param $customScripts
     *
     * @return $this
     */
    public function addCustomScript($customScripts)
    {
        $this->_customScripts[] = $customScripts;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomScripts()
    {
        return $this->_customScripts;
    }

    /**
     * @param $containerClass
     *
     * @return $this
     */
    public function setContainerClass($containerClass)
    {
        $this->_containerClass = $containerClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getContainerClass()
    {
        return $this->_containerClass;
    }

    public function setPadding($padding)
    {
        $this->_padding = $padding;

        return $this;
    }

    public function getPadding()
    {
        return $this->_padding;
    }
}