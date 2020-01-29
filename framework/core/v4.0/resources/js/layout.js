/*
layout.js v2.0.12
*/
var menuBarScroll;
var listBarScroll;
var sidebarScroll;
var screenSizeL = 1920;
var ajaxLoadedJSArray = [];
var ajaxLoadedCSSArray = [];

mainViewWrapId = 'main_window_view_wrap';
menuBarScrollId = 'main_nav_wrap';
listBarScrollElId = 'list_table_wrap';
sidebarScrollElId = 'main_window_sidebar_wrap';
defaultTargetId = 'main_window';

function openMainMenu(){
    $('#menu_btn').addClass('open');
    $('body').addClass('menu_open');
}

function closeMainMenu(){
    $('#menu_btn').removeClass('open');
    $('body').removeClass('menu_open');
    $('#main_nav').find('> li > ul.open').slideUp();
    $('#main_nav').find('> li > ul.open').slideUp();
    $('#main_nav').find('.open').removeClass('open');
    $('#main_nav').find('.sub_menu ul').slideUp();
}

$(function () {
    if (typeof ElementQueries === 'function') {
        ElementQueries.init();
    }
    if($('body').is('.logged_in') && $('body').is('.protected_page')){
        sessionCheck();
    }
    
//    if (!$('#page_loading').length) {
//        $('body').append('<div id="page_loading"></div>');
//    }
    
    if (!$('#user_dialog').length) {
        $('body').append('<div id="user_dialog"></div>');
    }
    
    $('#user_dialog').dialog({
        autoOpen: false,
        resizable: false,
        modal: true
    });
    
    if ($('.form_element.error').length) {
        let centerErrorScrollTop = parseInt($('.form_element.error').first().offset().top - ($(window).height()/2)) + ($('.form_element.error').first().outerHeight()/2);
        $("html,body").animate({ scrollTop: centerErrorScrollTop }, 500, 'swing');
    }
    
    if($('.footer_bars').length){
        var footerBarsHeight = $('.footer_bars').outerHeight();
        var windAdj = footerBarsHeight + 80;
        $('#main_window').css({
            'min-height' : 'calc(100vh - ' + windAdj + 'px)'
        });
        $('#content_wrap').css({
            paddingBottom: footerBarsHeight + 'px'
        });
    }
    
    $('#main_nav li > .sub_menu').click(function (e) {
        e.stopPropagation();
        e.preventDefault();
        var thisLi = $(this).closest('li');
        var thisUl = thisLi.closest('ul');
        var thisList = thisLi.children('ul');
        if (thisList.is('.open')) {
            thisList.slideUp();
            thisList.removeClass('open');
            $(this).removeClass('open');
            thisLi.removeClass('open');
        } else {
            openMainMenu();
            thisUl.find('> li > ul.open').slideUp();
            thisUl.find('> li > ul.open').slideUp();
            thisUl.find('.open').removeClass('open');
            thisList.removeClass('open_left');
            thisList.slideDown();
            var contentR = $('#content').offset().left + $('#content').width();
            var listR = thisList.offset().left + thisList.width();
            if(listR > contentR){
                thisList.addClass('open_left');
            }
            thisList.addClass('open');
            $(this).addClass('open');
            thisLi.addClass('open');
        }
    });
    
    if ($('#menu_btn').is(':visible') || $('#menu_bar').is(':visible')) {
        if($(window).width() > screenSizeL){
            $('#main_nav li.current ul').show();
            $('#main_nav li.current ul').addClass('open');
            $('#main_nav li.current a.dropdown').addClass('open');
        }
    }
    
    $('#menu_btn').on('click tap', function () {
        if ($(this).is('.open')) {
            closeMainMenu();
        } else {
            openMainMenu();
        }
    });
    /** Smaller screen's multiple header menu**/
    $(document).on('click tap', '.multiple_header_btns .right_btns', function () {
        if ($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).find('.close_btn').remove();
        } else {
            $(this).prepend('<span class="custom_btn close_btn"><span class="icon_wrap border circle"><span class="icon primary arrow_up"></span></span></span>');
            $(this).slideDown();
            $(this).addClass('open');
        }
    });
    
    $('img').on('dragstart', function (e) {
        e.preventDefault();
    });
    
    if ($('form.sticky_submit').length == 1){
        var stickBtns = $('#stick_btns');
        if(stickBtns.length){
            $('body').append('<div id="sticky_submit_bar">' + stickBtns.html() + '</div>');
            stickBtns.hide();
            updateStickyBar();
        }
    }
    
    newContentLoaded();
    
});

