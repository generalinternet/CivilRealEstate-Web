/*
forms.js v3.0.9
*/
function formatFields() {
    $('.time_field').not('.read_only').not('.hasDatepicker').each(function(){
        var thisField = $(this);
        $(this).timepicker({
            timeFormat: thisField.attr('data-time-format'),
            stepMinute: parseInt(thisField.attr('data-step-minute')),
            minuteGrid: thisField.attr('data-minute-grid'),
            controlType: thisField.attr('data-control-type'),
            hour: 12,
            onSelect: function(){
                $(this).trigger('change');
                if ($('input[name="dummy"]').length) {
                    $('input[name="dummy"]').focus();
                }
            }
        }).keyup(function(e) {
            if(e.keyCode === 8 || e.keyCode === 46) {
                //$.datepicker._clearDate(this);
            }
        });
    });
    $('.date_field').not('.read_only').not('.hasDatepicker').each(function(){        
        var thisField = $(this);
        var minDate = thisField.attr('data-min-date');
        var minDateFrom = thisField.attr('data-min-date-from');
        var maxDate = thisField.attr('data-max-date');
        var maxDateFrom = thisField.attr('data-max-date-from');
        var defaultDate = thisField.attr('data-default-date');
        $(this).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
            changeMonth: thisField.attr('data-change-month'),
            changeYear: thisField.attr('data-change-year'),
            yearRange: thisField.attr('data-year-range'),
            dateFormat: thisField.attr('data-date-format'),
            firstDay: thisField.attr('data-first-day'),
            onSelect: function(){
                $(this).trigger('change');
                if ($('input[name="dummy"]').length) {
                    $('input[name="dummy"]').focus();
                }
            }
        }).keyup(function(e) {
            if(e.keyCode === 8 || e.keyCode === 46) {
                //$.datepicker._clearDate(this);
            }
        });
        if (defaultDate !== 'null') {
            $(this).datepicker( 'option', 'defaultDate', defaultDate );
        }
        if (minDate !== 'null') {
            $(this).datepicker( 'option', 'minDate', minDate );
        }
        if (maxDate !== 'null') {
            $(this).datepicker( 'option', 'maxDate', maxDate );
        }
        
        if(minDateFrom != undefined && minDateFrom != ''){
            var minDateFromField = $(this).closest('form').find('input[name="' + minDateFrom + '"]');
            if(minDateFromField.length){
                var setMinDate = minDateFromField.val();
                if (minDate !== 'null') {
                    var dateA = new Date(setMinDate);
                    var dateB = new Date(minDate);
                    if (dateB > dateA) {
                        setMinDate = minDate;
                    }
                }
                thisField.datepicker( 'option', 'minDate', setMinDate);
                minDateFromField.on('change', function(){
                    var setMinDate = minDateFromField.val();
                    if (minDate !== 'null') {
                        var dateA = new Date(setMinDate);
                        var dateB = new Date(minDate);
                        if (dateB > dateA) {
                            setMinDate = minDate;
                        }
                    }
                    thisField.datepicker( 'option', 'minDate', setMinDate);
                });
            }
        }
        
        if(maxDateFrom != undefined && maxDateFrom != ''){
            var maxDateFromField = $(this).closest('form').find('input[name="' + maxDateFrom + '"]');
            if(maxDateFromField.length){
                var setMaxDate = maxDateFromField.val();
                if (maxDate !== 'null') {
                    var dateA = new Date(setMaxDate);
                    var dateB = new Date(maxDate);
                    if (dateB < dateA) {
                        setMaxDate = maxDate;
                    }
                }
                thisField.datepicker( 'option', 'maxDate', setMaxDate);
                maxDateFromField.on('change', function(){
                    var setMaxDate = maxDateFromField.val();
                    if (maxDate !== 'null') {
                        var dateA = new Date(setMaxDate);
                        var dateB = new Date(maxDate);
                        if (dateB < dateA) {
                            setMaxDate = maxDate;
                        }
                    }
                    thisField.datepicker( 'option', 'maxDate', setMaxDate);
                });
            }
        }
    });
    $('.date_time_field').not('.read_only').not('.hasDatepicker').each(function(){
        var thisField = $(this);
        var minDate = thisField.attr('data-min-date');
        var minDateFrom = thisField.attr('data-min-date-from');
        var maxDate = thisField.attr('data-max-date');
        var maxDateFrom = thisField.attr('data-max-date-from');
        var defaultDate = thisField.attr('data-default-date');
        $(this).datetimepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
            changeMonth: thisField.attr('data-change-month'),
            changeYear: thisField.attr('data-change-year'),
            yearRange: thisField.attr('data-year-range'),
            dateFormat: thisField.attr('data-date-format'),
            firstDay: thisField.attr('data-first-day'),
            timeFormat: thisField.attr('data-time-format'),
            stepMinute: parseInt(thisField.attr('data-step-minute')),
            minuteGrid: thisField.attr('data-minute-grid'),
            controlType: thisField.attr('data-control-type'),
            hour: 12,
            onSelect: function(){
                $(this).trigger('change');
                if ($('input[name="dummy"]').length) {
                    $('input[name="dummy"]').focus();
                }
            }
        }).keyup(function(e) {
            if(e.keyCode === 8 || e.keyCode === 46) {
                //$.datepicker._clearDate(this);
            }
        });
        if (defaultDate !== 'null') {
            $(this).datepicker( 'option', 'defaultDate', defaultDate );
        }
        if (minDate !== 'null') {
            $(this).datepicker( 'option', 'minDate', minDate );
        }
        if (maxDate !== 'null') {
            $(this).datepicker( 'option', 'maxDate', maxDate );
        }
        
        if(minDateFrom != undefined && minDateFrom != ''){
            var minDateFromField = $(this).closest('form').find('input[name="' + minDateFrom + '"]');
            if(minDateFromField.length){
                var setMinDate = minDateFromField.val();
                if (minDate !== 'null') {
                    var dateA = new Date(setMinDate);
                    var dateB = new Date(minDate);
                    if (dateB > dateA) {
                        setMinDate = minDate;
                    }
                }
                thisField.datepicker( 'option', 'minDate', setMinDate);
                minDateFromField.on('change', function(){
                    var setMinDate = minDateFromField.val();
                    if (minDate !== 'null') {
                        var dateA = new Date(setMinDate);
                        var dateB = new Date(minDate);
                        if (dateB > dateA) {
                            setMinDate = minDate;
                        }
                    }
                    thisField.datepicker( 'option', 'minDate', setMinDate);
                });
            }
        }
        
        if(maxDateFrom != undefined && maxDateFrom != ''){
            var maxDateFromField = $(this).closest('form').find('input[name="' + maxDateFrom + '"]');
            if(maxDateFromField.length){
                var setMaxDate = maxDateFromField.val();
                if (maxDate !== 'null') {
                    var dateA = new Date(setMaxDate);
                    var dateB = new Date(maxDate);
                    if (dateB < dateA) {
                        setMaxDate = maxDate;
                    }
                }
                thisField.datepicker( 'option', 'maxDate', setMaxDate);
                maxDateFromField.on('change', function(){
                    var setMaxDate = maxDateFromField.val();
                    if (maxDate !== 'null') {
                        var dateA = new Date(setMaxDate);
                        var dateB = new Date(maxDate);
                        if (dateB < dateA) {
                            setMaxDate = maxDate;
                        }
                    }
                    thisField.datepicker( 'option', 'maxDate', setMaxDate);
                });
            }
        }
    });
    autosize($('textarea').not('.autosizeOff'));
    autosize.update($('textarea').not('.autosizeOff'));
    $('textarea.wysiwyg').not('.trumbowyg-textarea').each(function(){
        var textarea = $(this);
        textarea.trumbowyg({
            svgPath: 'resources/external/js/trumbowyg/icons.svg',
            removeformatPasted: true,
            autogrow: true,
//            semantic: false,
            btns: getWYSIWYGBtns(textarea)
        });
    });
    
    $("input[type='checkbox'].onoff").not('.gi_styled').after("<span class='checkbox_box'></span>");
    $("input[type='checkbox'].onoff").addClass('gi_styled');
    
    $("label > input[type='checkbox']").not('.gi_styled').after("<span class='checkbox_box'></span>");
    $("label > input[type='checkbox']").addClass('gi_styled');
    
    $("label > input[type='radio']").not('.gi_styled').after("<span class='radio_box'></span>");
    $("label > input[type='radio']").addClass('gi_styled');    
    
    $('.autocomp').not('.ac_added').each(function(i,elm){
        elm = $(elm);
        var elmName = elm.attr('name');
        var postArrayKey = elm.data('post-array-key');
        var postArrayKeyID = '';
        if(postArrayKey != undefined){
            postArrayKeyID = '_' + postArrayKey;
        }
        var cleanElmName = elmName.replace(/\[(.*)\]/g,'');
        var acElmID = cleanElmName + '_autocomp' + postArrayKeyID;
        var acElm = $('#'+acElmID);
        var acURL = acElm.data('url');
        var acRemFull = true;
        if (acElm.data('rem-full') === false) {
            var acRemFull = false;
        }
        var acMultiple = true;
        if (acElm.data('multiple') === false) {
            var acMultiple = false;
        }
        var acDuplicates = 0;
        if (acElm.data('duplicates') === true) {
            var acDuplicates = 1;
        }
        var acResultsElmID = 'acresults_' + cleanElmName + postArrayKeyID;
        var acResultsElm = $('#'+acResultsElmID);
        var acAppendToID = acElm.data('append-to');
        var acMinLength = acElm.data('min-length');
        if (acAppendToID!== undefined && acAppendToID!== 'self') {
            if (!$('#'+acAppendToID).length) {
                $('body').append('<div id="'+acAppendToID+'" class="ac_results"></div>');
            }
            var acAppendTo = $('#'+acAppendToID);
        } else {
            var acAppendTo = acResultsElm;
        }
        if (acMultiple) {
            var acMultiElmID = 'aclist_' + cleanElmName + postArrayKeyID;
            var acMultiElm = $('#'+acMultiElmID);
            $(document).on('click tap','#'+acMultiElmID+' .ac_remove',function(){
                var dataID = $(this).data('id');
                var terms = elm.val().split(/,\s*/);
                var newTerms = [];
                var termLength = terms.length;
                var termRemoved = false;
                for(i=0;i<termLength;i++){
                    var curTerm = terms[i];
                    if (curTerm!=dataID || termRemoved) {
                        newTerms.push(curTerm);
                    } else {
                        termRemoved = true;
                    }
                }
                var listItem = $(this).parents('li');
                $(this).remove();
                listItem.remove();
                elm.val( newTerms.join(',') );
                checkACLimit(elm);
                acElm.trigger('autocompleteRemovedItem', [dataID]);
            });
        }
        
        acElm.bind('autocompleteFill',function(e, fillData){
            var acElm = $(this);
            var acURL = acElm.data('url');
            var prefill = fillData.prefill;
            let acFelm = acElm.closest('.form_element');
            if(acFelm.length){
                acFelm.addClass('searching');
            }
            jQuery.post(acURL+'&curVal='+fillData.value, function(data){
                var dataLabel = data.label;
                var dataResult = data.autoResult;
                var dataValue = data.value;
                elm.val(dataValue);
                if (acMultiple) {
                    acMultiElm.html('');
                    var vals = elm.val().split(/,\s*/);
                    var labels = dataLabel;
                    var autoResults = dataResult;
                    var valCount = vals.length;
                    for(i=0; i<valCount; i++){
                        var dataID = vals[i];
                        dataLabel = labels[i];
                        var dataAutoResult = autoResults[i];
                        acMultiElm.append('<li data-id="'+dataID+'"><span class="ac_remove custom_btn" data-id="'+dataID+'"><span class="icon_wrap"><span class="icon primary remove_sml"></span></span></span>'+dataAutoResult+'</li>');
                    }
                    checkACLimit(elm);
                } else {
                    acElm.val(dataLabel);
                }
                if(acFelm.length){
                    acFelm.removeClass('searching');
                }
                if(prefill){
                    acElm.trigger('autocompletePreFill', [data]);
                } else {
                    acElm.trigger('autocompleteFilled', [data]);
                }
            });
        });
        
        if (elm.val()!=='') {
            var curVal = elm.val();
            acElm.trigger('autocompleteFill',{value : curVal, prefill : true});
            
        }
        if(!acElm.is('.read_only')){
            acElm.autocomplete({
                minLength: acMinLength,
                autoFocus: true,
                appendTo: acAppendTo,
                source: function(request, response){
                    var searchURL = acElm.data('url');
                    var pageNumber = acElm.data('page-number');
                    if(pageNumber != undefined && pageNumber){
                        searchURL += '&pageNumber=' + acElm.data('page-number');
                    }
                    $.getJSON(searchURL, {
                        term: request.term,
                        current: elm.val(),
                        allowDups: acDuplicates
                    }, response );
                },
                focus: function(event, ui){
                    return false;
                },
                select: function(event, ui){
                    acElm.data('page-number', null);
                    var selectItemEvent = jQuery.Event('autocompleteSelectItem');
                    acElm.trigger(selectItemEvent, [ui]);
                    if(!selectItemEvent.isDefaultPrevented()){
                        if (acMultiple) {
                            acElm.val('');
                            $(this).blur();
                            var terms = elm.val().split(/,\s*/);
                            var strVal = String(ui.item.value);
                            if(terms.indexOf(strVal)===-1 || acDuplicates){
                                if (elm.val()!=='') {
                                    var vals = elm.val().split(',');
                                } else {
                                    var vals = [];
                                }                    
                                vals.push(ui.item.value);
                                elm.val( vals.join(',') );
                                acMultiElm.append('<li data-id="'+ui.item.value+'"><span class="ac_remove custom_btn" data-id="'+ui.item.value+'"><span class="icon_wrap"><span class="icon primary remove_sml"></span></span></span>'+ui.item.autoResult+'</li>');
                                checkACLimit(elm);
                            } else {
                                acMultiElm.children('li[data-id="'+ui.item.value+'"]').effect("highlight", {}, 1000);
                            }                    
                        } else {
                            acElm.val( ui.item.label );
                            elm.val( ui.item.value );
                            $(this).blur();
                        }
                        acElm.trigger('autocompleteSelected', [ui]);
                    }
                    return false;
                },
                change : function(event, ui){
                    acElm.data('page-number', null);
                    if (elm.val()==='' && !acMultiple && acRemFull) {
                        acElm.val('');
                    }
                },
                open: function(event, ui){

                },
                close : function (event, ui){
                    acElm.data('page-number', null);
                    var term = this.value;
                    if (elm.val()==='' && !acMultiple && acRemFull) {
                        acElm.val('');
                    }
                },
                search : function (event, ui){
                    let acFelm = acElm.closest('.form_element');
                    if(acFelm.length){
                        acFelm.addClass('searching');
                    }
                },
                response : function (event, ui){
                    let acFelm = acElm.closest('.form_element');
                    if(acFelm.length){
                        acFelm.removeClass('searching');
                    }
                }
            }).data('ui-autocomplete')._renderItem = function(ul, item){
                var hoverTitle = item.hoverTitle;
                var hoverTitleAttr = '';
                if(hoverTitle != undefined && hoverTitle != ''){
                    hoverTitleAttr = 'title="' + hoverTitle + '"';
                }
                var liClass = item.liClass;
                var liClassAttr = '';
                if(liClass != undefined && liClass != ''){
                    liClassAttr = 'class="' + liClass + '"';
                }
                var anchorClass = item.anchorClass;
                var anchorClassAttr = '';
                if(anchorClass != undefined && anchorClass != ''){
                    anchorClassAttr = 'class="' + anchorClass + '"';
                }
                return $( '<li ' + liClassAttr + ' ' + hoverTitleAttr + '>' )
                .append( '<a ' + anchorClassAttr + '>' + item.autoResult + '</a>' )
                .appendTo( ul );
            };
            elm.addClass('ac_added');
            if(acMinLength == 0){
                acElm.on('focus', function(){
                    $(this).autocomplete('search', '');
                });
            }
            if (acRemFull) {
                acElm.on('focusout', function() {
                    acElm.data('page-number', null);
                    //var menu = $(this).data("autocomplete").menu;
                    //$(this).data("autocomplete")._trigger( "select", e, { item: menu.element[0].children[0] } );
                });
                acElm.on('keydown', function(event) {
                    acElm.data('page-number', null);
                    if($(this).is('[readonly]')){
                        return false;
                    }
                    var key = event.keyCode || event.charCode;
                    if (key===9 || key===13) {
                        //var menu = $(this).data("autocomplete").menu;
                        //$(this).data("autocomplete")._trigger( "select", e, { item: menu.element[0].children[0] } );
                    } else {
                        if (elm.val()!=='' && !acMultiple) {
                            if( key === 8 || key === 46 ) {
                                $(this).val('');
                                elm.val('');
                            } else {
                                $(this).val('');
                                elm.val('');
                            }
                            acElm.trigger('autocompleteRemFull');
                        }
                    }
                });
            } else {
                acElm.on('keydown', function(event) {
                    if($(this).is('[readonly]')){
                        return false;
                    }
                    if (elm.val()!=='' && !acMultiple) {
                        elm.val('');
                        acElm.trigger('autocompleteRemoved');
                    }
                });
            }
        }
    });
    
    $('.tagit_field').not('.tagit_added').each(function(){
        var tagLimit = $(this).data('tag-limit');
        if(tagLimit==0){
            tagLimit = null;
        }
        var tagReadOnly = false;
        if($(this).attr('readonly')){
            tagReadOnly = true;
        }
        var tagPlaceholder = $(this).attr('placeholder');
        var tagitSettings = {
            removeConfirmation: true,
            caseSensitive: false,
            allowSpaces: true,
            readOnly: tagReadOnly,
            tagLimit: tagLimit,
            placeholderText: tagPlaceholder
        };
        var tagAutocompURL = $(this).data('url');
        if(tagAutocompURL != undefined){
            var acMinLength = $(this).data('min-length');
            var acAppendToID = $(this).data('append-to');
            tagitSettings['autocomplete'] = {
                minLength: acMinLength,
                autoFocus: true,
                //appendTo: acAppendTo,
                source: function(request, response){
                    $.getJSON(tagAutocompURL, {
                        term: request.term
                    }, response );
                }
            }
            if (acAppendToID!== undefined && acAppendToID!== 'self') {
                if (!$('#'+acAppendToID).length) {
                    $('body').append('<div id="'+acAppendToID+'" class="ac_results"></div>');
                }
                var acAppendTo = $('#'+acAppendToID);
                tagitSettings['autocomplete']['appendTo'] = acAppendTo;
            }
        }
        $(this).tagit(tagitSettings);
        $(this).addClass('tagit_added');
    });
    
    $('.colour_picker_field').not('.read_only').each(function(){
        var thisField = $(this);
        var formElm = thisField.closest('.form_element');
        var colour = thisField.val();
        thisField.wheelColorPicker({
            htmlOptions: true,
            live: true,
            mobile: true,
            preview: true,
            rounding: 0
        });
        thisField.wheelColorPicker('setValue', colour);
        formElm.addClass('colour_picker_added');
    });
    
    $('select').not('.selectriced').selectric({
        optionsItemBuilder: function(itemData, el, index){
            var label = itemData.text;
            var optObj = itemData.element;
            if(optObj.data('raw-label') !==  undefined){
                label = optObj.data('raw-label');
            }
            return label;
        },
        labelBuilder: function(curItemData) {
            var label = curItemData.text;
            var optObj = curItemData.element;
            if(optObj.data('raw-label') !==  undefined){
                label = optObj.data('raw-label');
            }
            var labelClass = curItemData.className;
            finalLabel = '<span class="' + labelClass + '">' + label + '</span>';
            return finalLabel;
        },
        onInit: function(){
            $(this).addClass('selectriced');
        }
    });
    
    formatTextExpander2000s();
    createJSignatures();
    $(document).trigger('formatFields');
}

