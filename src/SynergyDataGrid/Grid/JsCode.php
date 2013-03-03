<?php
namespace SynergyDataGrid\Grid;

use SynergyDataGrid\Grid\Column;
use SynergyDataGrid\Util\ArrayUtils;
use SynergyDataGrid\Grid\JqGridFactory;
use Zend\Json\Expr;

/**203
 *
 * JsCode class is needed to prepare all javascript code for jqGrid plugin
 *
 * @author Pele Odiase
 * @package mvcgrid
 */
class JsCode extends Base
{
    /**
     * JqGrid instance
     *
     * @var string
     */
    private $grid;

    /**
     * Set up base JsCode options
     *
     * @param JqGridFactory $grid
     */

    public function __construct(JqGridFactory $grid = null)
    {
        $this->grid = $grid;
    }

    /**
     * Add actions column to the grid with base controls (edit or editform, delete)
     *
     * @return void
     */
    public function addActionsColumn()
    {
        $datePickerFunctionName = $this->grid->getDatePicker()->getFunctionName();
        $this->grid->addColumn('Actions', array('name' => 'myac',
            'width' => 80,
            'fixed' => true,
            'sortable' => false,
            'resize' => false,
            'formatter' => 'mactions',
            'formatoptions' => array('keys' => true,
                'editbutton' => $this->grid->getAllowEdit(),
                'editformbutton' => $this->grid->getAllowEditForm(),
                'delbutton' => $this->grid->getAllowDelete(),
                'delOptions' => array(
                    'afterSubmit' => new Expr("function(response, postdata) {  var json = eval('(' + response.responseText + ')');
                                               return [json.success, json.message];
                                               }
                                              ")
                ),
                'editOptions' => array( 'closeOnEscape' => true,
                                        'afterShowForm' => new Expr("function (elem){ $datePickerFunctionName(elem); }")
                ),
                'onError' => new Expr("function(rowid,response) {  var json = eval('(' + response.responseText + ')');
                                                                   alert('Error saving row: ' + json.message);
                                                                   jQuery('#" . $this->grid->getGridId() . "').restoreAfterErorr = false;
                                                                         return true;
                                                                   }
                                                                   ")
            )));
    }