$(window).resize(function () {
    setMainWindowHeight();
    setMenuBarHeight();
    setListBarTableHeight();
});

var qbConnectWindow;

$(document).on('click', '.qb_connect_btn', function(e){
    e.preventDefault();
    var url = $(this).attr('href');
    qbConnectWindow = window.open(url,'popup','width=600,height=600');
    return false;
});

$(document).on('click', '.qb_disconnect_btn', function(e){
    e.preventDefault();
    jQuery.post('index.php?controller=accounting&action=disconnectFromQB&ajax=1', function (data) {
        if(data.success && data.qbBtn != undefined){
            var newBtn = $(data.qbBtn);
            $('.qb_disconnect_btn').replaceWith(newBtn);
            $('.qb_bar.connected').removeClass('connected');
            $('.qb_related_content.connected').removeClass('connected');
        }
    });
    return false;
});

$(document).on('click', '.ui_tile.tile_link', function(e){
    var targetUrl = $(this).data('url');
    var tableEl = $(this).closest('.ui_tiles');
    var rowEl = $(this).closest('.ui_tile');
    tableEl.find('.ui_tile').removeClass('current');
    rowEl.addClass('current');
    var targetId = $(this).data('target-id');
    if (targetId === undefined) {
        targetId = defaultTargetId;
    }
    if($(this).closest('.ajax_link_wrap').length){
        loadInElementByTargetId(targetUrl, targetId, true);
    } else {
        window.location = targetUrl;
    }
});

$(document).on('click', '.ui_tile a', function(e){
    e.stopPropagation();
    var tableEl = $(this).closest('.ui_tiles');
    var rowEl = $(this).closest('.ui_tile');
    tableEl.find('.ui_tile').removeClass('current');
    rowEl.addClass('current');
});
$(document).on('click', '#clear_main_content', function(e){
    clearMainPanel();
});

function clearMainPanel() {
    let prevDTUrl = prevDTUrls.pop();
    let clearPanel = true;
    if(prevDTUrl !== undefined && prevDTUrl !== ''){
        let targetId = getUrlParamValue(prevDTUrl, 'targetId');
        if(targetId !== undefined){
            loadInElementByTargetId(prevDTUrl, targetId, true);
            clearPanel = false;
        }
    }
    if(clearPanel){
        let listBar = $('#list_bar');
        let listUrl = listBar.data('url');
        if(listUrl !== undefined){
            listUrl = removeUrlParam(listUrl, 'curId');
            historyPushState('reload', listUrl, 'list_bar');
        }
        $('#'+defaultTargetId).html('');
        $('#'+defaultTargetId).addClass('empty');
        $('#list_bar .ui_tiles .ui_tile').removeClass('current');
    }
    verifyMainWindowContent();
}

function setCurrentOnListBar(id) {
    var selectedEl = $('#list_bar').find('.ui_tile[data-model-id="' + id + '"]');
    if (selectedEl.length) {
        $('#list_bar').find('.ui_tile').removeClass('current');
        selectedEl.addClass('current');
        var listUrl = $('#list_bar').data('url');
        if (listUrl !== undefined) {
            $('#list_bar').data('url', replaceUrlParam(listUrl, 'curId', id));
        }
    }
}