function getWYSIWYGBtns(wysiwyg){
    var btns = [];
    if(wysiwyg.data('wyg-html')){
        btns.push(['viewHTML']);
    }
    if(wysiwyg.data('wyg-undo')){
        btns.push(['undo', 'redo']);
    }
    if(wysiwyg.data('wyg-format')){
        btns.push(['formatting']);
    }
    var designBtns = [];
    if(wysiwyg.data('wyg-bold')){
        designBtns.push('bold');
    }
    if(wysiwyg.data('wyg-italic')){
        designBtns.push('italic');
    }
    if(wysiwyg.data('wyg-underline')){
        designBtns.push('underline');
    }
    if(wysiwyg.data('wyg-strike')){
        designBtns.push('del');
    }
    btns.push(designBtns);
    var subsupBtns = [];
    if(wysiwyg.data('wyg-superscript')){
        subsupBtns.push('superscript');
    }
    if(wysiwyg.data('wyg-subscript')){
        subsupBtns.push('subscript');
    }
    btns.push(subsupBtns);
    if(wysiwyg.data('wyg-link')){
        btns.push(['link']);
    }
    if(wysiwyg.data('wyg-justify')){
        btns.push('btnGrp-justify');
    }
    if(wysiwyg.data('wyg-lists')){
        btns.push('btnGrp-lists');
    }
    if(wysiwyg.data('wyg-rule')){
        btns.push(['horizontalRule']);
    }
    if(wysiwyg.data('wyg-code')){
        btns.push(['preformatted']);
    }
    if(wysiwyg.data('wyg-table')){
        btns.push('table');
    }
    if(wysiwyg.data('wyg-unformat')){
        btns.push(['removeformat']);
    }
    if(wysiwyg.data('wyg-fullscreen')){
        btns.push(['fullscreen']);
    }
    return btns;
}

