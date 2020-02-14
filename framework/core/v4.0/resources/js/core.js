/*
layout.js v4.0.1
*/
let time = new Date().getTime();
let sessionWarningMinutes = 25;
let sessionMinutes = 30;
let mainViewWrapId = 'main_window_view_wrap';
let menuBarScrollId = 'main_nav_wrap';
let listBarScrollElId = 'list_table_wrap';
let sidebarScrollElId = 'main_window_sidebar_wrap';
let defaultTargetId = 'main_window';
let pageRedirecting = false;

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
    pageRedirecting = true;
});

$( window ).on('pageshow',function() {
    //stopPageLoader();
    pageRedirecting = false;
});

$(document).on('keyup', '*:not(input):not(textarea)', function(e){
    if (e.keyCode === 13) {
        e.preventDefault();
        e.stopPropagation();
        $(this).trigger('click');
    }
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

function makeItMoney(value, precision) {
    if(precision == undefined){
        precision = 2;
    }
    var roundedVal = preciseRound(value, precision);
    var money = roundedVal.toFixed(precision);
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

function makeItMoneyForDisplay(value, precision){
    if(precision == undefined){
        precision = 2;
    }
    var parsedVal = parseFloatNoNaN(value);
    var roundedVal = parsedVal.toFixed(precision);
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

function setCookie(name, value, days) {
    let d = new Date();
    d.setTime(d.getTime() + (days*24*60*60*1000));
    let expires = 'expires='+ d.toUTCString();
    let uniqueName = SESSION_NAME + '_' + name;
    let cookieValue = uniqueName + '=' + value + ';' + expires + ';path=/;'
    document.cookie = cookieValue;
}

function getCookie(name) {
    let uniqueName = SESSION_NAME + '_' + name + '=';
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(uniqueName) == 0) {
            return c.substring(uniqueName.length, c.length);
        }
    }
    return '';
}

Object.defineProperty(Number.prototype, 'pad', {
    value : function(size) {
        let s = String(this);
        while (s.length < (size || 2)) {s = "0" + s;}
        return s;
    }
});

Object.defineProperty(Array.prototype, 'unique', {
    value : function() {
        let a = this.concat();
        for(let i=0; i<a.length; ++i) {
            for(let j=i+1; j<a.length; ++j) {
                if(a[i] === a[j])
                    a.splice(j--, 1);
            }
        }

        return a;
    }
});

Object.defineProperty(Array.prototype, 'remove', {
    value : function() {
        let what, a = arguments, L = a.length, ax;
        while (L && this.length) {
            what = a[--L];
            while ((ax = this.indexOf(what)) !== -1) {
                this.splice(ax, 1);
            }
        }
        return this;
    }
});

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
    if(blinkInterval !== null){
        clearInterval(blinkInterval);
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

$(document).on('click','.advanced_btn,.other_advanced_btn', function(e){
    var advBtn = $(this);
    var advId = advBtn.data('adv-id');
    var advWrap = $('#' + advId);
    if(!advWrap.length){
        var advWrap = advBtn.closest('.advanced');
    }
    if(advWrap.is('.open') && !advBtn.is('.other_advanced_btn')){
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
    $(document).trigger('bindActionsToWindowResized');
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
    
    if($('.footer_bars').length){
        $('#content_wrap').css({
            paddingBottom: $('.footer_bars').outerHeight() + 'px'
        });
    }
}

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

function setListBarURL(url) {
    url = replaceUrlParam(url, 'fullView', 1);
    let listBar = $('#list_bar');
    if (listBar.length) {
        listBar.data('url', url);
    }
}

$(document).on('submit', '.search_box.use_ajax form', function(e){
    let preventAjaxSubmit = false;
    if(e.isDefaultPrevented()){
        preventAjaxSubmit = true;
    }
    e.preventDefault();
    
    var submitFormEvent = jQuery.Event('searchSubmitForm');
    $(this).trigger(submitFormEvent);
    if(preventAjaxSubmit){
        submitFormEvent.preventDefault();
    }
    if(!submitFormEvent.isDefaultPrevented()){
        var form = $(this);
        var formAction = form.attr('action');
        var searchBox = form.closest('.search_box');
        var targetId = searchBox.data('target-id');
        var uiTableWrap = $('#' + targetId);
        let hasIScrollElm = uiTableWrap.closest('.hasIScroll');
        elmStartLoading(uiTableWrap, 'circle');
        jQuery.ajax({type: 'POST', url: formAction, data: form.serialize(), success: function (data) {
                form.removeClass('submitting');
                form.data('submitting', false);
                if (data.uiTable != undefined) {
                    uiTableWrap.replaceWith(data.uiTable);
                    if(hasIScrollElm.length){
                        hasIScrollElm.removeClass('hasIScroll');
                    }
                    setListBarIScroll();
                    setListBarURL(replaceUrlParam(formAction, 'search', 0));
                } else if(data.mainContent != undefined && $('#list_bar').length){
                    //list bar assumed here
                    let sourceURL = data.sourceURL;
                    let selectedEl = $('#list_bar').find('.ui_tile.current');
                    if (selectedEl.length && selectedEl.data('model-id') !== undefined) {
                        sourceURL = replaceUrlParam(sourceURL, 'curId', selectedEl.data('model-id'));
                    } else {
                        historyPushState('reload', sourceURL, 'list_bar');
                    }
                    sourceURL = replaceUrlParam(sourceURL, 'ajax', null);
                    setListBarURL(sourceURL);
                    reloadElement($('#list_bar'));
                } else {
                    console.log(data);
                    loadErrorInElement(uiTableWrap, 2500);
                }
                
                newContentLoaded();
        }});
    }
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
    var toggledEvent = jQuery.Event('wasToggled');
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
            if($(this).is('.init_toggled')){
                $(this).slideUp('fast');
            } else {
                $(this).hide();
            }
        }
    });
    toggler.trigger(toggledEvent);
}

$(document).on('change', '.radio_toggler', function(){
    radioToggler($(this));
});
/***END RADIO TOGGLER SCRIPT***/

/***FIELD TOGGLER (mainly dropdowns)***/
function fieldElementToggler(toggler){
    var name = toggler.attr('name');
    var val = toggler.val();
    var toggledEvent = jQuery.Event('wasToggled');
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
            if($(this).is('.init_toggled')){
                $(this).slideUp('fast');
            } else {
                $(this).hide();
            }
        }
    });
    toggler.trigger(toggledEvent);
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
    var toggledEvent = jQuery.Event('wasToggled');
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
                if($(this).is('.init_toggled')){
                    $(this).slideUp('fast');
                } else {
                    $(this).hide();
                }
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
                if($(this).is('.init_toggled')){
                    $(this).slideUp('fast');
                } else {
                    $(this).hide();
                }
            } else if (dataElementArray.includes('NULL')) {
                $(this).slideDown('fast',function(){
                    visibleContentUpdate();
                });
            }
        });
    }
    toggler.trigger(toggledEvent);
}

