/*
layout.js v2.0.12
*/
var time = new Date().getTime();
var sessionWarningMinutes = 25;
var sessionMinutes = 30;
var menuBarScroll;
var listBarScroll;
var sidebarScroll;
var mainViewWrapId = 'main_window_view_wrap';
var menuBarScrollId = 'main_nav_wrap';
var listBarScrollElId = 'list_table_wrap';
var sidebarScrollElId = 'main_window_sidebar_wrap';
var screenSizeL = 1920;
var ajaxLoadedJSArray = [];

syntaxhighlighterConfig = {
    toolbar: false
};

function startPageLoader(){
//    $('#page_loading').fadeIn('fast');
    elmStartLoading($('body'), 'circle');
}

function stopPageLoader(){
//    $('#page_loading').fadeOut('fast');
    elmStopLoading($('body'));
}

(function($) {
    $.fn.closestChild = function(filter) {
        var $found = $(),
            $currentSet = this; // Current place
        while ($currentSet.length) {
            $found = $currentSet.filter(filter);
            if ($found.length) break;  // At least one match: break loop
            // Get all children of the current set
            $currentSet = $currentSet.children();
        }
        return $found.first(); // Return first match of the collection
    }    
})(jQuery);

$( window ).on('beforeunload',function() {
    //commented out do to safari bug
    //startPageLoader();
});

$( window ).on('pageshow',function() {
    //stopPageLoader();
});

var selectedRows = {};

function pushIfMissing(array, value){
    var len = array.length;
          
    if (len > 0) {
      for (var i = 0; i < len; i++) {
        if (array[i] == value) {
          return true;
        }
      }
    }
          
    array.push(value);
}

function makeItMoney(value) {
    var roundedVal = preciseRound(value, 2);
    var money = roundedVal.toFixed(2);
//    var money = Number(value.replace(/[^0-9\.\-]+/g, ""));
    return money;
}