function checkACLimit(elm){
    var elmName = elm.attr('name');
    var acElmID = elmName+'_autocomp';
    var acElm = $('#'+acElmID);
    var acDrop = acElm.closest('.autocomp_field_wrap').find('.autocomp_dropdown');
    var terms = elm.val().split(/,\s*/);
    var termCount = terms.length;
    var limit = acElm.data('limit');
    if(limit > 0 && terms!= '' && termCount >= limit){
        acDrop.hide();
        acElm.hide();
    } else {
        acDrop.show();
        acElm.show();
    }
}

function inputControl(input,format){
    var value = input.val();
    var values = value.split("");
    var update = "";
    var transition = "";
    var id = "";
    var minVal = input.data('min-val');
    var maxVal = input.data('max-val');
    if (format === 'int'){
        var expression=/[\-]|(^\d+$)|(^\d+\.\d+$)/;
        var finalExpression=/^\-?[0-9]*$/;
    } else if (format === 'pos_int'){
        var expression=/(^\d+$)|(^\d+\.\d+$)/;
        var finalExpression=/^[0-9]*$/;
    } else if (format === 'float') {
        var expression=/[\-]|(^\d+$)|(^\d+\.\d+$)|[,\.]/;
        var finalExpression=/^(\-?[0-9]*[,\.]?\d{0,10})$/;
    } else if (format === 'money') {
        var expression=/[\-]|(^\d+$)|(^\d+\.\d+$)|[,\.]/;
        var finalExpression=/^(\-?[0-9]*[,\.]?\d{0,2})$/;
    } else if (format === 'money_rate') {
        var expression=/[\-]|(^\d+$)|(^\d+\.\d+$)|[,\.]/;
        var finalExpression=/^(\-?[0-9]*[,\.]?\d{0,7})$/;
    }
    for(id in values){
        if (expression.test(values[id])===true && values[id]!==''){
            transition += ''+values[id].replace(',','');
            if(finalExpression.test(transition)===true){
                update += ''+values[id].replace(',','');
            }
        }
    }
    if(minVal != undefined && parseFloatNoNaN(update) < minVal){
        update = parseFloatNoNaN(minVal);
    }
    if(maxVal != undefined && parseFloatNoNaN(update) > maxVal){
        update = parseFloatNoNaN(maxVal);
    }
    if(value != update){
        input.val(update);
    }
}

$(document).on('submit', 'form', function(e){
    let submitting = $(this).data('submitting');
    if(submitting !== undefined && submitting === true){
        e.preventDefault();
        console.log('double submission prevented');
    }
    $(this).data('submitting', true);
});

$(document).on('keydown', 'textarea', function(e){
    if(e.keyCode === 9) {
        e.preventDefault();
        if(!$(this).is('.wysiwyg')){
            var start = $(this).get(0).selectionStart;
            var end = $(this).get(0).selectionEnd;
            $(this).val($(this).val().substring(0, start) + "\t" + $(this).val().substring(end));
            $(this).get(0).selectionStart = $(this).get(0).selectionEnd = start + 1;
        }
    }
});

$(document).on('keydown', '.trumbowyg-editor', function(e) {
    if(e.keyCode === 9) {
        // prevent the focus lose
        e.preventDefault();
        var textarea = $(this).siblings('textarea.wysiwyg');
        textarea.trumbowyg('execCmd', {
            cmd: 'insertHTML',
            param: '&emsp;'
        });
    }
});

$(document).on('keyup','.int_field',function(){
    inputControl($(this),'int');
});

$(document).on('keyup','.pos_int_field',function(){
    inputControl($(this),'pos_int');
});

$(document).on('keyup','.money_field',function(){
    inputControl($(this),'money');
});

$(document).on('keyup','.money_rate_field',function(){
    inputControl($(this),'money_rate');
});

$(document).on('keyup','.decimal_field, .percentage_field',function(){
    inputControl($(this),'float');
});

$(document).on('click','input[type="radio"]:not(.stay_on)',function(e){
    e.stopPropagation();
    var radioName = $(this).attr('name');
    var prevVal = true;
    if ($(this).data('prev-val') === true) {
        $(this).prop('checked', false);
        $(this).trigger('change');
        prevVal = false;
    } else {
        $(this).parents('form').find('input[name="'+radioName+'"]').data('prev-val',false);
        //not triggering change because already triggers by default
    }
    $(this).data('prev-val',prevVal);
});

$(document).on('change','input[type="checkbox"]', function(e){
    var felm = $(this).closest('.form_element');
    var maxSelCount = felm.data('max-selections');
    if(maxSelCount == undefined || maxSelCount == 0){
        return;
    }
    var form = $(this).closest('form');
    var name = $(this).attr('name');
    if(name.indexOf('[') > -1){
        name = name.substr(0, name.indexOf('['));
    }
    var curSelCount = form.find('input[name="' + name + '[]"]:checked').length;
    if($(this).is(':checked')){
        if(maxSelCount <= curSelCount){
            felm.addClass('max_reached');
            form.find('input[name="' + name + '[]"]').not(':checked').attr('disabled','disabled').addClass('max_reached');
        }
    } else {
        if(maxSelCount > curSelCount){
            felm.removeClass('max_reached');
            form.find('input[name="' + name + '[]"].max_reached').attr('disabled',null);
        }
    }
});

$(document).on('click','input[type="checkbox"].read_only, input[type="radio"].read_only',function(e){
    e.preventDefault();
    e.stopPropagation();
});

$(document).on('keydown','.form_element.error input, .form_element.error textarea, .trumbowyg-editor, input.error, textarea.error',function(){
    if($(this).is(':focus')){
        $(this).removeClass('error');
        $(this).closest('.form_element').removeClass('error');
        $(this).closest('.form_element').find('.field_error').remove();
    }
});

$(document).on('change','.form_element.error input, .form_element.error select, input.error, select.error',function(){
    var selectricWrapper = $(this).closest('.selectric-wrapper');
    if($(this).is(':focus') || selectricWrapper.is('.selectric-focus')){
        $(this).removeClass('error');
        $(this).closest('.form_element').removeClass('error');
        $(this).closest('.form_element').find('.field_error').remove();
        selectricWrapper.removeClass('selectric-error');
    }
});

$(document).on('click','.submit_btn',function(e){
    if(!$(this).is('.disabled')){
        var formSubmitBtnEvent = jQuery.Event('formSubmitBtn');
        $(this).trigger(formSubmitBtnEvent);
        if(!formSubmitBtnEvent.isDefaultPrevented()){
            e.preventDefault();
            var targetFormId = $(this).data('target-id');
            var loadInId = $(this).data('load-in-id');
            var parentForm = $(this).closest('form');
            if(targetFormId != undefined && $('#' + targetFormId).length){
                var parentForm = $('#' + targetFormId);
            }
            if(loadInId != undefined && $('#' + loadInId).find('form').first().length){
                var parentForm = $('#' + loadInId).find('form').first();
            }
            
            parentForm.submit();
            parentForm.addClass('submitting');
            var formSubmittedEvent = jQuery.Event('formSubmitted');
            parentForm.trigger(formSubmittedEvent);
        }
    }
});

