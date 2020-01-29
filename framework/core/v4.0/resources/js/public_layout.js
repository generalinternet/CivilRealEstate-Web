/*
layout.js v2.0.12
*/
var menuBarScroll;
var ajaxLoadedJSArray = [];
var ajaxLoadedCSSArray = [];

menuBarScrollId = 'main_nav_wrap';
defaultTargetId = 'content';

$(function () {
    
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
        $('#content_wrap').css({
            paddingBottom: $('.footer_bars').outerHeight() + 'px'
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
    
    if ($('#menu_btn').is(":visible")) {
        $('#main_nav li.current ul').show();
        $('#main_nav li.current ul').addClass('open');
        $('#main_nav li.current a.dropdown').addClass('open');
    }
    
    $('#menu_btn').on('click tap', function () {
        if ($(this).is('.open')) {
            $(this).removeClass('open');
            $('body').removeClass('menu_open');
        } else {
            $(this).addClass('open');
            $('body').addClass('menu_open');
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

$(document).on('click', '.ui_tile.tile_link', function(e){
    e.stopPropagation();
    var targetUrl = $(this).data('url');
    var targetId = $(this).data('target-id');
    if (targetId === undefined) {
        window.location.href = targetUrl;
    } else {
        var tableEl = $(this).closest('.ui_tiles');
        var rowEl = $(this).closest('.ui_tile');
        tableEl.find('.ui_tile').removeClass('current');
        rowEl.addClass('current');
        loadInElementByTargetId(targetUrl, targetId, true);
    }
});

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
$(document).ready(function(){
    setMenuBarIScroll();

    //Revers scroll
    scrollDownReverseScrolls();
    
    //Tile cell
    setTileCells();
});

function scrollDownReverseScrolls(){
    if($('.reverse_scroll').length){
        $('.reverse_scroll').each(function(){
            var targetOffset= $(this).offset();
            var bottom = targetOffset.top + $(this).children().first().outerHeight(true);
            $('.reverse_scroll').animate({
                    scrollTop: bottom
            }, 800);
        });
    }
}

function setTileCells(){
    $(document).on('click', '.ui_tile', function(e){
        //e.stopPropagation();
        if ($(this).hasClass('current')) {
            $(this).removeClass('current');
        } else {
            $(this).addClass('current');
        }
    });
}

$(document).on('click','.ajaxed_contents_cancel_btn',function(e){
    e.preventDefault();
    $(this).closest('.ajaxed_contents').slideUp();
});

//Close the submenus if they're open when the outside is clicked
$(document).mouseup(function (e){
    if (!$(e.target).closest('#main_nav').length){
        var subMenu = $('#main_nav li > .sub_menu.open');
        if (subMenu.length) {
            var thisLi = $(subMenu).closest('li');
            var thisList = thisLi.children('ul');

            thisList.slideUp();
            thisList.removeClass('open');
            $(subMenu).removeClass('open');
            thisLi.removeClass('open');
        }
    }
});

function verifyMainWindowContent(){
    return;
}