$(document).on('change', '.checkbox_toggler', function(){
    checkboxToggler($(this));
});
/***END FIELD TOGGLER SCRIPT***/

/***NEW CONTENT LOADED***/
function newContentLoaded(){
    $(document).trigger('bindActionsToNewContent');
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
        if($(this).is('.advanced_content')){
            var advancedElm = $(this).parent('.advanced');
            if(advancedElm.length){
                advancedElm.addClass('loading');
            }
        }
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
    
    $('.flex_table').each(function(){
        var headers = $(this).find('> .flex_head > .flex_col');
        $(this).find('> .flex_row').not('.flex_head').each(function(){
            for (var i = 0; i < headers.length; i++) {
                var headCol = headers.eq(i);
                var headLabel = headCol.html();
                var curLabel = $(this).find('> .flex_col').eq(i).attr('data-label');
                if(curLabel == undefined || curLabel == ''){
                    $(this).find('> .flex_col').eq(i).attr('data-label', headLabel);
                }
            }
        });
    });

    $(document).trigger('actionsBoundToNewContent');
    loadUITableData();
    setUpAutoMaths();
    cleanEmptyBirdBeaks();
}
function visibleContentUpdate(){
    $(document).trigger('bindActionsToVisibleContent');
}
/***END NEW CONTENT LOADED SCRIPT***/