$(document).on('formSubmitBtn', '.submit_btn[data-field-name]', function(e){
    e.preventDefault();
    let fieldName = $(this).data('field-name');
    let fieldValue = $(this).data('field-value');
    if(fieldValue == undefined){
        fieldValue = 1;
    }
    let parentForm = $(this).closest('form');
    let field = parentForm.find('input[name="' + fieldName + '"]');
    if(!field.length){
        field = $('<input type="hidden" name="' + fieldName + '" />');
        parentForm.append(field);
    } 
    field.val(fieldValue);
    parentForm.submit();
    parentForm.addClass('submitting');
});

$(document).on('click','.reset_btn',function(e){
    e.preventDefault();
    $(this).parents('form').trigger('reset');
});

$(document).on('keydown', function(e){
    if (e.which === 8 && !$(e.target).is('input, textarea, .trumbowyg-editor')) {
        e.preventDefault();
    }
});

$(document).on('click', '.form_element select', function(e){
    if($(this).data('opened')){
        $(this).blur();
    } else {
        $(this).data('opened', true);
    }
});

$(document).on('focus', '.form_element select', function(){
    $(this).parents('.form_element').addClass('focused');
});

$(document).on('focusout', '.form_element select', function(){
    $(this).parents('.form_element').removeClass('focused');
    $(this).data('opened', false);
});

$(document).on('focus', '.tagit input', function(){
    $(this).parents('.tagit').addClass('focused');
});

$(document).on('focusout', '.tagit input', function(){
    $(this).parents('.tagit').removeClass('focused');
});

function useAddress(useAddrBtn, forceUse){
    var prefix = useAddrBtn.data('field-prefix');
    if(prefix == undefined){
        prefix = '';
    }
    var suffix = useAddrBtn.data('field-suffix');
    if(suffix != undefined && suffix != ''){
        suffix = '_' + suffix;
    }
    var curRegion = $('#field_' + prefix + 'addr_region' + suffix).val();
    if(forceUse || curRegion == 'NULL'){
        var addrStreet = useAddrBtn.data('addr-street');
        var addrStreetTwo = useAddrBtn.data('addr-street-two');
        var addrCity = useAddrBtn.data('addr-city');
        var addrRegion = useAddrBtn.data('addr-region');
        var addrCode = useAddrBtn.data('addr-code');
        var addrCountry = useAddrBtn.data('addr-country');
        var addrCountryAndRegion = addrCountry + '_' + addrRegion;
        
        $('#field_' + prefix + 'addr_street' + suffix).val(addrStreet).trigger('change').giHighlight();
        $('#field_' + prefix + 'addr_street_two' + suffix).val(addrStreetTwo).trigger('change').giHighlight();
        $('#field_' + prefix + 'addr_city' + suffix).val(addrCity).trigger('change').giHighlight();
        
        $('#field_' + prefix + 'addr_code' + suffix).val(addrCode).trigger('change').giHighlight();
        $('#field_' + prefix + 'addr_country' + suffix).val(addrCountry).trigger('change').selectric('refresh').closest('.selectric-wrapper').giHighlight();
        
        var regionField = $('#field_' + prefix + 'addr_region' + suffix);
        
        if(regionField.find('option[value="' + addrCountryAndRegion + '"]').length){
            regionField.val(addrCountryAndRegion).trigger('change').selectric('refresh').closest('.selectric-wrapper').giHighlight();
        } else if(regionField.find('option[value="' + addrRegion + '"]').length){
            regionField.val(addrRegion).trigger('change').selectric('refresh').closest('.selectric-wrapper').giHighlight();
        } else {
            $('#field_' + prefix + 'custom_addr_region' + suffix).val(addrRegion).trigger('change').giHighlight();
        }
    
        useAddrBtn.trigger('addrUsed');
    }
}

function addUseAddrBtn(formElement, addrBtn){
    formElement.find('.use_this_address').remove();
    formElement.find('.addr_picker_wrap').remove();
    formElement.append(addrBtn);
    useAddress(formElement.find('.use_this_address'), false);
}

$(document).on('autocompleteselect', '.get_addr .gi_field_autocomplete', function (e, ui) {
    var formElement = $(this).closest('.form_element');
    if(ui.item.addrBtn != undefined){
        addUseAddrBtn(formElement, ui.item.addrBtn);
    }
});

$(document).on('autocompletePreFill autocompleteFilled','.get_addr .gi_field_autocomplete', function(e, data){
    var formElement = $(this).closest('.form_element');
    if(data.addrBtn != undefined){
        addUseAddrBtn(formElement, data.addrBtn);
    }
});

$(document).on('autocompleteselect', '.get_addr_view .gi_field_autocomplete', function (e, ui) {
    var formElement = $(this).closest('.form_element');
    var viewClass = '';
    if(formElement.is('.hide_addr_view')){
        viewClass = 'hide_on_load';
    }
    if(ui.item.addrView != undefined){
        if(formElement.find('.addr_view').length){
            formElement.find('.addr_view').replaceWith(ui.item.addrView);
        } else {
            formElement.append(ui.item.addrView);
        }
        formElement.find('.addr_view').addClass(viewClass);
        formElement.trigger('addrViewLoaded');
    }
});

$(document).on('autocompletePreFill autocompleteFilled','.get_addr_view .gi_field_autocomplete', function(e, data){
    var formElement = $(this).closest('.form_element');
    var viewClass = '';
    if(formElement.is('.hide_addr_view')){
        viewClass = 'hide_on_load';
    }
    if(data.addrView != undefined){
        if(formElement.find('.addr_view').length){
            formElement.find('.addr_view').replaceWith(data.addrView);
        } else {
            formElement.append(data.addrView);
        }
        formElement.find('.addr_view').addClass(viewClass);
        if(e.type == 'autocompletePreFill'){
            formElement.trigger('addrViewPreLoaded');
        } else {
            formElement.trigger('addrViewLoaded');
        }
    }
});
/*
$(document).on('autocompleteFilled','.get_addr .gi_field_autocomplete', function(e, data){
    var formElement = $(this).closest('.form_element');
    if(data.addrBtn != undefined){
        addUseAddrBtn(formElement, data.addrBtn);
    }
});
*/

$(document).on('autocompleteRemFull','.get_addr .gi_field_autocomplete', function(e){
    var formElement = $(this).closest('.form_element');
    formElement.find('.use_this_address').remove();
    formElement.find('.addr_picker_wrap').remove();
});

$(document).on('autocompleteRemFull','.get_addr_view .gi_field_autocomplete', function(e){
    var formElement = $(this).closest('.form_element');
    formElement.find('.addr_view').remove();
    formElement.trigger('addrViewRemoved');
});

$(document).on('click', '.use_this_address', function(e){
    e.preventDefault();
    useAddress($(this), true);
});

$(document).on('keyup change', '.mirror_value', function(){
    var thisVal = $(this).val();
    var mirrorField = $(this).data('mirror-field');
    var mirrorPrefix = $(this).data('mirror-prefix');
    if(mirrorPrefix == undefined){
        mirrorPrefix= '';
    }
    var mirrorSuffix = $(this).data('mirror-suffix');
    if(mirrorSuffix == undefined){
        mirrorSuffix = '';
    }
    var field = $('#field_' + mirrorPrefix + mirrorField + mirrorSuffix);
    field.val(thisVal);
    if (field.is('select')) {
        field.trigger('change').selectric('refresh');
    }
});