/** Iscroll : start **/
function setMenuBarIScroll() {
    if ($('#'+menuBarScrollId).length && typeof IScroll === 'function') {
        if (menuBarScroll === undefined || !$('#'+menuBarScrollId).hasClass('hasIScroll')) {
            menuBarScroll = new IScroll('#'+menuBarScrollId, { 
                mouseWheel: true,
                mouseWheelSpeed: 100,
                click: true,
                probeType: 2,
            });
            $('#'+menuBarScrollId).addClass('hasIScroll');
        } else {
            menuBarScroll.refresh();
        }
    }
    
}
function setListBarIScroll() {
    if ($('#'+listBarScrollElId).length && typeof IScroll === 'function') {
        if (listBarScroll === undefined || !$('#'+listBarScrollElId).hasClass('hasIScroll')) {
            listBarScroll = new IScroll('#'+listBarScrollElId, { 
                mouseWheel: true,
                mouseWheelSpeed: 100,
                click: true,
                probeType: 2,
            });
            $('#'+listBarScrollElId).addClass('hasIScroll');
        } else {
            listBarScroll.refresh();
        }
    }
}
function setSidebarIScroll() {
    if ($('#'+sidebarScrollElId).length && typeof IScroll === 'function') {
        if (sidebarScroll === undefined || !$('#'+sidebarScrollElId).hasClass('hasIScroll')) {
            sidebarScroll = new IScroll('#'+sidebarScrollElId, { 
                mouseWheel: true,
                mouseWheelSpeed: 100,
                click: true,
                probeType: 2,
            });
            $('#'+sidebarScrollElId).addClass('hasIScroll');
        } else {
            sidebarScroll.refresh();
        }
    }
}
//Set list table height 
function setListBarTableHeight() {
    if ($('#'+listBarScrollElId).length) {
        //Set heights
        var footerBarsH = 0;
        if($('.footer_bars').length){
            footerBarsH = $('.footer_bars').outerHeight();
        }
        var headerH = $('#header_wrap').outerHeight();
        
        var listBarHeaderH = 0; 
        if($('#list_bar .view_header').length){
            listBarHeaderH = $('#list_bar .view_header').outerHeight();
        }
        var listBarBtnH = 0; 
        if($('#list_bar .list_btns').length){
            listBarBtnH = $('#list_bar .list_btns').outerHeight();
        }
        var listBarSelectorH = 0; 
        if($('#list_bar .top_selector').length){
            listBarSelectorH = $('#list_bar .top_selector').outerHeight();
        }
        var gapListBarTopListTable = listBarHeaderH + listBarBtnH + listBarSelectorH;
        var newH = $(window).height() - headerH - footerBarsH - gapListBarTopListTable;
        $('#'+listBarScrollElId).css('height', newH+'px');
        setListBarIScroll();
    }
}

//Set main section height 
function setViewWrapHeight() {
    if ($('#'+mainViewWrapId).length) {
        //Set heights
        var footerBarsH = 0;
        if($('.footer_bars').length){
            footerBarsH = $('.footer_bars').outerHeight();
        }
        var headerH = $('#header_wrap').outerHeight();
        var breadcrumbsH = 0;
        if($('#breadcrumbs').length){
            breadcrumbsH = $('#breadcrumbs').outerHeight();
        }
        var qbBarH = 0;
        if($('.qb_bar_wrap').length){
            qbBarH = $('.qb_bar_wrap').outerHeight();
        }
        var newH = $(window).height() - headerH - footerBarsH - breadcrumbsH - qbBarH;
        $('#'+mainViewWrapId).css('height', newH+'px');
    }
}

//Set menu bar height 
function setMenuBarHeight() {
    if ($('#'+menuBarScrollId).length) {
        //Set heights
        var footerBarsH = 0;
        if($('.footer_bars').length){
            footerBarsH = $('.footer_bars').outerHeight();
        }
        var headerH = $('#header_wrap').outerHeight();
        var newH = $(window).height() - headerH - footerBarsH;
        $('#'+menuBarScrollId).css('height', newH+'px');
        setMenuBarIScroll();
    }
}

//Set side bar height 
function setSidebarHeight() {
    if ($('#'+sidebarScrollElId).length) {
        //Set heights
        var footerBarsH = 0;
        if($('.footer_bars').length){
            footerBarsH = $('.footer_bars').outerHeight();
        }
        var headerH = $('#header_wrap').outerHeight();
        var breadcrumbsH = 0;
        if($('#breadcrumbs').length){
            breadcrumbsH = $('#breadcrumbs').outerHeight();
        }
        var qbBarH = 0;
        if($('.qb_bar_wrap').length){
            qbBarH = $('.qb_bar_wrap').outerHeight();
        }
        var newH = $(window).height() - headerH - footerBarsH - breadcrumbsH - qbBarH;
        $('#'+sidebarScrollElId).css('height', newH+'px');
        setSidebarIScroll();
    }
}

