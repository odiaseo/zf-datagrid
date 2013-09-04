<?php
    namespace SynergyDataGrid\Grid;

        /**
         * This file is part of the Synergy package.
         *
         * (c) Pele Odiase <info@rhemastudio.com>
         *
         * For the full copyright and license information, please view the LICENSE
         * file that was distributed with this source code.
         *
         * @author  Pele Odiase
         * @license http://opensource.org/licenses/BSD-3-Clause
         *
         */

    /**
     * Predifined icons that can be used in jqGrid control.
     *
     * @author  Pele Odiase
     * @see     http://jqueryui.com/themeroller/
     * @package mvcgrid
     */
    abstract class PredefinedIcons
    {

        const ICON_CARAT_1_n              = "ui-icon-carat-1-n";
        const ICON_CARAT_1_NE             = "ui-icon-carat-1-ne";
        const ICON_CARAT_1_E              = "ui-icon-carat-1-e";
        const ICON_CARAT_1_SE             = "ui-icon-carat-1-se";
        const ICON_CARAT_1_S              = "ui-icon-carat-1-s";
        const ICON_CARAT_1_SW             = "ui-icon-carat-1-sw";
        const ICON_CARAT_1_W              = "ui-icon-carat-1-w";
        const ICON_CARAT_1_NW             = "ui-icon-carat-1-nw";
        const ICON_CARAT_2_N_S            = "ui-icon-carat-2-n-s";
        const ICON_CARAT_2_E_W            = "ui-icon-carat-2-e-w";
        const ICON_TRIANGLE_1_N           = "ui-icon-triangle-1-n";
        const ICON_TRIANGLE_1_NE          = "ui-icon-triangle-1-ne";
        const ICON_TRIANGLE_1_E           = "ui-icon-triangle-1-e";
        const ICON_TRIANGLE_1_SE          = "ui-icon-triangle-1-se";
        const ICON_TRIANGLE_1_S           = "ui-icon-triangle-1-s";
        const ICON_TRIANGLE_1_SW          = "ui-icon-triangle-1-sw";
        const ICON_TRIANGLE_1_W           = "ui-icon-triangle-1-w";
        const ICON_TRIANGLE_1_NW          = "ui-icon-triangle-1-nw";
        const ICON_TRIANGLE_2_N_S         = "ui-icon-triangle-2-n-s";
        const ICON_TRIANGLE_2_E_W         = "ui-icon-triangle-2-e-w";
        const ICON_ARROW_1_N              = "ui-icon-arrow-1-n";
        const ICON_ARROW_1_NE             = "ui-icon-arrow-1-ne";
        const ICON_ARROW_1_E              = "ui-icon-arrow-1-e";
        const ICON_ARROW_1_SE             = "ui-icon-arrow-1-se";
        const ICON_ARROW_1_S              = "ui-icon-arrow-1-s";
        const ICON_ARROW_1_SW             = "ui-icon-arrow-1-sw";
        const ICON_ARROW_1_W              = "ui-icon-arrow-1-w";
        const ICON_ARROW_1_NW             = "ui-icon-arrow-1-nw";
        const ICON_ARROW_N_S              = "ui-icon-arrow-2-n-s";
        const ICON_ARROW_NE_SW            = "ui-icon-arrow-2-ne-sw";
        const ICON_ARROW_E_W              = "ui-icon-arrow-2-e-w";
        const ICON_ARROW_SE_NW            = "ui-icon-arrow-2-se-nw";
        const ICON_ARROWSTOP_1_N          = "ui-icon-arrowstop-1-n";
        const ICON_ARROWSTOP_1_E          = "ui-icon-arrowstop-1-e";
        const ICON_ARROWSTOP_1_S          = "ui-icon-arrowstop-1-s";
        const ICON_ARROWSTOP_1_W          = "ui-icon-arrowstop-1-w";
        const ICON_ARROWTHICK_1_N         = "ui-icon-arrowthick-1-n";
        const ICON_ARROWTHICK_1_NE        = "ui-icon-arrowthick-1-ne";
        const ICON_ARROWTHICK_1_E         = "ui-icon-arrowthick-1-e";
        const ICON_ARROWTHICK_1_SE        = "ui-icon-arrowthick-1-se";
        const ICON_ARROWTHICK_1_S         = "ui-icon-arrowthick-1-s";
        const ICON_ARROWTHICK_1_SW        = "ui-icon-arrowthick-1-sw";
        const ICON_ARROWTHICK_1_W         = "ui-icon-arrowthick-1-w";
        const ICON_ARROWTHICK_1_NW        = "ui-icon-arrowthick-1-nw";
        const ICON_ARROWTHICK_2_N_S       = "ui-icon-arrowthick-2-n-s";
        const ICON_ARROWTHICK_2_NE_SW     = "ui-icon-arrowthick-2-ne-sw";
        const ICON_ARROWTHICK_2_E_W       = "ui-icon-arrowthick-2-e-w";
        const ICON_ARROWTHICK_2_SE_NW     = "ui-icon-arrowthick-2-se-nw";
        const ICON_ARROWTHICKSTOP_1_N     = "ui-icon-arrowthickstop-1-n";
        const ICON_ARROWTHICKSTOP_1_E     = "ui-icon-arrowthickstop-1-e";
        const ICON_ARROWTHICKSTOP_1_S     = "ui-icon-arrowthickstop-1-s";
        const ICON_ARROWTHICKSTOP_1_W     = "ui-icon-arrowthickstop-1-w";
        const ICON_ARROWTURNTHICK_1_W     = "ui-icon-arrowreturnthick-1-w";
        const ICON_ARROWTURNTHICK_1_N     = "ui-icon-arrowreturnthick-1-n";
        const ICON_ARROWTURNTHICK_1_E     = "ui-icon-arrowreturnthick-1-e";
        const ICON_ARROWTURNTHICK_1_S     = "ui-icon-arrowreturnthick-1-s";
        const ICON_ARROWRETURN_1_W        = "ui-icon-arrowreturn-1-w";
        const ICON_ARROWRETURN_1_N        = "ui-icon-arrowreturn-1-n";
        const ICON_ARROWRETURN_1_E        = "ui-icon-arrowreturn-1-e";
        const ICON_ARROWRETURN_1_S        = "ui-icon-arrowreturn-1-s";
        const ICON_ARROWREFRESH_1_W       = "ui-icon-arrowrefresh-1-w";
        const ICON_ARROWREFRESH_1_N       = "ui-icon-arrowrefresh-1-n";
        const ICON_ARROWREFRESH_1_E       = "ui-icon-arrowrefresh-1-e";
        const ICON_ARROWREFRESH_1_S       = "ui-icon-arrowrefresh-1-s";
        const ICON_ARROW_4                = "ui-icon-arrow-4";
        const ICON_ARROW_4_DIAG           = "ui-icon-arrow-4-diag";
        const ICON_EXTLINK                = "ui-icon-extlink";
        const ICON_NEWWIN                 = "ui-icon-newwin";
        const ICON_REFRESH                = "ui-icon-refresh";
        const ICON_SHUFFLE                = "ui-icon-shuffle";
        const ICON_TRANSFER_E_W           = "ui-icon-transfer-e-w";
        const ICON_TRANSFERTHICK_E_W      = "ui-icon-transferthick-e-w";
        const ICON_COLLAPSED              = "ui-icon-folder-collapsed";
        const ICON_FOLDER_OPEN            = "ui-icon-folder-open";
        const ICON_DOCUMENT               = "ui-icon-document";
        const ICON_DOCUMENT_B             = "ui-icon-document-b";
        const ICON_NOTE                   = "ui-icon-note";
        const ICON_ICON_MAIL_CLOSED       = "ui-icon-mail-closed";
        const ICON_MAIL_OPEN              = "ui-icon-mail-open";
        const ICON_SUITCASE               = "ui-icon-suitcase";
        const ICON_COMMENT                = "ui-icon-comment";
        const ICON_PERSON                 = "ui-icon-person";
        const ICON_PRINT                  = "ui-icon-print";
        const ICON_TRASH                  = "ui-icon-trash";
        const ICON_LOCKED                 = "ui-icon-locked";
        const ICON_UNLOCKED               = "ui-icon-unlocked";
        const ICON_BOOKMARK               = "ui-icon-bookmark";
        const ICON_TAG                    = "ui-icon-tag";
        const ICON_HOME                   = "ui-icon-home";
        const ICON_FLAG                   = "ui-icon-flag";
        const ICON_CALENDAR               = "ui-icon-calendar";
        const ICON_CART                   = "ui-icon-cart";
        const ICON_PENCIL                 = "ui-icon-pencil";
        const ICON_CLOCK                  = "ui-icon-clock";
        const ICON_DISK                   = "ui-icon-disk";
        const ICON_CALCULATOR             = "ui-icon-calculator";
        const ICON_ZOOMIN                 = "ui-icon-zoomin";
        const ICON_ZOOMOUT                = "ui-icon-zoomout";
        const ICON_SEARCH                 = "ui-icon-search";
        const ICON_WRENCH                 = "ui-icon-wrench";
        const ICON_GEAR                   = "ui-icon-gear";
        const ICON_HEART                  = "ui-icon-heart";
        const ICON_STAR                   = "ui-icon-star";
        const ICON_LINK                   = "ui-icon-link";
        const ICON_CANCEL                 = "ui-icon-cancel";
        const ICON_PLUS                   = "ui-icon-plus";
        const ICON_PLUSTHICK              = "ui-icon-plusthick";
        const ICON_MINUS                  = "ui-icon-minus";
        const ICON_MINUSTHICK             = "ui-icon-minusthick";
        const ICON_CLOSE                  = "ui-icon-close";
        const ICON_CLOSETHINCK            = "ui-icon-closethick";
        const ICON_KEY                    = "ui-icon-key";
        const ICON_LIGHTBULB              = "ui-icon-lightbulb";
        const ICON_SCISSORS               = "ui-icon-scissors";
        const ICON_CLIPBOARD              = "ui-icon-clipboard";
        const ICON_COPY                   = "ui-icon-copy";
        const ICON_CONTACT                = "ui-icon-contact";
        const ICON_IMAGE                  = "ui-icon-image";
        const ICON_VIDEO                  = "ui-icon-video";
        const ICON_SCRIPT                 = "ui-icon-script";
        const ICON_ALERT                  = "ui-icon-alert";
        const ICON_INFO                   = "ui-icon-info";
        const ICON_NOTICE                 = "ui-icon-notice";
        const ICON_HELP                   = "ui-icon-help";
        const ICON_CHECK                  = "ui-icon-check";
        const ICON_BULLET                 = "ui-icon-bullet";
        const ICON_RADIO_OFF              = "ui-icon-radio-off";
        const ICON_RADIO_ON               = "ui-icon-radio-on";
        const ICON_PIN_W                  = "ui-icon-pin-w";
        const ICON_PIN_S                  = "ui-icon-pin-s";
        const ICON_PLAY                   = "ui-icon-play";
        const ICON_PAUSE                  = "ui-icon-pause";
        const ICON_SEEK_NEXT              = "ui-icon-seek-next";
        const ICON_SEEK_PREV              = "ui-icon-seek-prev";
        const ICON_SEEK_END               = "ui-icon-seek-end";
        const ICON_SEEK_START             = "ui-icon-seek-start";
        const ICON_STOP                   = "ui-icon-stop";
        const ICON_EJECT                  = "ui-icon-eject";
        const ICON_VOLUME_OFF             = "ui-icon-volume-off";
        const ICON_VOLUME_ON              = "ui-icon-volume-on";
        const ICON_POWER                  = "ui-icon-power";
        const ICON_SIGNAL_DIAG            = "ui-icon-signal-diag";
        const ICON_SIGNAL                 = "ui-icon-signal";
        const ICON_BATTERY_0              = "ui-icon-battery-0";
        const ICON_BATTERY_1              = "ui-icon-battery-1";
        const ICON_BATTERY_2              = "ui-icon-battery-2";
        const ICON_BATTERY_3              = "ui-icon-battery-3";
        const ICON_CIRCLE_PLUS            = "ui-icon-circle-plus";
        const ICON_CIRCLE_MINUS           = "ui-icon-circle-minus";
        const ICON_CIRCLE_CLOSE           = "ui-icon-circle-close";
        const ICON_CIRCLE_TRIANGLE_E      = "ui-icon-circle-triangle-e";
        const ICON_CIRCLE_TRIANGLE_S      = "ui-icon-circle-triangle-s";
        const ICON_CIRCLE_TRIANGLE_W      = "ui-icon-circle-triangle-w";
        const ICON_CIRCLE_TRIANGLE_N      = "ui-icon-circle-triangle-n";
        const ICON_CIRCLE_ARROW_E         = "ui-icon-circle-arrow-e";
        const ICON_CIRCLE_ARROW_S         = "ui-icon-circle-arrow-s";
        const ICON_CIRCLE_ARROW_W         = "ui-icon-circle-arrow-w";
        const ICON_CIRCLE_ARROW_N         = "ui-icon-circle-arrow-n";
        const ICON_CIRCLE_ZOOMIN          = "ui-icon-circle-zoomin";
        const ICON_CIRCLE_ZOOMOUT         = "ui-icon-circle-zoomout";
        const ICON_CIRCLE_CHECK           = "ui-icon-circle-check";
        const ICON_CIRCLESMALL_PLUS       = "ui-icon-circlesmall-plus";
        const ICON_CIRCLESMALL_MINUS      = "ui-icon-circlesmall-minus";
        const ICON_CIRCLESMALL_CLOSE      = "ui-icon-circlesmall-close";
        const ICON_SQUARESMALL_PLUS       = "ui-icon-squaresmall-plus";
        const ICON_SQUARESMALL_MINUS      = "ui-icon-squaresmall-minus";
        const ICON_SQUARESMALL_CLOSE      = "ui-icon-squaresmall-close";
        const ICON_GRIP_DOTTED_VERTICAL   = "ui-icon-grip-dotted-vertical";
        const ICON_GRIP_DOTTED_HORIZONTAL = "ui-icon-grip-dotted-horizontal";
        const ICON_GRIP_SOLID_VERTICAL    = "ui-icon-grip-solid-vertical";
        const ICON_SOLID_HORIZONTAL       = "ui-icon-solid-horizontal";
        const ICON_GRIPSMALL_DIAGONAL_SE  = "ui-icon-gripsmall-diagonal-se";
        const ICON_GRIP_DIAGONAL_SE       = "ui-icon-grip-diagonal-se";

    }