function makeAddrSame(fromPrefix, toPrefix, fromSuffix, toSuffix){
    if(fromSuffix == undefined){
        fromSuffix = '';
    }
    if(toSuffix == undefined){
        toSuffix = '';
    }
    var addrStreetField = $('#field_' + fromPrefix + 'addr_street' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_street');
    var addrStreetTwoField = $('#field_' + fromPrefix + 'addr_street_two' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_street_two');
    var addrCityField = $('#field_' + fromPrefix + 'addr_city' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_city');
    var addrRegionField = $('#field_' + fromPrefix + 'addr_region' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_region');
    var customAddrRegionField = $('#field_' + fromPrefix + 'custom_addr_region' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'custom_addr_region');
    var addrCodeField = $('#field_' + fromPrefix + 'addr_code' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_code');
    var addrCountryField = $('#field_' + fromPrefix + 'addr_country' + fromSuffix)
            .addClass('mirror_value')
            .data('mirror-prefix', toPrefix)
            .data('mirror-suffix', toSuffix)
            .data('mirror-field', 'addr_country');
    
    var addrStreet = addrStreetField.val();
    var addrStreetTwo = addrStreetTwoField.val();
    var addrCity = addrCityField.val();
    var addrRegion = addrRegionField.val();
    var addrCustomRegion = customAddrRegionField.val();
    var addrCode = addrCodeField.val();
    var addrCountry = addrCountryField.val();
    
    $('#field_' + toPrefix + 'addr_street').val(addrStreet).trigger('change');
    $('#field_' + toPrefix + 'addr_street_two').val(addrStreetTwo).trigger('change');
    $('#field_' + toPrefix + 'addr_city').val(addrCity).trigger('change');
    $('#field_' + toPrefix + 'addr_country').val(addrCountry).trigger('change').selectric('refresh');
    $('#field_' + toPrefix + 'addr_region').val(addrRegion).trigger('change').selectric('refresh');
    $('#field_' + toPrefix + 'custom_addr_region').val(addrCustomRegion).trigger('change');
    $('#field_' + toPrefix + 'addr_code').val(addrCode).trigger('change');
}

function makeAddrNotSame(fromPrefix, fromSuffix){
    if(fromSuffix == undefined){
        fromSuffix = '';
    }
    $('#field_' + fromPrefix + 'addr_street' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'addr_street_two' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'addr_city' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'addr_region' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'custom_addr_region' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'addr_code' + fromSuffix).removeClass('mirror_value');
    $('#field_' + fromPrefix + 'addr_country' + fromSuffix).removeClass('mirror_value');
}

function formatTextExpander2000s(){
    $('.text_expander_2000').not('.expander_added').each(function(){
        var textarea = $(this);
        var fieldContentWrap = textarea.closest('.field_content');
        var placeHolder = textarea.attr('placeholder');
        if(placeHolder == undefined){
            placeHolder = '';
        }
        fieldContentWrap.prepend('<input type="text" readonly="readonly" class="text_expander_display" placeholder="' + placeHolder + '" value="' + textarea.val().replace(/\n/g, ' ') + '" />');
        textarea.addClass('expander_added');
    });
}

$(document).on('focus','.text_expander_display', function(){
    var formElm = $(this).closest('.form_element');
    if(!formElm.is('.read_only') && !formElm.is('.disabled')){
        var textareaElm = formElm.clone();
        var textarea = textareaElm.find('textarea');
        textareaElm.find('.text_expander_display').remove();
        textarea.removeClass('text_expander_2000 expander_added');
        var textareaId = textarea.attr('id');
        textarea.removeAttr('id');
        var label = textareaElm.find('label.main').html();
        giModalOpen(giModalContent(label, '<div class="form_element hide_label text_filler_2000" data-textarea-id="'+textareaId+'">'+textareaElm.html()+'</div><div class="center_btns wrap_btns"><span class="other_btn close_gi_modal gray">Done</span></div>'),null, 'medium_sized');
    }
});

$(document).on('keyup','.text_filler_2000', function(e){
    var textareaId = $(this).data('textarea-id');
    var textarea = $('#'+textareaId);
    var newText = $(this).find('textarea').val();        
    var textareaDisplay = textarea.siblings('.text_expander_display');
    textarea.html(newText);
    textareaDisplay.val(newText.replace(/\n/g, ' '));
});

$(document).on('keydown','.text_filler_2000', function(e){
    var code = e.keyCode || e.which;
    var textareaId = $(this).data('textarea-id');
    var textarea = $('#'+textareaId);
    if (code == '9') {
        e.preventDefault();
        giModalClose();
        var formElement = textarea.closest('.form_element');
        var idx = formElement.index('.form_element') + 1;
        var nextFormElement = $('.form_element').eq(idx);
        var focusField = nextFormElement.find('input,textarea,select').filter(':visible:first');
        focusField.focus();        
    }
});

function createJSignatures(){
    // Digital signature element(jSignature jquery plugin)
    if ( typeof $.fn.jSignature!== 'undefined' && $.isFunction($.fn.jSignature) ){
        $('.jsignature_signarea.jsignature_added').html('').removeClass('jsignature_added');
        $('.jsignature_signarea').not('.jsignature_added').each(function() {
            var sign_el = $(this);
            var form_el = sign_el.closest('.form_element');
            var img_el = form_el.find('.jsignature_imgarea');
            var rewrite_el = form_el.find('.btn_jsignature_rewrite');
            sign_el.jSignature({
                'UndoButton': true,
                'decor-color': 'transparent'
            });
            form_el.find('input[type="button"]').addClass('other_btn');
            var check_el = form_el.find('input[type="checkbox"]');
            var img_data_el = form_el.find('.jsignature_imgdata');
            var img_type_el = form_el.find('.jsignature_imgtype');
            if(check_el.prop('checked') && img_data_el!=''){
                var i = new Image();
                var imgPath = img_data_el.data('img-path');
                if(imgPath != ''){
                    i.src = imgPath;
                } else {
                    i.src = 'data:' + img_type_el.val() + ',' + img_data_el.html();
                }
                $(i).appendTo(img_el.html(''));
                sign_el.addClass('hide_pad');
                img_el.fadeIn();
                rewrite_el.fadeIn();
            }
            // After writing a stroke
            sign_el.on('change', function(e){
                // Remove error message if there any
                $(e.target).closest('.form_element').find('.field_error').fadeOut();
                $(e.target).closest('.form_element').removeClass('error');
            });
            sign_el.addClass('jsignature_added');
        });
        
    }
}

$(document).on('click', '.jsignature_wrap input[type="checkbox"]', function() {
    var wrap_el = $(this).closest('.jsignature_wrap');
    var form_el = wrap_el.closest('.form_element');
    var sign_el = wrap_el.find('.jsignature_signarea');
    var img_el = wrap_el.find('.jsignature_imgarea');
    var rewrite_el = wrap_el.find('.btn_jsignature_rewrite');
    var img_data_el = wrap_el.find('.jsignature_imgdata');
    var img_type_el = form_el.find('.jsignature_imgtype');
    var unaltered = wrap_el.find('.jsignature_unaltered');
    if($(this).prop('checked')){
        var signatureCheck =  sign_el.jSignature('getData', 'native');
        if (signatureCheck.length === 0) {
            // No signature data
            if(!form_el.find('.field_error').length){
                form_el.find('.field_content').append('<div class="field_error"></div>');
            }
            form_el.find('.field_error').html('Signature is required.');
            form_el.find('.field_error').fadeIn();
            $(this).prop('checked', false);
            return;
        }
        var datapair = sign_el.jSignature('getData', 'image');
        // Generate a image object
        var i = new Image();
        i.src = 'data:' + datapair[0] + ',' + datapair[1];
        $(i).appendTo(img_el.html(''));
        sign_el.addClass('hide_pad');
        img_el.fadeIn();
        rewrite_el.fadeIn();
        img_type_el.val(datapair[0]);
        img_data_el.html(datapair[1]);
    } else {
        // Reset
        sign_el.jSignature('reset');
        img_el.hide();
        sign_el.removeClass('hide_pad');
        rewrite_el.hide();
        img_type_el.val('');
        img_data_el.html('');
        if(unaltered.length){
            unaltered.remove();
        }
    }
});

// Click the Rewrite button
$(document).on('click', '.btn_jsignature_rewrite', function() {
    var wrap_el = $(this).closest('.jsignature_wrap');
    var sign_el = wrap_el.find('.jsignature_signarea');
    var img_el = wrap_el.find('.jsignature_imgarea');
    var img_type_el = wrap_el.find('.jsignature_imgtype');
    var img_data_el = wrap_el.find('.jsignature_imgdata');
    var chk_el = wrap_el.find('input[type="checkbox"]');
    var unaltered = wrap_el.find('.jsignature_unaltered');
    // Reset
    sign_el.jSignature('reset');
    img_el.hide();
    sign_el.removeClass('hide_pad');
    $(this).hide();
    img_data_el.html('');
    img_type_el.val('');
    if(unaltered.length){
        unaltered.remove();
    }
    chk_el.prop('checked', false);
});

$(document).on('bindActionsToNewContent',function(){
    formatFields();
    updateOptionGroupSections();
    setPasswordValidationRules();
    setMirrorFromField();
    $('.addr_country').trigger('change');
});

function getCurSeqNumber(obj){
    var seq = obj.data('cur-seq-number');
    if(seq == undefined || isNaN(seq)){
        seq = parseIntNoNaN(obj.find('.form_row:first').siblings('.form_row:last').data('seq-number'));
    }
    return seq;
}

function setCurSeqNumber(obj, seq){
    obj.data('cur-seq-number', seq);
}

function addFormRow(btn){
    var addEvent = jQuery.Event('addFormRow');
    btn.trigger(addEvent);
    if(!addEvent.isDefaultPrevented()){
        var addToId = btn.data('add-to');
        var addTo = $('#' + addToId);
        var seq = getCurSeqNumber(addTo);
        var newSeq = seq+1;
        if(isNaN(newSeq)){
            newSeq = 0;
        }
        var addURL = btn.data('add-url');
        var addType = btn.data('add-type');
        var addTypeAttr = '';
        if(addType != undefined && addType != ''){
            addTypeAttr = '&typeRef=' + addType;
        }
        setCurSeqNumber(addTo, newSeq);
        
        var defaultTaxCodeId = '';
        var taxableElement = addTo.closest('.taxable_element');
        
        if (taxableElement != undefined) {
            var defaultTaxCodeIdField = taxableElement.find('.default_tax_code_id');
            if (defaultTaxCodeIdField != undefined) {
                defaultTaxCodeId = defaultTaxCodeIdField.val();
            }
        }
        if (defaultTaxCodeId == '') {
            defaultTaxCodeId = $('#field_default_tax_code_id').val();
        }
        if (defaultTaxCodeId != undefined && defaultTaxCodeId != '') {
            addURL += '&taxCodeId=' + defaultTaxCodeId;
        }
        jQuery.post(addURL + '&ajax=1&seq=' + newSeq + addTypeAttr, function (data) {
            var formRow = data.formRow;
            if (formRow != undefined && formRow != '') {
                var newFormRow = $(formRow).hide();
                var seqNum = newFormRow.data('seq-number');
                var eventData = {
                    seqNum : seqNum,
                    row : newFormRow,
                    btn : btn
                };
                var addingEvent = jQuery.Event('formRowAdding');
                addTo.trigger(addingEvent, eventData);
                addTo.append(newFormRow);
                $(newFormRow).slideDown(300, function(){
                    newContentLoaded();
                    var addedEvent = jQuery.Event('formRowAdded');
                    addTo.trigger(addedEvent, eventData);
                });
            }
            if(data.jqueryAction) {
                eval(data.jqueryAction);
            }
        });
    }
}

function removeFormRow(btn){
    var row = btn.closest('.form_row');
    var seqNum = row.data('seq-number');
    var data = {
        seqNum : seqNum,
        btn : btn
    };
    var removeEvent = jQuery.Event('removeFormRow');
    btn.trigger(removeEvent, data);
    if(!removeEvent.isDefaultPrevented()){
        var removeFrom = row.closest('.form_rows');
        row.slideUp(300, function(){
            row.remove();
            var removedEvent = jQuery.Event('formRowRemoved');
            removeFrom.trigger(removedEvent, data);
        });
    }
}

$(document).on('click', '.add_form_row', function(){
    addFormRow($(this));
});

$(document).on('click', '.remove_form_row', function(){
    removeFormRow($(this));
});

$(document).on('autocompleteSelectItem', '.gi_field_autocomplete', function(e, ui){
    if(ui.item.preventDefault){
        e.preventDefault();
    }
    if(ui.item.jqueryAction){
        eval(ui.item.jqueryAction);
    }
    if(ui.item.setGIModalAutocompField){
        var autocompFieldId = $(this).closest('.form_element').find('input.autocomp').attr('id');
        $("#gi_modal").data("autocomplete-field-id", autocompFieldId);
    }
    if(ui.item.pageNumber){
        var pageNumber = ui.item.pageNumber;
        var autocompField = $(this);
        var term = autocompField.val();
        autocompField.blur();
        setTimeout(function(){
            autocompField.val(term);
            autocompField.focus();
            autocompField.data('page-number', pageNumber);
            autocompField.autocomplete('search', term);
            console.log(pageNumber);
        }, 100);
    }
});

$(document).on('click', '.autocomp_dropdown', function(){
    var formElm = $(this).closest('.form_element');
    var autocompField = formElm.find('.gi_field_autocomplete');
    autocompField.focus();
    autocompField.autocomplete('search', '');
});

/***ZIPPABLE OPTION GROUPS***/
function closeOptionGroup(optionGroup){
    var label = optionGroup.find('.option_group_title');
    var options = optionGroup.find('label:not(.check_all)');
    var icon = label.find('.icon');
    var closeEvent = jQuery.Event('optionGroupClosed');
    optionGroup.trigger(closeEvent);
    if(!closeEvent.isDefaultPrevented()){
        options.slideUp();
        optionGroup.removeClass('open');
        icon.addClass('plus');
        icon.removeClass('minus');
    }
}

function openOptionGroup(optionGroup){
    var label = optionGroup.find('.option_group_title');
    var options = optionGroup.find('label:not(.check_all)');
    var icon = label.find('.icon');
    var openEvent = jQuery.Event('optionGroupOpened');
    optionGroup.trigger(openEvent);
    if(!openEvent.isDefaultPrevented()){
        options.slideDown();
        optionGroup.addClass('open');
        icon.addClass('minus');
        icon.removeClass('plus');
    }
}

$(document).on('click','.zippable .selected_count_title, .zippable .icon_wrap', function(e){
    var label = $(this);
    var optionGroup = label.closest('.option_group');
    if(optionGroup.is('.open')){
        closeOptionGroup(optionGroup);
    } else {
        openOptionGroup(optionGroup);
    }
});

function updateOptionGroupSelectedCount(optionGroup){
    var label = optionGroup.find('.option_group_title');
    var total = optionGroup.find('label:not(.check_all)').length;
    if(!label.find('.selected_count').length){
        var labelTitle = label.html();
        var checkbox = '<label class="check_all" title="Select All"><input class="gi_field_checkbox" type="checkbox" /></label>';
        label.html(checkbox + '<span class="selected_count_title">' + labelTitle + '</span> <span class="selected_count"><span class="num_selected"></span>/<span class="num_selectable">' + total + '</span></span> <span class="icon_wrap"><span class="icon plus gray"></span></span>');
    }
    var selectedCount = optionGroup.find('label:not(.check_all)').find(':checked').length;
    if(selectedCount != total){
        optionGroup.find('label.check_all input').prop('checked',false);
    } else {
        optionGroup.find('label.check_all input').prop('checked',true);
    }
    label.find('.num_selected').html(selectedCount);
}

$(document).on('change', '.zippable input', function(){
    if(!$(this).closest('.check_all').length){
        var optionGroup = $(this).closest('.option_group');
        updateOptionGroupSelectedCount(optionGroup);
    }
});

$(document).on('change', '.option_group_title .check_all input[type="checkbox"]', function(){
    var checkVal = false;
    if($(this).is(':checked')){
        checkVal = true;
    }
    var optionGroup = $(this).closest('.option_group');
    optionGroup.find('label:not(.check_all) input').each(function(){
        $(this).not('[readonly]').prop('checked',checkVal).trigger('change');
    });
});

function updateOptionGroupSections(){
    if($('.zippable .option_group').length){
        $('.zippable .option_group').each(function(){
            updateOptionGroupSelectedCount($(this));
            if($(this).is('.open')){
                openOptionGroup($(this));
            } else {
                closeOptionGroup($(this));
            }
        });
    }
}

function setPotentialValue(field, value){
    var wrap = field.closest('.form_element');
    if(wrap.find('.potential_value').length){
        wrap.find('.potential_value').html(value);
    } else {
        wrap.append('<span class="highlighted potential_value">' + value + '</span>');
    }
    var potentialValue = wrap.find('.potential_value');
    var wrapOffset = wrap.offset();
    var fieldOffset = field.offset();
    var offsetTop = fieldOffset.top - wrapOffset.top;
    potentialValue.css({
        top: offsetTop + 'px'
    });
}

function removePotentialValue(field){
    var wrap = field.closest('.form_element');
    wrap.find('.potential_value').remove();
}
/***END ZIPPABLE OPTION GROUPS***/
$(document).on('change', '.change_form_view', function (e) {
    e.preventDefault();
    var submitUrl = $('#gi_modal').data('url');
    var form = $('#gi_modal').find('form');
    var formData = false;
    if (window.FormData){
        formData = new FormData(form[0]);
    } else {
        formData = form.serialize();
    }
    elmStartLoading($('#gi_modal'));
    jQuery.ajax({
        type: 'POST',
        url: submitUrl + '&noSave=1',
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.mainContent != undefined) {
                giModalOpen(data.mainContent, submitUrl, data.modalClass, function(){
                    giModalAutoTitle($('#gi_modal'));
                    $('#gi_modal').trigger('ajaxContentLoaded');
                    //Add jquery callback action for modal form
                    if(data.jqueryCallbackAction) {
                        eval(data.jqueryCallbackAction);
                    }
                });            
            }
        },
        complete: function () {
            elmStopLoading($('#gi_modal'));
        },
    });
});

/** Detect form changes **/
$(document).on('change', 'form.detect_form_change :input', function (e) { //This detects select too
    //@todo causing too many errors commented out for now
//    $(this).closest('form').data('changed', true);
});
$(document).on('autocompleteSelectItem autocompleteRemovedItem fileUploaded fileRemoved formRowAdded removeFormRow', 'form.detect_form_change', function (e) {
    $(this).data('changed', true);
});
$(document).on('fileUploaded fileRemoved', 'body', function (e, data) {
    var uploaderName = data.uploaderName;
    if (uploaderName !== undefined) {
        var uploaderEl = $('#'+uploaderName+'_container');
        if (uploaderEl.length > 0) {
            $(uploaderEl).closest('form.detect_form_change').data('changed', true);
        }
    }
});
$(document).on('click', 'form.detect_form_change .form_change_item', function (e) {
    $(this).closest('form').data('changed', true);
});

$(document).on('click', '.check_for_form_change', function (e) {
    var form = $(this).closest('form');
    var url = $(this).attr('href');
    if(url == undefined){
        url = $(this).data('url');
    }
    if(form.data('changed')) {
        e.preventDefault();
        giModalConfirm('Save Changes?', 'It looks like you have been editing something. Would you like to save the changes?'
            , 'Yes', function(){
                var submitUrl = form.prop('action');
                if(submitUrl != undefined){
                    submitUrl += '&ajax=1';
                    var formData = false;
                    if (window.FormData){
                        formData = new FormData(form[0]);
                    } else {
                        formData = form.serialize();
                    }
                    startPageLoader();
                    jQuery.ajax({
                        type: 'POST',
                        url: submitUrl,
                        data: formData,
                        contentType: false,
                        processData: false
                    }).done(function(data) {
                        if(url != undefined){
                            window.location.href = url;
                        }
                    }).fail(function(data) {
                        console.log('ERROR:'+data);
                    }).always(function() {
                        stopPageLoader();
                    });
                }
                
            }, 'No', function(){
                if(url != undefined){
                    window.location.href = url;
                }
            });
    }
});
/** Detect form changes : end **/

/** Add/remove form view**/
function addRowHTML(targetRowId, rowData) {
    $('#' + targetRowId).find('.empty_row').remove();
    $('#' + targetRowId).append(rowData.html);
    newContentLoaded();
}

function replaceRowHTML(targetRowId, rowData) {
    $('#' + targetRowId).replaceWith(rowData.html);
    newContentLoaded();
}
function removeRowHTML(targetRowId) {
    $('#' + targetRowId).slideUp(300, function(){
        $(this).remove();
    });
}
/** Add/remove form view:end**/

function updateAddrRegionField(countryField){
    var regionField = countryField.closest('.addr_element').find('.addr_region');
    var curSelectedRegion = regionField.find('option:selected');
    var regionWrap = regionField.closest('.addr_region_wrap');
    var selection = countryField.find('option:selected');
    var forceRegion = selection.data('force-region');
    var countryName = selection.text();
    if(forceRegion != undefined && forceRegion){
        var curRegionGroup = curSelectedRegion.closest('optgroup');
        var regionGroups = regionField.children('optgroup');
        var refreshSelectric = false;
        for (var i = 0; i < regionGroups.length; i++) {
            var regionGroup = regionGroups.eq(i);
            if(regionGroup.attr('label') != countryName){
                regionGroup.hide();
                if(!regionGroup.is('.hide_group')){
                    refreshSelectric = true;
                }
                regionGroup.addClass('hide_group');
                var regionGroupChildren = regionGroup.children();
                for (var j = 0; j < regionGroupChildren.length; j++) {
                    var regionGroupChild = regionGroupChildren.eq(j);
                    regionGroupChild.attr('disabled', 'disabled');
                }
                regionGroup.appendTo(regionField);
            }
        }
        
        var selectedRegionGroup = regionField.children('optgroup[label="' + countryName + '"]');
        if(selectedRegionGroup.is('.hide_group')){
            refreshSelectric = true;
        }
        selectedRegionGroup.show();
        selectedRegionGroup.removeClass('hide_group');
        var selectedGroupChildren = selectedRegionGroup.children();
        for (var k = 0; k < selectedGroupChildren.length; k++) {
            var selectedGroupChild = selectedGroupChildren.eq(k);
            selectedGroupChild.removeAttr('disabled', 'disabled');
        }
        selectedRegionGroup.insertAfter(regionField.find('option.null_option'));
        
        regionWrap.find('.selectric-group').hide();
        regionWrap.find('.selectric-group-label:contains("' + countryName + '")').closest('.selectric-group').show();
        
        if((regionField.val() != 'NULL' && curRegionGroup.attr('label') != countryName)){
            regionField.val('NULL');
        }
        if(refreshSelectric){
            regionField.trigger('change').selectric('refresh');
        }
        regionWrap.removeClass('custom_entry');
    } else {
        regionWrap.addClass('custom_entry');
        regionField.val('NULL');
        regionField.trigger('change').selectric('refresh');
    }
}

$(document).on('change', '.addr_country', function(){
    updateAddrRegionField($(this));
});

//Move form view's step
function moveFormStep(form, stepToMove) {
    var formWrapEl = form.find('.form_body_wrap');
    var totalStep = formWrapEl.data('total-step');
    if (stepToMove > 0 && stepToMove <= totalStep) {
        form.find('input[name="next_step"]').val(stepToMove);
        startPageLoader();
        formWrapEl.data('step', stepToMove);
        formWrapEl.removeClass (function (index, className) {
            return (className.match (/(^|\s)step_\d+/g) || []).join(' ');
        });
        formWrapEl.removeClass('last_step');
        formWrapEl.addClass('step_'+stepToMove);
        if (stepToMove == totalStep) {
            formWrapEl.addClass('last_step');
        }
        form.find('.step_nav > ul > li').removeClass('current');
        form.find('.step_nav > ul > li').eq(parseInt(stepToMove)-1).addClass('current');
        setCurrentStepToFormBody(form, stepToMove);
        stopPageLoader();
        
        //Move slide
        if (form.find('.slide_step_form').length > 0) {
            moveSlide(form);
        }
    }
}
function setCurrentStepToFormBody(form, curStep) {
    $(form).find('.step_form_body_step').removeClass('current');
    $(form).find('.step_form_body_step[data-step='+curStep+']').addClass('current');
}
$(document).on('click', '.next_form_step', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var curStep = form.find('input[name="next_step"]').val();
    if (curStep === undefined || curStep == 0) {
        curStep = 1;
    }
    var stepToMove = parseInt(curStep) + 1;
    moveFormStep(form, stepToMove);
});
$(document).on('click', '.prev_form_step', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var curStep = form.find('input[name="next_step"]').val();
    var stepToMove = parseInt(curStep) - 1;
    moveFormStep(form, stepToMove);
});
$(document).on('click', '.move_form_step', function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var stepToMove = $(this).data('step');
    moveFormStep(form, stepToMove);
});
//Show only current step fiels in case of 'hide other step blocks' 
$('#form_step_view_wrap .hide_other_step_blocks').each(function(){
    var form = $(this).closest('form');
    var formWrapEl = form.find('.form_body_wrap');
    var curStep = formWrapEl.data('step');
    setCurrentStepToFormBody(form, curStep);
});