$(document).on('click','.cancel_submit_btn',function(){
    window.history.back();
});

/****SESSION CHECKER SCRIPTS****/
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
/****END SESSION CHECKER SCRIPTS****/

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
        } else {
            console.log(data);
            let errorDisplayMethod = 'append';
            if ((step < 0 && reverse == 0) || (step > 0 && reverse == 1)) {
                errorDisplayMethod = 'prepend';
            }
            loadErrorInElement(uiBody, 2500, errorDisplayMethod);
        }
    });
});

function loadErrorInElement(element, errorCode = 2500, errorDisplayMethod = 'replace'){
    let errorURL = 'index.php?controller=static&action=error&errorCode=' + errorCode + '&ajax=1';
    jQuery.post(errorURL, function (data) {
        let errorContent = data.mainContent;
        if(!element.length || errorDisplayMethod === 'modal'){
            giModalOpen(errorContent);
        } else if(errorDisplayMethod === 'replace'){
            element.html(errorContent);
        } else if(errorDisplayMethod === 'append'){
            element.append(errorContent);
        } else if(errorDisplayMethod === 'prepend'){
            element.prepend(errorContent); 
        }
    });
    if(element.is('.advanced_content')){
        let advancedElm = element.parent('.advanced');
        if(advancedElm.length){
            advancedElm.removeClass('loading');
        }
    }
    elmStopLoading(element);
}

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
    url = replaceUrlParam(url, 'ajax', 1);
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
                var unwrap = false;
                if(element.is('.tmp_load_element')){
                    unwrap = true;
                }
                element.removeClass();
                element.addClass(finalClass);
                if(data.elementClass !== undefined){
                    element.addClass(data.elementClass);
                }
                element.data('url', url);
                if(data.dynamicScripts != undefined){
                    ajaxLoadMultiScripts(data.dynamicScripts);
                }
                if(data.uploaderScripts) {
                    eval(data.uploaderScripts);
                }
                newContentLoaded();