    /**
     * Prepare formatter and attach standard buttons for every row, as well as custom buttons, if needed
     * Code partially taken from jquery.jqGrid.src.js and must be updated if switching to next jqGrid verison
     *
     * @return string
     */
    public function renderActionsFormatter()
    {
        $formatterCode = '
            jQuery.extend($.fn.fmatter , {
                mactions : function(cellval,opts, rwd) {
                            var op ={keys:false, editbutton:true, delbutton:true, editformbutton: false};
                            if(!$.fmatter.isUndefined(opts.colModel.formatoptions)) {
                                    op = $.extend(op,opts.colModel.formatoptions);
                            }
                            var rowid = opts.rowId, str="",ocl;
                            if(typeof(rowid) ==\'undefined\' || $.fmatter.isEmpty(rowid)) {return "";}
                            if(op.editformbutton){
                                    ocl = "onclick=jQuery.fn.fmatter.rowactions(\'"+rowid+"\',\'"+opts.gid+"\',\'formedit\',"+opts.pos+"); onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\'); "
                                    str =str+ "<div title=\'"+$.jgrid.nav.edittitle+"\' style=\'float:left;cursor:pointer;\' class=\'ui-pg-div ui-inline-edit\' "+ocl+"><span class=\'ui-icon ui-icon-pencil\'></span></div>";
                            } else 	if(op.editbutton){
                                    ocl = "onclick=jQuery.fn.fmatter.rowactions(\'"+rowid+"\',\'"+opts.gid+"\',\'edit\',"+opts.pos+"); onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\') ";
                                    str =str+ "<div title=\'"+$.jgrid.nav.edittitle+"\' style=\'float:left;cursor:pointer;\' class=\'ui-pg-div ui-inline-edit\' "+ocl+"><span class=\'ui-icon ui-icon-pencil\'></span></div>";
                            }
                            if(op.delbutton) {
                                    ocl = "onclick=jQuery.fn.fmatter.rowactions(\'"+rowid+"\',\'"+opts.gid+"\',\'del\',"+opts.pos+"); onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\'); ";
                                    str = str+"<div title=\'"+$.jgrid.nav.deltitle+"\' style=\'float:left;margin-left:5px;\' class=\'ui-pg-div ui-inline-del\' "+ocl+"><span class=\'ui-icon ui-icon-trash\'></span></div>";
                            }
        ';
        $rowActionButtons = $this->grid->getRowActionButtons();
        foreach ($rowActionButtons as $button) {
            $formatterCode .= '
                ocl = "onclick=\"' . $button['action'] . '\"; onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\'); ";
                str = str+"<div title=\'' . $button['name'] . '\' style=\'float:left;margin-left:5px;\' class=\'ui-pg-div ' . $button['class'] . '\' "+ocl+"><span class=\'ui-icon ' . $button['icon'] . '\'></span></div>";
                ';
        }

        $formatterCode .= '
                            ocl = "onclick=jQuery.fn.fmatter.rowactions(\'"+rowid+"\',\'"+opts.gid+"\',\'save\',"+opts.pos+"); onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\'); ";
                            str = str+"<div title=\'"+$.jgrid.edit.bSubmit+"\' style=\'float:left;display:none\' class=\'ui-pg-div ui-inline-save\' "+ocl+"><span class=\'ui-icon ui-icon-disk\'></span></div>";
                            ocl = "onclick=jQuery.fn.fmatter.rowactions(\'"+rowid+"\',\'"+opts.gid+"\',\'cancel\',"+opts.pos+"); onmouseover=jQuery(this).addClass(\'ui-state-hover\'); onmouseout=jQuery(this).removeClass(\'ui-state-hover\'); ";
                            str = str+"<div title=\'"+$.jgrid.edit.bCancel+"\' style=\'float:left;display:none;margin-left:5px;\' class=\'ui-pg-div ui-inline-cancel\' "+ocl+"><span class=\'ui-icon ui-icon-cancel\'></span></div>";
                            return "<div style=\'margin-left:8px;\'>" + str + "</div>";
                    }
            });
        ';

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
        $rowActionButtons = $this->grid->getRowActionButtons();
        foreach ($rowActionButtons as $button) {
            if (!$id) {
                $customButtonsShow .= "
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
        $rowActionButtons = $this->grid->getRowActionButtons();
        foreach ($rowActionButtons as $button) {
            if (!$id) {
                $customButtonsHide .= "
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
     * @param string $id custom button id
     *
     * @return void
     */
    public function prepareAfterInsertRow()
    {
        $this->grid->setAfterInsertRow(new Expr("
            function(rowid,rowdata,rowelem) { 
                if (rowid == 'new_row') {
                    jQuery('tr#new_row div.ui-inline-edit, tr#new_row div.ui-inline-del').hide();
                    jQuery('tr#new_row div.ui-inline-save, tr#new_row div.ui-inline-cancel').show();
                    " . $this->getCustomButtonsHide('new_row') . "
                }    
        }
        "));
    }

    /**
     * Prepare javscript code for afterSaveRow jqGrid event (show/hide row buttons and bind needed events to them)
     *
     * @param string $id custom button id
     *
     * @return void
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
                ")));
        }
    }

    /**
     * Prepare javscript code for onEditRow jqGrid event (setup DatePicker and hide row buttons)
     *
     * @return void
     */
    public function prepareOnEditRow()
    {
        if ($actionColumn = $this->grid->getColumn('myac')) {
            $datePickerFunctionName = $this->grid->getDatePicker()->getFunctionName();
            // this function will work only onEdit, but not on add row
            $actionColumn->mergeFormatOptions(
                array('onEdit' => new Expr("function (elem){
            " . $datePickerFunctionName . "(elem); " .
                    $this->getCustomButtonsHide() . "
            }
        ")));
        }
    }

    /**
     * Prepare javscript code for afterRestoreRow jqGrid event (show row buttons, and enable add row button)
     *
     * @return void
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
        ")));
        }
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
                colModel = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','colModel');
                columnName = colModel[index].name;
                columnSizesCookieName = '" . JqGrid::COOKIE_COLUMNS_SIZES_PREFIX . "' + window.location.pathname + '_' + '" . $this->grid->getId() . "';
                columnSizesCookieName = columnSizesCookieName.toLowerCase().replace(/\//g,'_');
                currentValues = jQuery.cookie(columnSizesCookieName);
                found = false;
                newValue = '';
                if (currentValues) {
                    valuesArray = currentValues.split(';');
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
                sortingCookieName = '" . JqGridFactory::COOKIE_SORTING_PREFIX . "' + window.location.pathname + '_' + '" . $this->grid->getId() . "';
                sortingCookieName = sortingCookieName.toLowerCase().replace(/\//g,'_');
                newValue = index + ':' + sortorder;
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
                pagingCookieName = '" . JqGridFactory::COOKIE_PAGING_PREFIX . "' + window.location.pathname + '_' + '" . $this->grid->getId() . "';
                pagingCookieName = pagingCookieName.toLowerCase().replace(/\//g,'_');
                newValue = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','rowNum');
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
                orderingCookieName = '" . JqGridFactory::COOKIE_COLUMNS_ORDERING_PREFIX . "' + window.location.pathname + '_' + '" . $this->grid->getId() . "';
                orderingCookieName = orderingCookieName.toLowerCase().replace(/\//g,'_');
                colModel = $('#" . $this->grid->getId() . "').jqGrid('getGridParam','colModel');
                newValue = '';
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
                                        jQuery('#" . $detailGridId . "').jqGrid('setGridParam',{url: url + '?_search=true&page=1&searchField=" . $detailFieldName . "&searchOper=eq&searchString='+ids,page:1}); 
                                        jQuery('#" . $detailGridId . "').jqGrid('setCaption','" . $captionPrefix . ": '+ids).trigger('reloadGrid');
                                    } 
                                } 
                                else 
                                { 
                                    jQuery('#" . $detailGridId . "').jqGrid('setGridParam',{url: url + '?_search=true&page=1&searchField=" . $detailFieldName . "&searchOper=eq&searchString='+ids,page:1}); 
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
}