$(document).on('keydown', 'form.step_form input', function (e) {
    let code = e.keyCode || e.which;
    let form = $(this).closest('form');
    if (code == 13) {
        let formWrapEl = form.find('.form_body_wrap');
        e.preventDefault();
        let btnToClick = formWrapEl.find('.next_form_step');
        btnToClick.trigger('click');
        return false;
    }
});

function moveSlide(slideEl) {
    var curSlide = slideEl.find('.step_form_body_step.current');
    var preSlideCnt = curSlide.prevAll().length;
    var slideWidth = curSlide.width();
    var slideContainer = slideEl.find('.slide_container');
    $(slideContainer).css('left', - (slideWidth * preSlideCnt));
}
function renderRecaptcha(wrapEl) {
    //Render recaptcha
    var gRecaptchaEl = wrapEl.find('.g-recaptcha');
    if (gRecaptchaEl.length) {
        var recaptchaSiteKey = gRecaptchaEl.data('sitekey');
        if (recaptchaSiteKey !== undefined) {
            var gRecaptchaContainer = gRecaptchaEl[0];

            //Note: In order to render it, GI_View::setRecaptchaUsed(true) should be added in the view 
            //To avoid "reCAPTCHA has already been rendered in this element" error, check if gRecaptchaContainer has g-recaptcha-response class element, which means it's already rendered
            if ($(gRecaptchaContainer).find('.g-recaptcha-response').length === 0) {
                try {
                    grecaptcha.render(gRecaptchaContainer, {
                        sitekey: recaptchaSiteKey,
                        callback: function(response) {
                            console.log(response);
                        }
                    });
                } catch (err) {
                    console.log('form.js/renderRecaptcha function:'+err.message);
                }
            }
        }
    }
}
function calculateSlideStepSize(stepForm){
    let form = stepForm.closest('form');
    let formWrapEl = form.find('.form_body_wrap');
    let curStep = formWrapEl.data('step');
    setCurrentStepToFormBody(form, curStep);

    //Set initialize slide style
    let slideWidth = form.width();

    let allSlides = form.find('.step_form_body_step');
    let slideCnt = allSlides.length;

    let slideTotalWidth = slideWidth * slideCnt;
    let formBody = form.find('.step_form_body');
    let formBodyWrap = formBody.parent();
    formWrapEl.find('.step_form_body_step').addClass('slide').css('width', slideWidth);
    formBody.addClass('slide_container');
    formBody.css('width', slideTotalWidth);
    formBodyWrap.addClass('slide_container_outer');

    moveSlide(form);
}
function setSlideStepFormView() {
    //Set slide step form view
    let stepForms = $('#form_step_view_wrap .slide_step_form');
    for(let i=0; i<stepForms.length; i++){
        let stepForm = stepForms.eq(i);
        calculateSlideStepSize(stepForm);
        let form = stepForm.closest('form');
        
//        renderRecaptcha(form);
        findFieldErrorsInStepFormView(form);
    }
}
$(document).on('bindActionsToWindowResized', function(){
    let stepForms = $('#form_step_view_wrap .slide_step_form');
    for(let i=0; i<stepForms.length; i++){
        let stepForm = stepForms.eq(i);
        calculateSlideStepSize(stepForm);
    }
});