function numberWithCommas(value) {
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function parseFloatNoNaN(val){
    var newVal = parseFloat(val);
    if(isNaN(newVal)){
        newVal = 0;
    }
    return newVal;
}

function parseIntNoNaN(val){
    var newVal = parseInt(val);
    if(isNaN(newVal)){
        newVal = 0;
    }
    return newVal;
}

function makeItMoneyForDisplay(value){
    var parsedVal = parseFloatNoNaN(value);
    var roundedVal = parsedVal.toFixed(2);
    var money = numberWithCommas(roundedVal);
    return money;
}

function preciseRound(value, decimals){
    if(decimals == undefined){
        decimals = 2;
    }
    return Number(Math.round(value + 'e'+decimals) + 'e-' + decimals);
}

function preciseFloor(value, decimals){
    if(decimals == undefined){
        decimals = 2;
    }
    return Number(Math.floor(value + 'e'+decimals) + 'e-' + decimals);
}

function preciseCeil(value, decimals){
    if(decimals == undefined){
        decimals = 2;
    }
    return Number(Math.ceil(value + 'e'+decimals) + 'e-' + decimals);
}

function removeValFromArray(array, removeVal){
    array = jQuery.grep(array, function(val) {
        return val != removeVal;
    });
    return array;
}

function windowResized() {
    $('#menu_btn').removeClass('open');
    $('#main_nav nav ul.open').hide();
    $('#main_nav nav .open').removeClass('open');
    $('#content_wrap,#footer_wrap').css({
        right: ''
    });
    $('#main_nav_wrap').css({
        right: ''
    });
}

function fullscreenToggle() {
    $('body').toggleClass('fullscreen');
    if ($('body').is('.fullscreen')) {
        $('#toggle_fullscreen .icon').removeClass('maximize').addClass('minimize');
        document.cookie = "fullscreen=1; path=/; max-age=2592000;";
    } else {
        $('#toggle_fullscreen .icon').removeClass('minimize').addClass('maximize');
        document.cookie = "fullscreen=0; path=/; max-age=2592000;";
    }
    windowResized();
}

function updateStickyBar(){
    if($('#sticky_submit_bar').length){
        $('#sticky_submit_bar').show();
        $('form.sticky_submit .submit_btn').hide();
        /*
        $('#content_wrap').css({
            paddingBottom: $('#sticky_submit_bar').height()+'px'
        });
        */
    }
}
/******TITLE BLINKER SCRIPT******/
var realPageTitle = document.title;
var blinkedTitle = false;
var blinkCount = 0;
var blinkInterval = null;

function blinkTitle(newTitle){
    if(blinkedTitle){
        document.title = realPageTitle;
        blinkedTitle = false;
    } else {
        document.title = newTitle;
        blinkedTitle = true;
        blinkCount++;
    }
}

function startTitleBlink(newTitle, interval, blinkLimit){
    if(interval == undefined){
        interval = 1000;
    }
    
    blinkCount = 0;
    blinkInterval = setInterval(function(){
        if(blinkLimit != undefined && blinkCount == blinkLimit){
            stopTitleBlink();
        }
        blinkTitle(newTitle);
    }, interval);
}

function stopTitleBlink(){
    if(blinkInterval){
        clearInterval(blinkInterval);
    }
    blinkInterval = null;
    document.title = realPageTitle;
}

/******END BLINKER SCRIPT******/

$(document).on('click','#sticky_submit_bar .submit_btn', function(){
    var formId = $(this).attr('data-form-id');
    $('#'+formId).submit();
});

/***ADVANCED***/
function toggleAdvancedIcon(icon, type) {
    if (type == 'open') {
        if (icon.hasClass('plus') || icon.hasClass('minus')) {
            icon.addClass('minus');
            icon.removeClass('plus');
        } else {
            var openIcon = icon.data('open-icon');
            var closeIcon = icon.data('close-icon');
            if (openIcon != undefined && closeIcon != undefined) {
                icon.removeClass(openIcon);
                icon.addClass(closeIcon);
            } else {
                icon.removeClass('arrow_right border');
                icon.addClass('arrow_down border');
            }
        }
    } else {
        if (icon.hasClass('minus') || icon.hasClass('plus')) {
            icon.addClass('plus');
            icon.removeClass('minus');
        } else {
            var openIcon = icon.data('open-icon');
            var closeIcon = icon.data('close-icon');
            if (openIcon != undefined && closeIcon != undefined) {
                icon.removeClass(closeIcon);
                icon.addClass(openIcon);
            } else {
                icon.removeClass('arrow_down border');
                icon.addClass('arrow_right border');
            }
        }
        
    }
}
function closeAdvanced(advWrap){
    var advId = advWrap.attr('id');
    var advBtn = advWrap.find('.advanced_btn').first();
    if(advId != '' && !advBtn.length){
        var advBtn = $('.advanced_btn[data-adv-id="' + advId + '"]');
    }
    var adv;
    if (advBtn.length && advBtn.data('adv-ref') !== undefined) {
        var targetRef = advBtn.data('adv-ref');
        adv = $('.target_ref_' + targetRef).children('.advanced_content');
    } else {
        adv = advWrap.find('.advanced_content').first();
    }
    
    var icon = advBtn.find('.icon');
    var closeEvent = jQuery.Event('advancedClosed');
    advBtn.trigger(closeEvent);
    if(!closeEvent.isDefaultPrevented()){
        var parentWrap;
        for (var i=0; i<adv.length; i++) {
            $(adv[i]).slideUp();
            parentWrap = $(adv[i]).parent();
            parentWrap.removeClass('open');
            toggleAdvancedIcon(parentWrap.find('.advanced_btn').first().find('.icon'), 'close');
        }
        
        toggleAdvancedIcon(icon, 'close');
    }
}

function openAdvanced(advWrap){
    var advId = advWrap.attr('id');
    var advBtn = advWrap.find('.advanced_btn').first();
    if(advId != '' && !advBtn.length){
        var advBtn = $('.advanced_btn[data-adv-id="' + advId + '"]');
    }
    var adv;
    if (advBtn.length && advBtn.data('adv-ref') !== undefined) {
        var targetRef = advBtn.data('adv-ref');
        adv = $('.target_ref_' + targetRef).children('.advanced_content');
    } else {
        adv = advWrap.find('.advanced_content').first();
    }
    var icon = advBtn.find('.icon');
    var openEvent = jQuery.Event('advancedOpened');
    advBtn.trigger(openEvent);
    if(!openEvent.isDefaultPrevented()){
        var parentWrap;
        for (var i=0; i<adv.length; i++) {
            $(adv[i]).slideDown();
            parentWrap = $(adv[i]).parent();
            parentWrap.addClass('open');
            toggleAdvancedIcon(parentWrap.find('.advanced_btn').first().find('.icon'), 'open');
        }
        
        toggleAdvancedIcon(icon, 'open');
    }
    
    //If the advance wrap is linked to a overly, close it.
    if (advWrap.attr('class').indexOf('target_ref_') > -1) {
        closeOverlay();
    }
}

$(document).on('click','.advanced_btn', function(e){
    var advBtn = $(this);
    var advId = advBtn.data('adv-id');
    var advWrap = $('#' + advId);
    if(!advWrap.length){
        var advWrap = advBtn.closest('.advanced');
    }
    if(advWrap.is('.open')){
        closeAdvanced(advWrap);
    } else {
        openAdvanced(advWrap);
    }
});

function updateAdvancedSections(){
    if($('.advanced').length){
        $('.advanced').each(function(){
            if($(this).is('.open') || $(this).find('.form_element.error').length){
                openAdvanced($(this));
            } else {
                closeAdvanced($(this));
            }
        });
    }
}
/***END ADVANCED SCRIPT***/

/***SEARCH BOX***/
$(document).on('click', '.open_search_box', function (e) {
    e.preventDefault();
    var searchBox = $(this).attr('data-box');
    if ($(this).is('.open')) {
        $('#' + searchBox).slideUp();
        $(this).removeClass('open');
    } else {
        $('#' + searchBox).slideDown();
        $(this).addClass('open');
    }
});

$(document).on('click', '.close_search_box', function (e) {
    e.preventDefault();
    var searchBox = $(this).parents('.search_box');
    searchBox.slideUp();
    var searchBoxID = searchBox.attr('id');
    $('.open_search_box[data-box="' + searchBoxID + '"]').removeClass('open');
});

$(document).on('click', '.open_basic_search_block', function (e) {
    e.preventDefault();
    var searchBox = $(this).closest('.search_box');
    searchBox.find('input[name=search_type]').val('basic');
    searchBox.find('.advanced_search_block').slideUp(function() {
        searchBox.find('.basic_search_block').slideDown();
        searchBox.find('.advanced_search_block').removeClass('open');
        searchBox.find('.basic_search_block').addClass('open');
        //Adjust GI_modal position
        giModalCenter($('#gi_modal'));
        $('#gi_modal').show();//Need this because giModalCenter wipes out display:block
    });
});

$(document).on('click', '.open_advanced_search_block', function (e) {
    e.preventDefault();
    var searchBox = $(this).closest('.search_box');
    searchBox.find('input[name=search_type]').val('advanced');
    searchBox.find('.basic_search_block').slideUp(function() {
        searchBox.find('.advanced_search_block').slideDown();
        searchBox.find('.basic_search_block').removeClass('open');
        searchBox.find('.advanced_search_block').addClass('open');
        //Adjust GI_modal position
        giModalCenter($('#gi_modal'));
        $('#gi_modal').show();//Need this because giModalCenter wipes out display:block
    });
});

function updateSearchBoxes(){
    if($('.open_search_box.open').length){
        $('.open_search_box.open').each(function(){
            var searchBox = $(this).attr('data-box');
            $('#' + searchBox).show();
        });
    }
}

$(document).on('submit', '.search_box.use_ajax form', function(e){
    e.preventDefault();
    var form = $(this);
    var formAction = form.attr('action');
    var searchBox = form.closest('.search_box');
    var targetId = searchBox.data('target-id');
    var uiTableWrap = $('#' + targetId);
    elmStartLoading(uiTableWrap, 'circle');
    jQuery.ajax({type: 'POST', url: formAction, data: form.serialize(), success: function (data) {
            form.removeClass('submitting');
            if (data.uiTable != undefined) {
                uiTableWrap.replaceWith(data.uiTable);
                $('#'+listBarScrollElId).removeClass('hasIScroll');
                setListBarIScroll();
                setListBarURL(replaceUrlParam(formAction, 'search', 0));
            } else {
                console.log(data);
                var errorCode = 2500;
                var errorURL = 'index.php?controller=static&action=error&errorCode=' + errorCode + '&ajax=1';
                jQuery.post(errorURL, function (data) {
                    uiTableWrap.replaceWith(data.mainContent);
                });
            }
    }});
});
$(document).on('modalSubmitForm', '.search_box.use_ajax form', function(e){
    var form = $(this);
    var searchBox = form.closest('.search_box');
    var targetId = searchBox.data('target-id');
    var uiTableWrap = $('#' + targetId);
    if(uiTableWrap.length && !uiTableWrap.closest('.gi_modal').length){
        e.preventDefault();
        giModalClose();
    }
});
/***END SEARCH BOX SCRIPT***/

/***RADIO TOGGLER***/
function radioToggler(toggler){
    var name = toggler.attr('name');
    var val = $('.radio_toggler[name="' + name + '"]:checked').val();
    $('.radio_toggler_element[data-group="' + name + '"]').each(function(){
        var dataElements = $(this).data('element');
        var dataElementArray = dataElements.toString().split(",").map(function(item) {
            return item.trim();
        });
        if (dataElementArray.includes(val)) {
            $(this).slideDown('fast',function(){
                visibleContentUpdate();
            });
        } else {
            $(this).slideUp('fast');
        }
    });
}

$(document).on('change', '.radio_toggler', function(){
    radioToggler($(this));
});
/***END RADIO TOGGLER SCRIPT***/

/***FIELD TOGGLER (mainly dropdowns)***/
function fieldElementToggler(toggler){
    var name = toggler.attr('name');
    var val = toggler.val();
    $('.toggler_element[data-group="' + name + '"]').each(function(){
        var dataElements = $(this).data('element');
        var dataElementArray = dataElements.toString().split(",").map(function(item) {
            return item.trim();
        });
        if (dataElementArray.includes(val)) {
            $(this).slideDown('fast',function(){
                visibleContentUpdate();
            });
        } else {
            $(this).slideUp('fast');
        }
    });
}

$(document).on('change', '.toggler', function(){
    fieldElementToggler($(this));
});
/***END FIELD TOGGLER SCRIPT***/

/***FIELD TOGGLER (mainly multi-checkbox)***/
function checkboxToggler(toggler){
    var name = toggler.attr('name');
    var uncleanName = name;
    if(name.indexOf('[') > -1){
        name = name.substr(0, name.indexOf('['));
    }
    var val = toggler.val();
    
    if(toggler.is(':checked')){
        $('.checkbox_toggler_element[data-group="' + name + '"]').each(function(){
            var dataElements = $(this).data('element');
            var dataElementArray = dataElements.toString().split(",").map(function(item) {
                return item.trim();
            });
            if (dataElementArray.includes(val)) {
                $(this).slideDown('fast',function(){
                    visibleContentUpdate();
                });
            } else if (dataElementArray.includes('NULL')) {
                $(this).slideUp('fast');
            }
        });
    } else {
        var allVals = $('input[name="' + uncleanName + '"]:checked').map(function(){
            return $(this).val();
        }).get();
        $('.checkbox_toggler_element[data-group="' + name + '"]').each(function(){
            var dataElements = $(this).data('element');
            var dataElementArray = dataElements.toString().split(",").map(function(item) {
                return item.trim();
            });
            var chkVal = true;
            for(var i=0; i<allVals.length; i++){
                var otherVal = allVals[i];
                if(dataElementArray.includes(otherVal)){
                    chkVal = false;
                    return;
                }
            }
            if (chkVal && dataElementArray.includes(val)) {
                $(this).slideUp('fast');
            } else if (dataElementArray.includes('NULL')) {
                $(this).slideDown('fast',function(){
                    visibleContentUpdate();
                });
            }
        });
    }
}

$(document).on('change', '.checkbox_toggler', function(){
    checkboxToggler($(this));
});
/***END FIELD TOGGLER SCRIPT***/

/***NEW CONTENT LOADED***/
function newContentLoaded(){
    $(document).trigger('bindActionsToNewContent');
    initResizeSensors();
    updateAdvancedSections();
    updateSearchBoxes();
    $('.radio_toggler').each(function(){
        if(!$(this).is('.init_toggled')){
            radioToggler($(this));
            var name = $(this).attr('name');
            $('.radio_toggler[name="' + name + '"]').addClass('init_toggled');
        }
    });
    $('.toggler').each(function(){
        if(!$(this).is('.init_toggled')){
            fieldElementToggler($(this));
            var name = $(this).attr('name');
            $('.toggler[name="' + name + '"]').addClass('init_toggled');
        }
    });
    $('.checkbox_toggler').each(function(){
        if(!$(this).is('.init_toggled')){
            checkboxToggler($(this));
            if($(this).is(':checked')){
                $(this).addClass('init_toggled');
            }
        }
    });
    
    $('.ajaxed_contents.auto_load:not(.loaded):not(.loading)').each(function(){
        var element = $(this);
        var url = element.data('url');
        url += '&ajax=1';
        var elementClass = element.attr('class');
        loadInElement(url, undefined, element, elementClass);
    });
    
    for(var selectedRowName in selectedRows){
        if (selectedRows.hasOwnProperty(selectedRowName)) {
            var selectedRowList = selectedRows[selectedRowName];
            for (var i = 0; i < selectedRowList.length; i++) {
                var rowVal = selectedRowList[i];
                var input = $('input[type="checkbox"][name="' + selectedRowName + '[' + rowVal + ']"][value="' + rowVal + '"]').not('[readonly]');
                if(input.length){
                    input.prop('checked',true).trigger('change');
                }
            }
        }
    }

    $(document).trigger('actionsBoundToNewContent');
    loadUITableData();
}
function visibleContentUpdate(){
    $(document).trigger('bindActionsToVisibleContent');
}
/***END NEW CONTENT LOADED SCRIPT***/

$(document).on('click','.cancel_submit_btn',function(){
    window.history.back();
});

function sessionAlmostExpired(){
    stopTitleBlink();
    giModalCloseEnabled = false;
    var sessionTimeout = (sessionMinutes - sessionWarningMinutes) * 60000;
    setTimeout(function() {
        sessionCheck();
    }, sessionTimeout);
    var url = 'index.php?controller=login&action=stillHere&ajax=1';
    giModalOpenAjaxContent(url);
    startTitleBlink('You still there?');
}

function sessionExpired(){
    stopTitleBlink();
    var url = 'index.php?controller=login&action=index&ajax=1';
    giModalOpenAjaxContent(url);
    startTitleBlink('You have been logged out.', 1000, 5);
}

function sessionReset(resetMinutes){
    stopTitleBlink();
    giModalClose();
    time = new Date().getTime();
    sessionCheck(resetMinutes);
}

function sessionCheck(resetMinutes){
    var curTime = new Date().getTime();
    if(resetMinutes == undefined){
        resetMinutes = sessionWarningMinutes;
    }
    if(curTime - time >= sessionMinutes * 60000){
        sessionAlmostExpired();
        //sessionExpired();
    } else if(curTime - time >= sessionWarningMinutes * 60000){
        sessionAlmostExpired();
    } else {
        var sessionTimeout = resetMinutes * 60000;
        setTimeout(function() {
            sessionCheck();
        }, sessionTimeout);
    }
}

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
        var errorFormElm = $('.form_element.error').first();
        var formOffSet = errorFormElm.offset().top;
        var windownHeight = $(window).height();
        var errorFormHeight = errorFormElm.height();

        $("html,body").animate({
            scrollTop: formOffSet + (windownHeight/2) - (errorFormHeight/2)
        }, 500, 'swing');
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

$(document).on('click', 'tr.selectable', function(){
    if($(this).find('input[type="checkbox"]').length == 1){
        var checkbox = $(this).find('input[type="checkbox"]');
        var checkVal = true;
        if(checkbox.is(':checked')){
            checkVal = false;
        }
        checkbox.not('[readonly]').prop('checked',checkVal).trigger('change');
    } else if($(this).find('input[type="radio"]').length == 1){
        var radio = $(this).find('input[type="radio"]');
        var radioName = radio.attr('name');
        var checkVal = true;
        if(radio.is(':checked')){
            checkVal = false;
        } else {
            $('input[name="' + radioName + '"]').prop('checked',false).trigger('change');
        }
        radio.not('[readonly]').prop('checked',checkVal).trigger('change');
    }
});

function toggleRowSelected(input){
    var row = input.closest('tr');
    var name = input.attr('name');
    var val = input.val();
    if(name.indexOf('[') > -1){
        name = name.substr(0, name.indexOf('['));
    }
    var table = row.closest('table');
    if(table.find('.check_all').length){
        if(!table.find('td input:checkbox:not(:checked)').length){
            table.find('.check_all input').prop('checked',true);
        }
    }
    
    if(selectedRows[name] == undefined){
        selectedRows[name] = [];
    }
    if(input.is(':checked')){
        row.addClass('checked');
        pushIfMissing(selectedRows[name], val);
        var selectedRowEvent = jQuery.Event('selectedRow');
        input.trigger(selectedRowEvent);
    } else {
        row.removeClass('checked');
        var selectedRowIndex = selectedRows[name].indexOf(val);
        
        if (selectedRowIndex >= 0) {
            selectedRows[name].splice( selectedRowIndex, 1 );
        }
        var unselectedRowEvent = jQuery.Event('unselectedRow');
        input.trigger(unselectedRowEvent);
    }
}

$(function(){
    $('td.checkbox_column input, td.select_row_column input').each(function(){
        toggleRowSelected($(this));
    });
});

$(document).on('change', 'td.checkbox_column input, td.select_row_column input', function(){
    toggleRowSelected($(this));
});

$(document).on('change', '.check_all input[type="checkbox"]', function(){
    var cellIndex = $(this).parents('th').index();
    var table = $(this).parents('table');
    var checkVal = false;
    if($(this).is(':checked')){
        checkVal = true;
    }
    table.find('tbody tr').each(function(){
        $(this).find('td').eq(cellIndex).find('input[type="checkbox"]').not('[readonly]').prop('checked',checkVal).trigger('change');
    });
});

$(document).on('click', '#toggle_fullscreen', function () {
    fullscreenToggle();
});

$(window).resize(function () {
    windowResized();
    updateStickyBar();
    if (typeof repositionNestedForm === 'function') {
        var clearPosition = 0; //Set clearPosition as 0, otherwise repositionNestedForm function show the hidden detail view.
        repositionNestedForm(clearPosition);
    }
    setMainWindowHeight();
    setMenuBarHeight();
    setListBarTableHeight();
});

$(document).on('click', '.coming_soon', function(e){
    e.preventDefault();
    alert('This feature is in development.');
});

function replaceUITableRow(newRowHTML, modelId){
    $('tr[data-model-id="' + modelId + '"]').replaceWith(newRowHTML);
    newContentLoaded();
}

$(document).on('click', '.pagination_btn', function(e){
    e.preventDefault();
    var loadFromPaginationEvent = jQuery.Event('loadFromPagination');
    $(this).trigger(loadFromPaginationEvent);
    if(!loadFromPaginationEvent.isDefaultPrevented()){
        var indexUrl = $(this).attr('href');
        var uiTableWrap = $(this).parents('.ui_table_wrap');
        elmStartLoading(uiTableWrap, 'circle');
        jQuery.post(indexUrl + '&ajax=1', function (data) {
            //var parsedData = JSON.parse(data);
            if (data.uiTable != undefined) {
                uiTableWrap.replaceWith(data.uiTable);
                newContentLoaded();
            }
        });
    }
});

$(document).on('click', '.limit_btn', function(e){
    e.preventDefault();
    
    var updateResultsPerPageEvent = jQuery.Event('updateResultsPerPage');
    $(this).trigger(updateResultsPerPageEvent);
    if(!updateResultsPerPageEvent.isDefaultPrevented()){
        var limit = $(this).data('limit');
        var useAjax = $(this).data('ajax');
        if(limit != undefined && limit > 0){
            document.cookie = 'ui_table_items_per_page=' + limit + '; path=/; max-age=2592000;';
        }
        var indexUrl = $(this).attr('href');
        if(useAjax != undefined && useAjax == 1){
            var uiTableWrap = $(this).parents('.ui_table_wrap');
            elmStartLoading(uiTableWrap, 'circle');
            jQuery.post(indexUrl + '&ajax=1', function (data) {
                //var parsedData = JSON.parse(data);
                if (data.uiTable != undefined) {
                    uiTableWrap.replaceWith(data.uiTable);
                    newContentLoaded();
                }
            });
        } else {
            window.location.href = indexUrl;
        }
    }
});

$(document).on('click', '.load_more_btn', function(e){
    e.preventDefault();
    var loadMoreBtn = $(this);
    var indexUrl = $(this).attr('href');
    var uiTableWrap = $(this).parents('.ui_table_wrap');
    var uiTable = uiTableWrap.find('.ui_table');
    var uiBody = uiTable.find('.ui_list_body');
    var nextPage = loadMoreBtn.data('next-page');
    var pageCount = loadMoreBtn.data('page-count');
    var step = loadMoreBtn.data('step');
    if (step === undefined) {
        step = 1;
    }
    var reverse = loadMoreBtn.data('reverse');
    if (reverse === undefined) {
        reverse = 0;
    }
    var listUrl = indexUrl+ '&ajax=1&pageNumber=' + nextPage;
    elmStartLoading(uiBody);
    jQuery.post(listUrl + '&onlyRows=1', function (data) {
        //var parsedData = JSON.parse(data);
        if (data.uiTableRows != undefined) {
            var newNextPage = nextPage + step;
            var hasPageToLoad = true;
            if ( (step > 0 && newNextPage > pageCount) || (step < 0 && newNextPage < 1) ) {
                hasPageToLoad = false;
            }
            
            if(!hasPageToLoad){
                if (loadMoreBtn.closest('.load_more_btn_wrap').length){
                    loadMoreBtn.closest('.load_more_btn_wrap').remove();
                } else {
                    loadMoreBtn.remove();
                }
            } else {
                loadMoreBtn.data('next-page', newNextPage);
            }
            if ((step < 0 && reverse == 0) || (step > 0 && reverse == 1)) {
               uiBody.prepend(data.uiTableRows); 
            } else {
                uiBody.append(data.uiTableRows);
            }
            newContentLoaded();
            setListBarURL(listUrl);
            elmStopLoading(uiBody);
        }
    });
});

function loadUITableData(){
    $('.ui_table_wrap[data-init-load]').each(function(){
        var indexUrl = $(this).data('init-load');
        var uiTableWrap = $(this);
        elmStartLoading(uiTableWrap, 'circle');
        indexUrl += '&targetId=main&ajax=1';
        jQuery.post(indexUrl, function (data) {
            if (data.uiTable != undefined) {
                uiTableWrap.replaceWith(data.uiTable);
                newContentLoaded();
            }
        });
    });
}

$(document).on('click', 'a.disabled', function(e){
    e.preventDefault();
});

$(document).on('dblclick', '.html_and_code_block .syntaxhighlighter', function(e){
    if($(this).is('.expanded')){
        $(this).removeClass('expanded');
    } else {
        $(this).addClass('expanded');
    }
});

function loadInElement(url, e, element, elementClass, callback, callbackEvent){
    if(e != undefined){
        e.preventDefault();
        e.stopPropagation();
    } 
    var finalClass = 'ajaxed_contents loaded';
    if(elementClass != undefined){
        finalClass = finalClass + ' ' + elementClass;
    }
    if(element.data('original-html') == undefined){
        var originalHTML = element.html();
        element.data('original-html', originalHTML);
    }
    if(!element.is('.no_loading_class')){
        elmStartLoading(element);
    }
    jQuery.post(url, function (data) {
        var showContent = true;
        if(data.jqueryAction) {
            eval(data.jqueryAction);
        }
        if(data.newUrl){
            loadInElement(data.newUrl, undefined, element);
        } else {
            if (showContent && data.mainContent != undefined) {
                element.html(data.mainContent);
                element.removeClass();
                element.addClass(finalClass);
                element.data('url', url);
                if(data.dynamicScripts != undefined){
                    ajaxLoadMultiScripts(data.dynamicScripts);
                }
                if(data.uploaderScripts) {
                    eval(data.uploaderScripts);
                }
                newContentLoaded();
                element.fadeIn('fast',function(){
                    elmStopLoading(element);
                    $('textarea').trigger('autosize.resize');
                    createJSignatures();
                    if(!element.is('.auto_load')){
                        var focusField = element.find('input:not(.gi_field_date),textarea').filter(':visible:first');
                        if(!focusField.is('.autofocus_off') && !focusField.closest('.autofocus_off').length){
                            var focusFieldVal = focusField.val();
                            focusField.focus().val('').val(focusFieldVal);
                        }
                    }
                    if(callback !== undefined){
                        callback();
                    }

                    //Add jquery callback action for modal form
                    if(data.jqueryCallbackAction) {
                        eval(data.jqueryCallbackAction);
                    }
                    if(callbackEvent !== undefined){
                        element.trigger(callbackEvent);
                    } else {
                        element.trigger('loadedInElement');
                    }
                });
            } else {
                //Error
                //todo: do more for other error cases
                console.log(data);
                var errorCode = 2500;
                var errorURL = 'index.php?controller=static&action=error&errorCode=' + errorCode + '&ajax=1';
                jQuery.post(errorURL, function (data) {
                    element.html(data.mainContent);
                });
                elmStopLoading(element);
            }
        }
    });
}

function unloadElement(element){
    var loadInId = element.attr('id');
    elmStopLoading(element);
    var originalHTML = element.data('original-html');
    if(originalHTML == undefined){
        element.html('');
    } else {
        element.html(originalHTML);
    }
    element.data('original-html', undefined);
    if(loadInId != undefined){
        $('.load_in_element[data-load-in-id="' + loadInId + '"]').show();
    }
    
    newContentLoaded();
}

$(document).on('submit', '.ajaxed_contents form', function(e){
    e.preventDefault();
    var element = $(this).closest('.ajaxed_contents');
    var ajaxedContentsSubmitFormEvent = jQuery.Event('ajaxedContentsSubmitForm');
    $(this).trigger(ajaxedContentsSubmitFormEvent);
    if(!ajaxedContentsSubmitFormEvent.isDefaultPrevented()){
        var submitUrl = element.data('url');
        var form = $(this);
        var formData = false;
        if (window.FormData){
            formData = new FormData(form[0]);
        } else {
            formData = form.serialize();
        }
        //startPageLoader();
        elmStartLoading(element);
console.log('submitUrl:'+submitUrl);         
        jQuery.ajax({
            type: 'POST',
            url: submitUrl,
            data: formData,
            contentType: false,
            processData: false,
//            async: false,
            success: function (data) {
console.log(data);                 
            if (data.success) {
                var saveBtns = $('.submit_btn[data-load-in-id="' + element.attr('id') + '"]');
                saveBtns.each(function(){
                    var otherBtn = $(this).prev();
                    if(otherBtn.length && !otherBtn.is(':visible')){
                        otherBtn.show();
                    }
                    $(this).remove();
                });
                
                if(data.newUrl){
                    if(data.newUrl == 'refresh'){
                        location.reload();
                    } else {
                        //Ver.4 : default is ajax
                        if (data.redirect) {
                            stopPageLoader();
                            elmStopLoading(element);
                            window.location.href = data.newUrl;
                        } else {
                            loadInElement(data.newUrl, undefined, element);
                            var targetId = element.attr('id');
                            if (targetId !== undefined) {
                                historyPushState('reload', data.newUrl, targetId);
                            }
                        }
                    }
                } else {
                    var ajaxedContentsSubmitFormSuccessEvent = jQuery.Event('ajaxedContentsSubmitFormSuccess');
                    form.trigger(ajaxedContentsSubmitFormSuccessEvent);
                    if(!ajaxedContentsSubmitFormSuccessEvent.isDefaultPrevented()){
                        if (data.mainContent) {
                            element.html(data.mainContent);
                            newContentLoaded();
                        }
                        elmStopLoading(element);
                    }
                }
            } else {
                element.html(data.mainContent);
                newContentLoaded();
                //stopPageLoader();
                elmStopLoading(element);
                element.trigger('ajaxContentLoaded');
                form.trigger('ajaxedContentsSubmitFormFail');
            }
            if(data.reloadContents) {
                var url = element.data('url');
                url += '&ajax=1';
                var elementClass = element.attr('class');
                loadInElement(url, undefined, element, elementClass);
            }
            if(data.dynamicScripts != undefined){
                ajaxLoadMultiScripts(data.dynamicScripts);
            }
            if(data.uploaderScripts) {
                eval(data.uploaderScripts);
            }
            if(data.jqueryAction) {
                eval(data.jqueryAction);
                unloadElement(element);
            }
            
            //Add jquery callback action for modal form
            if(data.jqueryCallbackAction) {
                eval(data.jqueryCallbackAction);
            }
        }});
    }
});

$(document).on('click', '.load_in_element:not(.disabled), .load_after_element:not(.disabled), .load_before_element:not(.disabled)', function(e){
    var btn = $(this);
    var hideBtn = btn.data('hide-btn');
    var changeBtn = btn.data('change-btn');
    var loadInElementEvent = jQuery.Event('loadInElement');
    $(this).trigger(loadInElementEvent);
    if(!loadInElementEvent.isDefaultPrevented()){
        var callback = undefined;
        var callbackEvent = undefined;
        if(hideBtn){
            btn.hide();
        }
        if(changeBtn != undefined){
            if(changeBtn == 'save'){
                var btnClone = btn.clone();
                btn.hide();
                btnClone.find('.icon.edit').removeClass('edit').addClass('check');
                btnClone.attr('title', 'Save');
                btnClone.addClass('submit_btn');
                btnClone.removeClass('load_in_element');
                btnClone.insertAfter(btn);
            }
        }
        var elementClass = $(this).data('element-class');
        var element = null;
        if(btn.is('.load_after_element')){
            var loadAfterId = $(this).data('load-after-id');
            element = $('<div class="tmp_load_element loading"></div>');
            element.insertAfter('#' + loadAfterId);
            callbackEvent = jQuery.Event('loadedAfterElement');
        } else if(btn.is('.load_before_element')){
            var loadBeforeId = $(this).data('load-before-id');
            element = $('<div class="tmp_load_element loading"></div>');
            element.insertBefore('#' + loadBeforeId);
            callbackEvent = jQuery.Event('loadedBeforeElement');
        } else {
            var loadInId = $(this).data('load-in-id');
            element = $('#' + loadInId);
        }
        var url = btn.attr('href');
        if(url == undefined){
            url = btn.data('url');
        }
        url += '&ajax=1';
        loadInElement(url, e, element, elementClass, callback, callbackEvent);
    } else {
        e.preventDefault();
    }
});

$.fn.giHighlight = function() {
    var elm = $(this);
    elm.css({
        transition: 'background 0.4s'
    });
    elm.addClass('highlighted');
    setTimeout(function(){
        elm.removeClass('highlighted');
    }, 800);
}

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

$(document).on('mouseover', '.hover_icon_to_edit', function(e){
    var icon = $(this).find('.icon');
    var colourClass = '';
    if(icon.hasClass('light_gray')){
        colourClass = 'light_gray';
    } else if(icon.hasClass('gray')){
        colourClass = 'gray';
    } else if(icon.hasClass('dark_gray')){
        colourClass = 'dark_gray';
    } else if(icon.hasClass('black')){
        colourClass = 'black';
    } else if(icon.hasClass('primary')){
        colourClass = 'primary';
    } else if(icon.hasClass('green')){
        colourClass = 'green';
    } else if(icon.hasClass('red')){
        colourClass = 'red';
    }
    var curClass = icon.attr('class');
    var curIconClass = curClass.replace('icon','');
    curIconClass = curIconClass.replace(colourClass,'');
    icon.data('icon-class', curIconClass);
    icon.removeClass(curIconClass);
    icon.addClass('edit');
});

$(document).on('mouseout', '.hover_icon_to_edit', function(e){
    var icon = $(this).find('.icon');
    var oldIconClass = icon.data('icon-class');
    icon.removeClass('edit');
    if(oldIconClass != undefined){
        icon.addClass(oldIconClass);
    }
});

/** Overlay buttons: start **/
//function closeAllOverlayTargetSections(){
//    $("div[class^='target_ref_']").each(function(){
//        closeAdvanced($(this));
//    });
//}

function scrollToTargetEl(scrollToTargetEl) {
    var targetTop = 0;
    if (scrollToTargetEl !== undefined && scrollToTargetEl.offset() !== undefined) {
        targetTop = scrollToTargetEl.offset().top;
    }
    if ($(scrollToTargetEl).closest('#'+mainViewWrapId).length) {
        //Case sidebar view : scroll main_window_view_wrap
        var mainViewWrapOffset= $('#'+mainViewWrapId).offset();
        $('#'+mainViewWrapId).animate({
                scrollTop: (targetTop - mainViewWrapOffset.top)
        }, 600);
    } else if ($(scrollToTargetEl).closest('#list_bar').length) {
        //In the list_bar, no need to scroll
    } else {
        $("html, body").animate({
                scrollTop: targetTop
        }, 600);
    }
}

function openOverlayTargetSection(ref){
    $('.target_ref_'+ref).each(function(){
        openAdvanced($(this));
    });
}

function closeOverlay() {
    if ($('#overlay_grids_wrap').length > 0) {
        $('body').removeClass('overlay_open');
        $('#overlay_grids_wrap').removeClass('open').hide();
    }
}

function openOverlay() {
    if ($('#overlay_grids_wrap').length > 0) {
        $('body').addClass('overlay_open');
        $('#overlay_grids_wrap').fadeIn(100, function(){
            $('#overlay_grids_wrap').addClass('open');
        });
        scrollToTargetEl($('#overlay_grids_wrap'));
    }
}

$(document).on('click', '.overlay_grid', function(){
    var targetRef = $(this).data('ref');
    //Open the selected target section
    openOverlayTargetSection(targetRef);
    
    //Scroll to the selected section 
    var targetEl= $('#'+mainViewWrapId).find('.target_ref_' + targetRef)
    scrollToTargetEl(targetEl);
    
    closeOverlay();
});
$(document).on('click', '.advanced.sidebar_cagegory:not(.open)', function(){
    //Scroll to the selected section
    var targetRef = $(this).data('ref');
    var targetEl= $('#'+mainViewWrapId).find('.target_ref_' + targetRef);
    scrollToTargetEl(targetEl);
});
$(document).on('click', '.close_overlay', function(e){
    closeOverlay();
});
$(document).on('click', '#overlay_grids_wrap.open', function(e){
    closeOverlay();
});
$(document).on('click', '.open_overlay', function(e){
    openOverlay();
});
/** Overlay button: end **/

var loadingGifs = [
    'loading_bar_01.gif',
    'loading_bar_02.gif',
    'loading_bar_03.gif',
    'loading_bar_04.gif'
];

function elmStartLoading(elm, defaultStyle){
    elm.addClass('loading');
    var desc = elm.data('loader-desc');
    var style = elm.data('loader-style');
    if(style == undefined || style == ''){
        style = defaultStyle;
    }
    var loader = elm.children('.loader_gif');
    
    if(!loader.length){
        var gifSRC = 'resources/media/loaders/';
        if(style != undefined && style != ''){
            switch(style){
                case 'gears':
                    gifSRC += 'loading_gears.gif';
                    break;
                case 'circle':
                default:
                    style = 'circle';
                    gifSRC += 'loading_circle.gif';
            }
        } else {
            var randomKey = Math.floor(Math.random() * Math.floor(loadingGifs.length));
            gifSRC += loadingGifs[randomKey];
        }
        var descSpan = '';
        if(desc != undefined && desc != ''){
            descSpan = '<span class="loader_desc">' + desc + '</span>';
        }
        elm.append('<span class="loader_gif ' + style + '">' + descSpan + '<img title="Loader" /></span>');
        elm.children('.loader_gif').children('img').attr('src', gifSRC);
    }
}

function elmStopLoading(elm){
    elm.removeClass('loading');
    var loader = elm.children('.loader_gif');
    if(loader.length){
        loader.remove();
    }
}

function replaceUrlParam(url, paramName, paramValue){
    if (paramValue == null) {
        paramValue = '';
    }
    var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
    if (url.search(pattern)>=0) {
        return url.replace(pattern,'$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/,'');
    return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}

function getUrlParamValue(url, paramName) {
    var params = url.split("&");
    for (var i=0;i<params.length;i++) {
            var pair = params[i].split("=");
            if(pair[0] == paramName){return pair[1];}
    }
    return;
}

var adminViewMode = false;
var adminViewModeLocked = false;
function toggleAdminOnly(){
    if(adminViewModeLocked){
        return;
    }
    adminViewModeLocked = true;
    if(adminViewMode){
        console.log('goodbye');
        $('.admin_only').hide();
        adminViewMode = false;
    } else {
        console.log('hello');
        $('.admin_only').show();
        adminViewMode = true;
    }
    setTimeout(function(){
        adminViewModeLocked = false;
    }, 100);
}

$(document).on('keydown', 'body', function(key){
    if(key.which == 192 && key.ctrlKey){
        toggleAdminOnly();
    }
});

var defaultTargetId = 'main_window';
$(document).on('click', 'a.ajax_link, .ajax_link_wrap a:not(.open_modal_form):not(.non_ajax_link)', function(e){
    e.preventDefault();
    var targetUrl = $(this).attr('href');
    var targetId = $(this).data('target-id');
    if (targetId === undefined) {
        targetId = defaultTargetId;
    }
    loadInElementByTargetId(targetUrl, targetId, true);
});

function reloadInElementByTargetId(targetId, curItemId) {
    var element = $('#'+targetId);
    if (element !== undefined) {
        var targetUrl = element.data('url');
        //@todo: if page number is greater than 1, it should load all pages under the page number too
        loadInElementByTargetId(targetUrl, targetId, null, curItemId);
    }
}
function loadInElementByTargetId(targetUrl, targetId, addHistory, curItemId) {
    var element = $('#'+targetId);
    if (element !== undefined && !element.hasClass('loading')) {
        var url = targetUrl;
        if (url !== undefined) {
            url += '&targetId='+targetId;
            var elementClass = element.attr('class');
            var ajaxUrl = url + '&ajax=1';
            //Add history before loading content because, there can be a redirection after loading
            if (addHistory!== undefined && addHistory) {
                historyPushState('reload', url, targetId);
            }
            loadInElement(ajaxUrl, undefined, element, elementClass, function(){
                afterLoadAjaxLink(targetId, curItemId);
            });
        }
    }
}
function afterLoadAjaxLink(targetId, curItemId) {
    //Remove the 'empty' content
    if (targetId == defaultTargetId) {
        $('#'+defaultTargetId).removeClass('empty');
        $('#'+defaultTargetId).prepend('<div id="clear_main_content" class="custom_btn" title="Clear Main Content"><span class="icon_wrap"><span class="icon eks"></span></span></div>');
    }
    if (targetId == 'list_bar') {
        //Set current id on the list
        if (curItemId !== undefined) {
            setCurrentOnListBar(curItemId);
        }
        //Set height and refresh IScroll
        setListBarTableHeight();
    }
    if (targetId == 'main_window') {
        //Set height and refresh IScroll
        setMainWindowHeight();
    }
}

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
    $('#'+defaultTargetId).html('');
    $('#'+defaultTargetId).addClass('empty');
    $('#list_bar .ui_tiles .ui_tile').removeClass('current');
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
function setListBarURL(url) {
    var listBar = $('#list_bar');
    if (listBar.length) {
        listBar.data('url', url);
    }
}
//Load multiple ajax
$.getMultiScripts = function(arr, path) {
    var cssRegex = /.\.css$/;
    var _arr = $.map(arr, function(scr) {
        if (cssRegex.test(scr)) {
            //CSS file
            return ajaxLoadCSS( (path||'') + scr );
        } else {
            return ajaxLoadJS( (path||'') + scr );
        }
    });

    _arr.push($.Deferred(function( deferred ){
        $( deferred.resolve );
    }));

    return $.when.apply($, _arr);
}
function ajaxLoadMultiScripts(urls) {
    if (!Array.isArray(urls)) {
        urls = [urls];
    }
    urls = filterSriptAlreadyIncluded(urls);
    $.getMultiScripts(urls, '').done(function() {
        // After all scripts loaded
        var jsRegex = /.\.js$/;
        var loadedJSArray = $.map(urls, function(value, index) {
            if (jsRegex.test(value) && !ajaxLoadedJSArray.includes(value)) {
                return value;
            }
        });
        // Added newly loaded javascript into the already loaded JS array by ajax
        ajaxLoadedJSArray = ajaxLoadedJSArray.concat(loadedJSArray);
    });
}
function ajaxLoadCSS(url) {
    $('head').append('<link rel="stylesheet" type="text/css" href="'+url+'" />');
}
function ajaxLoadJS(url) {
    var len = $('script[src^="'+url+'"]').length;
    //Load the JS only if it's not loaded already.
    if (!len) {
        $.getScript(url, setTimeout(function(){
            newContentLoaded(); 
         }, 500));
    }
}
function filterSriptAlreadyIncluded(arr){
    var jsRegex = /.\.js$/;
    var _arr = $.map(arr, function(value, index) {
        if (jsRegex.test(value)) {
            //Check only JS file
            if (!isScriptAlreadyIncluded(value)) {
                return value;
            }
        } else {
            //Other script like CSS
            return value;
        }
    });
    return _arr;
}
function isScriptAlreadyIncluded(src){
    //Already loaded scripts on load
    var scripts = document.getElementsByTagName("script");
    var jsSrc;
    var included = false;
    $.each(scripts, function(index, value) {
        if(value !== null) {
            jsSrc = value.getAttribute('src');
            //Check only JS file
            if (jsSrc !== null && value.getAttribute('type') == 'text/javascript') {
                if(jsSrc.indexOf(src) > -1) {
                    included = true;
                    return false;
                }
            }
        }
    });
    //Already loaded scripts by ajax
    $.each(ajaxLoadedJSArray, function(index, value) {
        if (value.indexOf(src) > -1) {
            included = true;
            return false;
        }
    });
    
    return included;
}
/** Browser history management **/
function historyPushState(id, url, targetId){
    if (targetId === undefined) {
        targetId = 'main_window';
    }
    var state = {id:id, target_id: targetId};
    history.pushState(state, null, replaceUrlParam(url,'ajax', 0));
}
window.onpopstate = function (event) {
    var state = event.state;
    if (state !== undefined && state !== null && state.id === 'reload' && state.target_id !== undefined) {
        // Reload contents
        var ajaxUrl = document.location + '&ajax=1';
        var targetId = history.state.target_id;
        var element = $('#'+targetId);
        var elementClass = element.attr('class');
        if (element !== undefined) {
            loadInElement(ajaxUrl, undefined, element, elementClass, function(){
                afterLoadAjaxLink(targetId);
                //Temp: clear all current class for now
                $('#list_bar .ui_tiles .ui_tile').removeClass('current');
            });
        }
    }
};
/** Browser history management:end **/

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
$(document).on('click', '.start_loader', function(){
    if(!$('.bos_loader').length){
        $('body').append('<div class="bos_loader"></div>');
    }
});

$(document).on('click', '.bos_loader', function(){
    $(this).remove();
});

$(document).on('click', '.close_alert', function(e){
    if (e.shiftKey) {
        var alerts = $(this).closest('.alerts_wrap');
        alerts.slideUp(300, function(){
            alerts.remove();
        });
    } 
    var alert = $(this).closest('.alert_message');
    alert.slideUp(300, function(){
        alert.remove();
    });
});

//@todo left menu