function setMainWindowHeight() {
    setViewWrapHeight();
    setSidebarHeight();
}
//Resize scroll if the list bar reloaded 
$(document).on('loadedInElement', '#list_bar', function(){
    setListBarTableHeight();
});

//Resize scroll if main content reloaded 
$(document).on('loadedInElement', '#main_window', function(){
    setMainWindowHeight();
});
    
function initResizeSensors(){
    //Set layout height
    setListBarTableHeight();
    setMenuBarHeight();
    setMainWindowHeight();

    //Detect element's resizing
    if (typeof ElementQueries === 'function') {
        if ($('#'+listBarScrollElId).length && $('#'+listBarScrollElId).data('resize-sensor') == undefined) {
            var scrollInner = $('#'+listBarScrollElId).children().first();
            if (scrollInner[0] !== undefined) {
                new ResizeSensor(scrollInner[0], function() {
                    setListBarIScroll();
                });
            }
            $('#'+listBarScrollElId).data('resize-sensor', true);
        }
//        var sidebarEl = $('#sidebar_body_wrap');
//        if (sidebarEl[0] !== undefined) {
//            new ResizeSensor(sidebarEl[0], function() {
//                setSidebarHeight();
//            });
//        }
        if ($('#'+sidebarScrollElId).length && $('#'+sidebarScrollElId).data('resize-sensor') == undefined) {
            var scrollInner = $('#'+sidebarScrollElId).children().first();
            if (scrollInner[0] !== undefined) {
                new ResizeSensor(scrollInner[0], function() {
                    setSidebarHeight();
                });
            }
            $('#'+sidebarScrollElId).data('resize-sensor', true);
        }
        if ($('#'+menuBarScrollId).length && $('#'+menuBarScrollId).data('resize-sensor') == undefined) {
            var scrollInner = $('#'+menuBarScrollId).children().first();
            if (scrollInner[0] !== undefined) {
                new ResizeSensor(scrollInner[0], function() {
                    setMenuBarIScroll();
                });
            }
            $('#'+menuBarScrollId).data('resize-sensor', true);
        }
    }
};

/** Iscroll : end **/

// Initialize public profile form
var ProfileForm = function(){
    var ins = {};
    var triggerButtonHTML = '<div class="wrap_btns"><a class="btn other_btn color_trigger_btn">Choose New Accent Colour</a></div>';

    var initColorPicker = function(){
        var $colorPickerInput = $("#qna_profile .colour_picker_field");
        if($colorPickerInput.length > 0){
            $colorPickerInput.parents('.field_content').append(triggerButtonHTML);
        }
    };

    var initTriggerButton = function(){
        $(document).on('click', '.color_trigger_btn', function(){
            event.preventDefault();
            var $colorPickerInput = $("#qna_profile .colour_picker_field");
            if($colorPickerInput.length > 0){
                $colorPickerInput.wheelColorPicker('show');
            }
        });
    };

    ins.init = function(){
        initColorPicker();
        initTriggerButton();
    };

    return ins;
}();
$(document).ready(function () {
    ProfileForm.init();
});

function verifyMainWindowContent(){
    let mainWindow = $('.right_panel #main_window');
    if(mainWindow.length){
        let panelWrap = mainWindow.closest('.two_col_panel_wrap');
        if(mainWindow.is('.empty')){
            panelWrap.removeClass('focus_right_panel');
        } else {
            panelWrap.addClass('focus_right_panel');
            let clearMainBtn = mainWindow.find('#clear_main_content');
            if(!clearMainBtn.length){
                mainWindow.prepend('<div id="clear_main_content" class="custom_btn" title="Clear Main Content"><span class="icon_wrap"><span class="icon eks"></span></span></div>');
            }
        }
    }
}

//@todo left menu
$(document).on('bindActionsToNewContent',function(){
    initResizeSensors();
    verifyMainWindowContent();
});