function findFieldErrorsInStepFormView(form) {
    //Move to the first step that has error fields
    if(form.find('.form_element.error').length){
        var errorStepBody = form.find('.form_element.error').first().closest('.step_form_body_step');
        var errorStep = errorStepBody.data('step');
        moveFormStep(form, errorStep);
    }
}
$(document).on('loadedInElement ajaxContentLoaded', '.ajaxed_contents', function(){
    if ($('#form_step_view_wrap .slide_step_form').length) {
        setSlideStepFormView();
    }
});

$(function(){
    setSlideStepFormView();
});

//If the save button is clicked without saving each nested form, submit each nested form and then redirect to the save process
$(document).on('formSubmitBtn', '.submit_nested_forms', function(e){
    var ajax = 0;
    var redirectUrl;
    var redirectTargetEl;
    if ($(this).data('redirect-url') !== undefined) {
        redirectUrl = $(this).data('redirect-url');
        ajax = getUrlParamValue(redirectUrl, 'ajax');
        if (ajax !== undefined && ajax == 1) {
            var targetId = getUrlParamValue(redirectUrl, 'targetId');
            if (targetId !== undefined) {
                redirectTargetEl = $('#'+targetId);
            } else {
                redirectTargetEl = $('#main');
            }
        }
    }
    var nestedFormArray = $('.nested_form_wrap form');
    if (nestedFormArray.length > 0) {
        e.preventDefault();
        var mainForm = $(this).closest('form');
        var hasError = false;
        $.when.apply($, nestedFormArray.map(function(i) {
            var nestedForm = $(this);
            var nestedFormSubmitUrl = nestedForm.prop('action');
            if(nestedFormSubmitUrl !== undefined){
                nestedFormSubmitUrl += '&ajax=1';
                var formData = false;
                if (window.FormData){
                    formData = new FormData(nestedForm[0]);
                } else {
                    formData = nestedForm.serialize();
                }
                elmStartLoading(nestedForm);
                return $.ajax({
                    type: 'POST',
                    url: nestedFormSubmitUrl,
                    data: formData,
                    contentType: false,
                    processData: false
                }).done(function(data) {
                    if (!data.success) {
                        hasError = true;
                    }
                    nestedForm.closest('.nested_form_wrap').replaceWith(data.mainContent);
                    newContentLoaded();
                }).fail(function(data) {
                    console.log('ERROR');
                    console.log(data);
                    hasError = true;
                }).always(function() {
                    elmStopLoading(nestedForm);
                });
            } else {
                return false;
            }
        })).done(function() {
            if (!hasError) {
                elmStartLoading(mainForm);
                if (redirectUrl !== undefined) {
                    if (ajax && redirectTargetEl !== undefined) {
                        giModalClose();
                        loadInElement(redirectUrl, undefined, redirectTargetEl);
                    } else {
                        window.location.href = redirectUrl;
                    }
                } else {
                    mainForm.submit();
                }
            } else {
                //Reposition nested form area
                var nestingId = $('#gi_modal').data('nesting-id');
                if (nestingId !== undefined) {
                    var clearPosition = 0;
                    positionNestedForm(nestingId, 'add', clearPosition);
                }
                return;
            }
        }).fail(function(data) {
            console.log('ERROR');
            console.log(data);
        });
    } else {
        if (redirectUrl !== undefined) {
            e.preventDefault();
            if (ajax && redirectTargetEl !== undefined) {
                loadInElement(redirectUrl, undefined, redirectTargetEl);
            } else {
                window.location.href = redirectUrl;
            }
        }
    }
});