//                element.fadeIn('fast',function(){
                    if(element.is('.advanced_content')){
                        var advancedElm = element.parent('.advanced');
                        if(advancedElm.length){
                            advancedElm.removeClass('loading');
                        }
                    }
                    elmStopLoading(element);
                    $('textarea').trigger('autosize.resize');
                    createJSignatures();
                    if(!element.is('.auto_load')){
                        var focusField = element.find('input:not(.gi_field_date):not(.selectric-input),textarea').filter(':visible:first');
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
                    if(unwrap){
                        element.find('>').first().unwrap();
                    }
//                });
            } else {
                //Error
                //todo: do more for other error cases
                console.log(data);
                loadErrorInElement(element, 2500);
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

function reloadElement(element, reloadUrl){
    if(element === undefined){
        return;
    }
    let url = element.data('url');
    if(reloadUrl !== undefined && reloadUrl !== null && reloadUrl !== ''){
        url = reloadUrl;
    }
    url += '&ajax=1';
    let elementClass = element.attr('class');
    elmStartLoading(element);
    loadInElement(url, undefined, element, elementClass);
}

$(document).on('click', '.refresh_page', function(){
    location.reload();
});

$(document).on('submit', '.ajaxed_contents form', function(e){
    let preventAjaxSubmit = false;
    if(e.isDefaultPrevented()){
        preventAjaxSubmit = true;
    }
    e.preventDefault();
    var element = $(this).closest('.ajaxed_contents');
    var ajaxedContentsSubmitFormEvent = jQuery.Event('ajaxedContentsSubmitForm');
    $(this).trigger(ajaxedContentsSubmitFormEvent);
    if(preventAjaxSubmit){
        ajaxedContentsSubmitFormEvent.preventDefault();
    }
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
        jQuery.ajax({
            type: 'POST',
            url: submitUrl,
            data: formData,
            contentType: false,
            processData: false,
//            async: false,
            success: function (data) {
            if (data.clear !== undefined && data.clear === 1) {
                element.removeClass('loaded');
                element.html('');
            }
            if (data.success) {
                element.trigger('ajaxedContentsSubmitFormSuccess');
                var saveBtns = $('.submit_btn[data-load-in-id="' + element.attr('id') + '"]');
                saveBtns.each(function(){
                    var otherBtn = $(this).prev();
                    if(otherBtn.length && !otherBtn.is(':visible')){
                        otherBtn.show();
                    }
                    if($(this).hasClass('one_at_a_time')){
                        let oaatRef = $(this).data('oaat-ref');
                        unhideOneAtAtime(oaatRef);
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
                            if (data.targetId !== undefined && data.targetId !== '') {
                                let newTargetElement = $('#' + data.targetId);
                                newTargetElement.html(data.mainContent);
                                reloadElement(element);
                            } else {
                                element.html(data.mainContent);
                            }
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
            if(data.reloadContents || data.loadNewContentUrl !== undefined) {
                let reloadUrl = null;
                if(data.loadNewContentUrl !== undefined){
                    reloadUrl = data.loadNewContentUrl;
                }
                reloadElement(element, reloadUrl);
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
        },
        error : function(data){
            loadErrorInElement(element, 2500);
            console.log(data);
        } 
        });
    }
});

$(document).on('click', '.load_in_element:not(.disabled), .load_after_element:not(.disabled), .load_before_element:not(.disabled)', function(e){
    var btn = $(this);
    var hideBtn = btn.data('hide-btn');
    var changeBtn = btn.data('change-btn');
    var loadInElementEvent = jQuery.Event('loadInElement');
    btn.trigger(loadInElementEvent);
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
                btnClone.removeClass('other_advanced_btn');
                btnClone.attr('title', 'Save');
                btnClone.addClass('submit_btn');
                btnClone.removeClass('load_in_element');
                btnClone.removeClass('load_after_element');
                btnClone.removeClass('load_before_element');
                btnClone.insertAfter(btn);
            }
        }
        var elementClass = btn.data('element-class');
        var element = null;
        if(btn.is('.load_after_element')){
            var loadAfterId = btn.data('load-after-id');
            element = $('<div class="tmp_load_element loading"></div>');
            element.insertAfter('#' + loadAfterId);
            callbackEvent = jQuery.Event('loadedAfterElement');
        } else if(btn.is('.load_before_element')){
            var loadBeforeId = btn.data('load-before-id');
            element = $('<div class="tmp_load_element loading"></div>');
            element.insertBefore('#' + loadBeforeId);
            callbackEvent = jQuery.Event('loadedBeforeElement');
        } else if(btn.is('.load_in_parent')){
            let loadInParentClass = btn.data('load-in-parent-class');
            let loadInClass = btn.data('load-in-class');
            let parentElement = btn.closest('.' + loadInParentClass);
            if(loadInClass == undefined || loadInClass == ''){
                element = parentElement;
            } else {
                element = parentElement.closestChild('.' + loadInClass);
            }
        } else {
            var loadInId = btn.data('load-in-id');
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
$(document).on('click', '.advanced.sidebar_category:not(.open)', function(){
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
        return removeUrlParam(url, paramName);
    }
    var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
    if (url.search(pattern)>=0) {
        return url.replace(pattern,'$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/,'');
    return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}

function removeUrlParam(url, paramName){
    let newUrl = url.split('?')[0],
        param,
        params_arr = [],
        queryString = (url.indexOf('?') !== -1) ? url.split('?')[1] : '';
    if (queryString !== '') {
        params_arr = queryString.split('&');
        for (let i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split('=')[0];
            if (param === paramName) {
                params_arr.splice(i, 1);
            }
        }
        newUrl = newUrl + '?' + params_arr.join('&');
    }
    return newUrl;
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

let prevDTUrls = [];

$(document).on('click', 'a.ajax_link, .ajax_link_wrap a:not(.open_modal_form):not(.non_ajax_link)', function(e){
    e.preventDefault();
    let targetUrl = $(this).attr('href');
    let targetId = $(this).data('target-id');
    if (targetId === undefined) {
        let ajaxLinkWrap = $(this).closest('.ajax_link_wrap');
        if (ajaxLinkWrap.length) {
            targetId = ajaxLinkWrap.data('target-id');
        }
    }
    if (targetId === undefined) {
        targetId = defaultTargetId;
    }
    let clearPrevDTUrls = true;
    if(targetId == defaultTargetId && $(this).closest('#' + defaultTargetId).length){
        //if the button clicked is a child of the default target element and it is also the new target for the content
        prevDTUrls.push(window.location.href);
        clearPrevDTUrls = false;
    }
    loadInElementByTargetId(targetUrl, targetId, true, null, clearPrevDTUrls);
});

$(document).on('click', '.ajaxed_contents a.load_in_ajaxed_contents', function(e){
    e.preventDefault();
    var element = $(this).closest('.ajaxed_contents');
    var url = $(this).attr('href');
    url += '&ajax=1';
    var elementClass = element.attr('class');
    loadInElement(url, undefined, element, elementClass);
});

function reloadInElementByTargetId(targetId, curItemId) {
    var element = $('#'+targetId);
    if (element.length) {
        var targetUrl = element.data('url');
        //@todo: if page number is greater than 1, it should load all pages under the page number too
        loadInElementByTargetId(targetUrl, targetId, null, curItemId);
    }
}
function loadInElementByTargetId(targetUrl, targetId) {
    let callback = null;
    let argVals = [
        false, //addHistory
        null, //curItemId
        true //clearPrevDTUrls
    ];
    let validArgIndex = 0;
    for (let i = 0; i < arguments.length; i++) {
        let arg = arguments[i];
        if (arg instanceof Function) {
            callback = arg;
        } else {
            argVals[validArgIndex] = arg;
            validArgIndex++;
        }
    }
    let addHistory = argVals[0];
    let curItemId = argVals[1];
    let clearPrevDTUrls = argVals[2];
    
    if(clearPrevDTUrls){
        prevDTUrls = [];
    }
    var element = $('#'+targetId);
    if (element.length && !element.hasClass('loading')) {
        var url = targetUrl;
        if (url !== undefined) {
            url += '&targetId='+targetId;
            var elementClass = element.attr('class');
            var ajaxUrl = url + '&ajax=1';
            //Add history before loading content because, there can be a redirection after loading
            if (addHistory) {
                historyPushState('reload', url, targetId);
            }
            loadInElement(ajaxUrl, undefined, element, elementClass, function(){
                afterLoadAjaxLink(targetId, curItemId);
                if(callback !== undefined && callback !== null){
                    callback();
                }
            });
        }
    }
}

function removeElementByTargetId(targetId) {
    var element = $('#'+targetId);
    if (element.length) {
        $(element).slideUp();
    }
}

function afterLoadAjaxLink(targetId, curItemId) {
    //Remove the 'empty' content
    if (targetId == defaultTargetId) {
        $('#'+defaultTargetId).removeClass('empty');
    }
    if (targetId == 'list_bar') {
        //Set current id on the list
        if (curItemId !== undefined && curItemId !== null) {
            setCurrentOnListBar(curItemId);
        }
        //Set height and refresh IScroll
        setListBarTableHeight();
    }
    if (targetId == 'main_window') {
        //Set height and refresh IScroll
        setMainWindowHeight();
    }
    verifyMainWindowContent();
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
        var cssRegex = /.\.css$/;
        var loadedCSSArray = $.map(urls, function(value, index) {
            if (cssRegex.test(value) && !ajaxLoadedCSSArray.includes(value)) {
                return value;
            }
        });
        // Added newly loaded javascript into the already loaded JS array by ajax
        ajaxLoadedJSArray = ajaxLoadedJSArray.concat(loadedJSArray);
        ajaxLoadedCSSArray = ajaxLoadedCSSArray.concat(loadedCSSArray);
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
            if (!isCSSAlreadyIncluded(value)) {
                return value;
            }
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
function isCSSAlreadyIncluded(href){
    //Already loaded styleSheets on load
    var styleSheets = document.getElementsByTagName("link");
    var cssHref;
    var included = false;
    $.each(styleSheets, function(index, value) {
        if(value !== null) {
            cssHref = value.getAttribute('href');
            //Check only JS file
            if (cssHref !== null && value.getAttribute('type') == 'text/css') {
                if(cssHref.indexOf(href) > -1) {
                    included = true;
                    return false;
                }
            }
        }
    });
    //Already loaded styleSheets by ajax
    $.each(ajaxLoadedCSSArray, function(index, value) {
        if (value.indexOf(href) > -1) {
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

$(document).on('click', '.close_section', function(e){
    var section = $(this).closest('.closable_section');
    section.slideUp(300, function(){
        section.remove();
    });
});

function setUpAutoMaths(){
    $('.auto_math:not(".listening")').each(function(){
        var autoMath = $(this);
        var xValField = autoMath.data('xval-field');
        var yValField = autoMath.data('yval-field');
        
        var form = autoMath.closest('form');
        if(xValField != undefined){
            //check if we want to auto update the xval with a field
            var xListenOn = '';
            if(form != undefined){
                xListenOn += '#' + form.attr('id') + ' ';
            }
            xListenOn += 'input[name="' + xValField + '"]';
            //listen to keyup on the xval field
            if($(xListenOn).length){
                $(document).on('keyup', xListenOn, function(){
                    autoMath.data('xval', $(this).val());
                    autoMath.trigger('calculateAutoMath');
                });
                autoMath.data('xval', $(xListenOn).val());
            }
        }
        if(yValField != undefined){
            //check if we want to auto update the yval with a field
            var yListenOn = '';
            if(form != undefined){
                yListenOn += '#' + form.attr('id') + ' ';
            }
            yListenOn += 'input[name="' + yValField + '"]';
            //listen to keyup on the yval field
            if($(yListenOn).length){
                $(document).on('keyup', yListenOn, function(){
                    autoMath.data('yval', $(this).val());
                    autoMath.trigger('calculateAutoMath');
                });
                autoMath.data('yval', $(yListenOn).val());
            }
        }
        //add class to prevent adding duplicate listeners
        autoMath.addClass('listening');
        autoMath.trigger('calculateAutoMath');
    });
}

$(document).on('calculateAutoMath', '.auto_math', function(){
    var autoMath = $(this);
    var xVal = parseFloatNoNaN(autoMath.data('xval'));
    var yVal = parseFloatNoNaN(autoMath.data('yval'));
    var op = autoMath.data('op');
    var newValue = null;
    switch(op){
        case 'addition':
            newValue = xVal + yVal;
            break;
        case 'subtraction':
            newValue = xVal - yVal;
            break;
        case 'multiply':
            newValue = xVal * yVal;
            break;
        case 'divide':
            newValue = xVal/yVal;
            break;
    }
    var newString = newValue;
    if(autoMath.is('.money')){
        newString = '$' + makeItMoneyForDisplay(newValue);
    }
    if(newValue == null){
        newString = 'Cannot calculate.';
    }
    autoMath.html(newString);
});

function addPageAlert(alertData){
    let alertCode = alertData.code;
    let alertMsg = alertData.msg;
    let alertColour = alertData.colour;
    
    let url = 'index.php?controller=notification&action=getAlertView&ajax=1';
    if(alertCode != undefined){
        url += '&code=' + alertCode;
    }
    if(alertMsg != undefined){
        url += '&msg=' + alertMsg;
    }
    if(alertColour != undefined){
        url += '&colour=' + alertColour;
    }
    
    jQuery.post(url, function (data) {
        if(data.mainContent != undefined){
            let newAlert = $(data.mainContent);
            let pageAlertWrap = $('#page_alerts');
            if(!pageAlertWrap.length){
                pageAlertWrap = $('<div id="#page_alerts" class="alerts_wrap"></div>');
                $('body').prepend(pageAlertWrap);
            }
            newAlert.addClass('hide_on_load');
            pageAlertWrap.append(newAlert);
            newAlert.slideDown();
        } else {
            console.log(data);
        }
    });
}

function loadHTMLInElementByTargetId(targetId, html, type) {
    let element = $('#' + targetId);
    if (element.length) {
        if (type !== undefined) {
            if (type === 'prepend') {
                element.prepend(html);
            } else if (type === 'replace') {
                element.replaceWith(html);
            } else {
                element.append(html);
            }
        } else {
            element.append(html);
        }
    }
}

$(document).on('click', '.click_to_copy', function(e){
    e.preventDefault();
    let tmpInput = $('<input>');
    let copyString = $(this).data('copy-string');
    
    if(copyString === undefined && $(this).is('a')){
        copyString = $(this).attr('href');
    } else if(copyString === undefined){
        copyString = $(this).data('url');
    }
    let copiedMsg = $('<div class="click_to_copy_msg">');
    if(copyString === undefined){
        copiedMsg.html('Error Copying');
    } else {
        $('body').append(tmpInput);
        tmpInput.val(copyString).select();
        document.execCommand('copy');
        tmpInput.remove();
        copiedMsg.html('Copied!');
    }
    
    $(this).append(copiedMsg);
    copiedMsg.fadeIn(function(){
        setTimeout(function(){
            copiedMsg.fadeOut(function(){
                copiedMsg.remove();
            });
        }, 2000);
    });
});

$(document).on('click', '.non_anchor_link', function(e){
    e.preventDefault();
    let url = $(this).data('url');
    let target = $(this).data('target');
    if(e.ctrlKey){
        target = '_blank';
    }
    if(url !== undefined && url !== null){
        if(target === '_blank'){
            window.open(url);
        } else {
            window.location.href = url;
        }
    }
});

function replacePrefixedClasses(element, prefix, newClass){
    let elmClasses = element[0].className.split(' ').filter(c => !c.startsWith(prefix));
    element[0].className = elmClasses.join(' ').trim();
    element.addClass(newClass);
    return true;
}

$(document).on('click', '.bird_beak_menu_wrap .bird_beak', function(e){
    e.preventDefault();
    let wrap = $(this).closest('.bird_beak_menu_wrap');
    if(wrap.is('.open')){
        closeBirdBeak(wrap);
    } else {
        openBirdBeak(wrap);
    }
});

//$(document).on('click', '.bird_beak_menu > *', function(e){
//    let wrap = $(this).closest('.bird_beak_menu_wrap');
//    closeBirdBeak(wrap);
//});

function cleanEmptyBirdBeaks(){
    let allBeakWraps = $('.bird_beak_menu_wrap');
    for(let i=0; i<allBeakWraps.length; i++){
        let beak = allBeakWraps.eq(i);
        let beakBtns = beak.find('.bird_beak_menu *');
        if(!beakBtns.length){
            beak.remove();
        }
    }
}

function openBirdBeak(birdBeakMenuWrap){
    let allBeakWraps = $('.bird_beak_menu_wrap');
    for(let i=0; i<allBeakWraps.length; i++){
        let otherBeak = allBeakWraps.eq(i);
        closeBirdBeak(otherBeak);
    }
    let menu = birdBeakMenuWrap.find('.bird_beak_menu');
    menu.slideDown(500, function(){
        birdBeakMenuWrap.addClass('open');
    });
}

function closeBirdBeak(birdBeakMenuWrap){
    let menu = birdBeakMenuWrap.find('.bird_beak_menu');
    menu.slideUp(500, function(){
        birdBeakMenuWrap.removeClass('open');
    });
    
}