$(document).on('mouseover', 'input, .selectric', function(){
    if($(this).attr('type') == 'password'){
        return;
    }
    var curTitle = $(this).attr('title');
    if($(this).is('.auto_title_on_hover') || curTitle == undefined || curTitle == ''){
        var newTitle = $(this).val();
        if($(this).is('.selectric')){
            newTitle = $(this).siblings('.selectric-hide-select').find('option:selected').html();
        }
        $(this).attr('title', newTitle);
        $(this).addClass('auto_title_on_hover');
    }
});

$(document).on('mouseover', '.form_element.read_only', function(){
    var readOnlyInput = $(this).find('input');
    if(!readOnlyInput.length){
        readOnlyInput = $(this).find('.selectric');
    }
    if(!readOnlyInput.length){
        return;
    }
    var curTitle = $(this).attr('title');
    if(readOnlyInput.is('.auto_title_on_hover') || curTitle == undefined || curTitle == ''){
        var newTitle = readOnlyInput.val();
        if(readOnlyInput.is('.selectric')){
            newTitle = readOnlyInput.siblings('.selectric-hide-select').find('option:selected').html();
        }
        $(this).attr('title', newTitle);
        readOnlyInput.addClass('auto_title_on_hover');
    }
});

$(document).on('click', '#form_step_view_wrap .tab_label', function(){
    var form = $(this).closest('form');
    var actionUrl = form.attr('action');
    var selectedTabIndex = $(this).data('tab-index');
    if (selectedTabIndex !== undefined) {
        actionUrl = replaceUrlParam(actionUrl, 'tab', selectedTabIndex);
        form.attr('action', actionUrl);
    }
});

$(document).on('input', '*', function(){
    $(this).trigger('keyup');
});

//Copy fields
$(document).on('change', '.copy_field_wrap .copy_from_field', function (e) {
    var copyFromWrap = $(this).closest('.copy_field_wrap');
    copyFromWrap.find('.copy_to_field').val($(this).val());
});

$(document).on('click', '.one_at_a_time', function(e){
    var ref = $(this).data('oaat-ref');
    $('.one_at_a_time[data-oaat-ref="' + ref + '"]').not(this).hide();
});

$(document).on('click', '.show_hidden_desc', function(){
    let hiddenDesc = $(this).closest('.form_element').find('.hidden_desc_wrap');
    hiddenDesc.fadeIn('fast');
});

$(document).on('click', '.hide_hidden_desc', function(){
    let hiddenDesc = $(this).closest('.form_element').find('.hidden_desc_wrap');
    hiddenDesc.fadeOut('fast');
});

function unhideOneAtAtime(ref){
    if(ref == undefined){
        $('.one_at_a_time').show();
    } else {
        $('.one_at_a_time[data-oaat-ref="' + ref + '"]').show();
    }
}

function getFieldHTML(fieldName, fieldType, fieldSettings){
    let url = 'index.php?controller=static&action=getFieldHTML&ajax=1&fieldName=' + fieldName + '&fieldType=' + fieldType;
    let postData = false;
    if (fieldSettings != undefined){
        postData = {
            fieldSettings : fieldSettings
        };
    }
    let placeholderName = fieldName + '_tmp_placeholder_field';
    let field = $('<span id="' + placeholderName + '">');
    
    $.ajax({
        type: 'POST',
        url: url,
        data: postData
    }).done(function(data) {
        let attempts = 0;
        let attemptToReplace = function(attempts){
            if($('#' + placeholderName).length){
                $('#' + placeholderName).replaceWith(data.mainContent);
                newContentLoaded();
            } else {
                attempts++;
                if(attempts == 10){
                    console.log('Could not find placeholder field [' + placeholderName + '].');
                }
                setTimeout(attemptToReplace(attempts), 500);
            }
        };
        attemptToReplace(attempts);
    }).fail(function(data) {
        console.log('ERROR');
        console.log(data);
    });
    return field[0].outerHTML;
}

function setPasswordValidationRules(){
    let passwordRuleSets = $('.validate_pass').not('.rules_set');
    for(let i=0; i<passwordRuleSets.length; i++){
        let passwordRuleSet = passwordRuleSets.eq(i);
        passwordRuleSet.hide();
        let fieldName = passwordRuleSet.data('field');
        let field = passwordRuleSet.closest('form').find('input[name="' + fieldName + '"]');
        let confFieldName = passwordRuleSet.data('conf-field');
        let confField = passwordRuleSet.closest('form').find('input[name="' + confFieldName + '"]');
        let rules = [];
        let ruleLis = passwordRuleSet.find('li');
        for(let r=0;r<ruleLis.length; r++){
            let ruleLi = ruleLis.eq(r);
            let name = ruleLi.data('rule');
            if(name !== undefined && name !== null && name !== ''){
                let rule = {
                    name : name,
                    val : ruleLi.data('val'),
                    li : ruleLi
                };
                rules.push(rule);
            }
        }
        field.data('rules', rules);
        field.addClass('pass_to_validate');
        field.data('conf-field', confField);
        field.data('rule-set', passwordRuleSet);
        if(confField.length){
            confField.data('pass-field', field);
            confField.addClass('pass_to_confirm');
        }
        passwordRuleSet.addClass('rules_set');
    }
}

function validatePassword(field){
    let ruleSet = field.data('rule-set');
    if(ruleSet === undefined || ruleSet === null || !ruleSet.length){
        return;
    }
    ruleSet.slideDown('fast');
    let rules = field.data('rules');
    let passVal = field.val();
    rules.forEach(function(rule){
        let name = rule.name;
        let val = parseIntNoNaN(rule.val);
        let li = rule.li;
        let valid = false;
        let regexExp = null;
        switch(name){
            case 'length':
                if(passVal.length >= val){
                    valid = true;
                }
                break;
            case 'upper':
                regexExp = /[A-Z]/;
                break;
            case 'lower':
                regexExp = /[a-z]/;
                break;
            case 'symbol':
                regexExp = /\W/;
                break;
            case 'number':
                regexExp = /[0-9]/;
                break;
            case 'whitespace':
                regexExp = /\s/;
                break;
            case 'match':
                let confField = field.data('conf-field');
                if(confField === undefined || confField === null || !confField.length){
                    li.hide();
                } else {
                    let confVal = confField.val();
                    if(val === 1 && confVal === passVal){
                        valid = true;
                    } else if(val === 0 && confVal !== passVal){
                        valid = true;
                    }
                }
                break;
        }
        if(regexExp !== null){
            let regexMatch = (regexExp.test(passVal));
            if(val && regexMatch){
                valid = true;
            } else if(!val && !regexMatch){
                valid = true;
            }
        }
        if(passVal === ''){
            valid = false;
        }
        if(valid){
            li.removeClass('red');
            li.addClass('green');
        } else {
            li.addClass('red');
            li.removeClass('green');
        }
    });
}

$(document).on('keyup', '.pass_to_validate', function(){
    validatePassword($(this));
});

$(document).on('keyup', '.pass_to_confirm', function(){
    let passField = $(this).data('pass-field');
    if(passField !== undefined && passField !== null && passField.length){
        validatePassword(passField);
    }
});

function setMirrorFromField(){
    let unLinkedMirrors = $('.mirror_from_field').not('.linked');
    for(let i=0; i<unLinkedMirrors.length; i++){
        let unLinkedMirror = unLinkedMirrors.eq(i);
        let fieldName = unLinkedMirror.data('field-name');
        let formId = unLinkedMirror.data('form-id');
        let form = null;
        if(formId === undefined || formId === null){
            form = unLinkedMirror.closest('form');
        } else {
            form = $('#' + formId);
        }
        if(form === undefined || form === null){
            continue;
        }
        let field = form.find('input[name="' + fieldName + '"]');
        let val = field.val();
        unLinkedMirror.html(val);
        field.on('keyup', function(){
            let val = $(this).val();
            unLinkedMirror.html(val);
        });
        unLinkedMirror.addClass('linked');
    }
}

$(document).on('click', '.toggle_otp', function(e){
    e.preventDefault();
    let form = $(this).closest('form');
    let otpToggler = form.find('.otp_toggler input');
    if(otpToggler.length){
        if(otpToggler.is(':checked')){
            otpToggler.prop('checked', false);
        } else {
            otpToggler.prop('checked', true);
        }
        otpToggler.trigger('change');
    }
});