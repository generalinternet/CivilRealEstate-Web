/*
gi_modal.js v3.0.5
*/
var giModalCloseEnabled = true;

function giModalOpen(content, submitUrl, modalClass, callback) {
    var finalClass = 'gi_modal opened';
    if(modalClass != undefined){
        finalClass = finalClass + ' ' + modalClass;
    }
    
    var openModals = $('.gi_modal');
    var numOpenModals = openModals.length;
    for (var i = 0; i < numOpenModals; i++) {
        var openModal = openModals.eq(i);
        if(!openModal.is('#gi_modal')){
            giModalClose(openModal);
        }
    }
    
    if (!$('#gi_modal').length) {
        $('body').append('<div id="gi_modal" class="' + finalClass + '"></div>');

        if (!$('#gi_modal').hasClass('page_nested_modal')) {
            $('#gi_modal').draggable({
                handle: '.gi_modal_no_pad h2',
                stop: function(event, ui){
                    $(this).css({
                        height: '',
                        width: ''
                    });
                    var offsetVal = $(this).offset();
                    if(offsetVal.top<0){
                        $(this).css({
                            top: '1em',
                            marginTop: 0
                        });
                    }
                }
            });
        }
    } else {
        $('#gi_modal').removeClass();
        $('#gi_modal').addClass(finalClass);
    }
    $('body').addClass('gi_modal_open');
    /*
    if($('#gi_modal').html() != ''){
        giModalOpenSubModal();
    }
    */
    giModalBGOpen(function(){
        if ($('#gi_modal').hasClass('append_to_modal')) {
            $(content).hide().appendTo("#gi_modal").fadeIn();
        } else {
            $('#gi_modal').html(content);
        }
        
        if(callback!==undefined){
            callback();
        }
        giModalCenter($('#gi_modal'));
        $('#gi_modal').data('url', submitUrl);
        newContentLoaded();
        $('#gi_modal').fadeIn('fast',function(){
            $('textarea').trigger('autosize.resize');
            createJSignatures();
//            $(window).trigger('resize');
            if($(window).width() > 640){
                if($('#gi_modal input').length || $('#gi_modal textarea').length){
                    var focusField = $('#gi_modal').find('input:not(.gi_field_date):not(.selectric-input),textarea').filter(':visible:first');
                    if(!focusField.is('.autofocus_off') && !focusField.closest('.autofocus_off').length){
                        var focusFieldVal = focusField.val();
                        focusField.focus().val('').val(focusFieldVal);
                    }
                }
            }
        });
    });
}

function giModalBGOpen(callback){
    if (!$('#gi_modal_bg').length) {
        $('body').append('<div id="gi_modal_bg"></div>');
    }
    $('#gi_modal_bg').fadeIn('fast', function () {
        if(callback !== undefined){
            callback();
        }
    });
}

function giModalMove(targetId, callback){
    var modal = $('#gi_modal');
    var modalMoveEvent = jQuery.Event('modalMove');
    modal.trigger(modalMoveEvent);
    if(modalMoveEvent.isDefaultPrevented()){
        return;
    }
    if ($('#'+targetId).length && modal.length) {
        //.append runs synchronously
        $('#'+targetId).append(modal);
        modal.trigger('modalMoved');
        modal.addClass('modal_moved');
        $('#gi_modal_bg').addClass('modal_moved');
        if(callback !== undefined){
            callback();
        }
    } else {
        if ($('#'+targetId).length  == 0) {
            console.log('No target "'+targetId+'" element found!');
        }
        if (modal.length == 0) {
            console.log('No gi_modal found!');
        }
    }
}

function isGIModalOpen(){
    var modalHTML = $('#gi_modal').html();
    if(modalHTML != '' && modalHTML != undefined){
        return true;
    }
    return false;
}

function giModalCenter(modal){
    if (!modal.hasClass('page_nested_modal')) {
        modal.removeAttr("style");
        var giModalHeight = modal.height();
        if($('#open_tab_iframe').length){
            var iframeHeight = giModalHeight*0.9;
            $('#open_tab_iframe').height(iframeHeight+'px');
            var giModalHeight = modal.height();
        }
        var windowHeight = $(window).height();
        var topVal = '50%';
        var margTop = '0';
        if(giModalHeight >= windowHeight){
            topVal = '1em';
        } else {
            var windTop = $(window).scrollTop();
            margTop = -(giModalHeight / 2) + windTop;            
        }
        modal.css({
            top: topVal,
            marginTop: margTop
        });
    }
}

function giModalAutoTitle(giModal){
    if(!giModal.find('.auto_title').length){
        let titleElm = giModal.find('.main_head, h1').eq(0);
        let titleElmClass = titleElm.attr('class');
        let title = '';
        if(titleElm.length){
            title = titleElm.html();
        }
        let titleClass = '';
        if (titleElmClass === undefined) {
            titleClass = 'auto_title';
        } else {
            titleClass = titleElmClass + ' auto_title';
        }
        
        let titleVH = titleElm.closest('.view_header');
        titleElm.remove();
        if(titleVH.length){
            let titleVHBtns = titleVH.find('.right_btns');
            if(!titleVHBtns.length || titleVHBtns.html() == ''){
                titleVH.hide();
            }
        }
        let titleString = '<div class="content_padding gi_modal_no_pad"><h2 class="' + titleClass + '">' + title + '</h2><span class="custom_btn close_gi_modal" title="Close"><span class="icon_wrap"><span class="icon primary remove_sml"></span></span></span></div>';
        giModal.prepend(titleString);
    }
    if(!giModalCloseEnabled){
        $('.close_gi_modal').hide();
    }
}

function giModalContent(title, content) {
    var giModalString = '<div class="content_padding gi_modal_no_pad"><h2>' + title + '</h2><span class="custom_btn close_gi_modal" title="Close"><span class="icon_wrap"><span class="icon primary remove_sml"></span></span></span></div><div class="content_padding">';
    giModalString += content;
    giModalString += '</div>';
    return giModalString;
}

function giModalConfirm(title, message, yesBtnLabel, yesCallback, noBtnLabel, noCallback){
    if(isGIModalOpen()){
        if(yesCallback !== undefined){
            yesCallback();
        }
        return null;
    }
    
    var giModalString = '<div class="content_padding gi_modal_no_pad"><h2>' + title + '</h2><span class="custom_btn close_gi_modal" title="Close"><span class="icon_wrap"><span class="icon primary remove_sml"></span></span></span></div><div class="content_padding">';
    if(message.indexOf('<') !== -1){
        giModalString += message;
    } else {
        giModalString += '<p>' + message + '</p>';
    }
    giModalString += '<div class="wrap_btns">';
    if(yesBtnLabel == undefined || yesBtnLabel == ''){
        yesBtnLabel = 'Ok';
    }
    giModalString += '<span id="gi_modal_confirm_yes" class="other_btn">' + yesBtnLabel + '</span>';
    if(noBtnLabel != undefined && noBtnLabel != ''){
        giModalString += '<span id="gi_modal_confirm_no" class="other_btn gray">' + noBtnLabel + '</span>';
    }
    giModalString += '</div>';
    giModalString += '</div>';
    
    giModalOpen(giModalString, null, null, function(){
        $('#gi_modal_confirm_yes').click(function() {
            if(yesCallback !== undefined){
                yesCallback();
            }
            giModalClose();
        });
        $('#gi_modal_confirm_no').click(function() {
            if(noCallback !== undefined){
                noCallback();
            }
            giModalClose();
        });
    });
}

function giModalOpenAjaxContent(submitUrl, modalClass, callback){
    jQuery.post(prepareGIModalURL(submitUrl), function (data) {
        //var parsedData = JSON.parse(data);
        var showContent = true;
        if(data.jqueryAction) {
            eval(data.jqueryAction);
        }
        if(data.modalClass != undefined) {
            modalClass = data.modalClass;
        }
        if (showContent && data.mainContent != undefined) {
            giModalOpen(data.mainContent, submitUrl, modalClass, function(){
                giModalAutoTitle($('#gi_modal'));
                $('#gi_modal').trigger('ajaxContentLoaded');
                if(modalClass != undefined){
                    changeModalClass(modalClass, $('#gi_modal'));
                }
                if(callback !== undefined){                    
                    callback();
                }
                //Add jquery callback action for modal form
                if(data.jqueryCallbackAction) {
                    eval(data.jqueryCallbackAction);
                }
            });            
        } else if(showContent){
            //Error
            console.log(data);
            loadErrorInElement(null, 2500, 'modal');
        }
    });
}

function prepareGIModalURL(url){
    url = replaceUrlParam(url, 'ajax', 1);
    url = replaceUrlParam(url, 'modal', 1);
    return url;
}

function giModalOpenForm(button, event, modalClass, callback){
    event.preventDefault();
    event.stopPropagation();
    var url = button.attr('href');
    if(url == undefined){
        url = button.data('url');
    }
    var submitUrl = url;
    giModalOpenAjaxContent(submitUrl, modalClass, callback);
    
}

function giModalClose(modal) {
    if(modal == undefined){
        modal = $('#gi_modal');
    }
    giModalCloseEnabled = true;
    var modalCloseEvent = jQuery.Event('modalClose');
    modal.trigger(modalCloseEvent);
    if(!modalCloseEvent.isDefaultPrevented()){
        if(modal.is('#gi_modal')){
            $('#gi_modal').html('');
            $('#gi_modal').data('autocomplete-field', '');
            $('#gi_modal').data('autocomplete-field-id', '');
        }
        modal.fadeOut('fast', function(){
            modal.removeClass('opened');
            $('body').removeClass('gi_modal_open');
            var modalClosedEvent = jQuery.Event('modalClosed');
            modal.trigger(modalClosedEvent);
            if(!modalClosedEvent.isDefaultPrevented()){
                if(!$('.gi_modal.opened').length){
                    $('#gi_modal_bg').fadeOut('fast');
                }
            };
        });
    }
}

function changeModalClass(modalClass, modal){
    if(modal == undefined){
        modal = $('#gi_modal');
    }
    modal.removeClass('medium_sized');
    modal.removeClass('large_sized');
    modal.removeClass('full_sized');
    modal.addClass(modalClass);
}
//#gi_modal_bg, 
$(document).on('click tap', '.close_gi_modal', function () {
    if(giModalCloseEnabled){
        var modal = $(this).closest('.gi_modal.opened');
        if(!modal.length){
            modal = $('.gi_modal.opened');
        }
        if(modal.length){
            giModalClose(modal);
        } else {
            giModalClose();
        }
    }
});

$(document).on('formSubmitted', '#gi_modal form', function(e){
    if($(this).attr('target') == '_blank'){
        giModalClose();
    }
});

$(document).on('submit', '#gi_modal form', function (e) {
    let preventAjaxSubmit = false;
    if(e.isDefaultPrevented()){
        preventAjaxSubmit = true;
    }
    if($(this).attr('target') == '_blank'){
        return true;
    }
    e.preventDefault();
    e.stopPropagation(); //In case of a nested form 
//    var newWindow = window.open('', '_blank');
    var modalSubmitFormEvent = jQuery.Event('modalSubmitForm');
    $(this).trigger(modalSubmitFormEvent);
    if(preventAjaxSubmit){
        modalSubmitFormEvent.preventDefault();
    }
    if(!modalSubmitFormEvent.isDefaultPrevented()){
        var submitUrl = $('#gi_modal').data('url');
        var form = $(this);
        var formData = false;
        if (window.FormData){
            formData = new FormData(form[0]);
        } else {
            formData = form.serialize();
        }
        if (!$('#gi_modal').hasClass('page_nested_modal')) {
            startPageLoader();
            elmStartLoading($('#gi_modal'));
        } else {
            elmStartLoading(form.closest('.nested_form_wrap'));
        }
        jQuery.ajax({
            type: 'POST',
            url: prepareGIModalURL(submitUrl),
            data: formData,
            contentType: false,
            processData: false,
//            async: false,
            success: function (data) {
                var modalClass = data.modalClass;
                if (data.success) {
                    if(data.newUrl){
                        if(data.newUrl == 'refresh'){
                            location.reload();
                        } else {
                            //Ver.4 : default is ajax to 'main' element
                            if (data.newUrlRedirect) {
                                elmStartLoading($('#gi_modal'));
                                window.location.href = data.newUrl;
                            } else {
                                if(data.ajax){
                                    giModalOpenAjaxContent(data.newUrl, modalClass);
                                } else {
                                    var newUrlTargetId = defaultTargetId;
                                    if (data.newUrlTargetId) {
                                        newUrlTargetId = data.newUrlTargetId;
                                    }
                                    var addHistory = 1;
                                    loadInElementByTargetId(data.newUrl, newUrlTargetId, addHistory);
                                    giModalClose();
                                }
                                stopPageLoader();
                                elmStopLoading($('#gi_modal'));
                            }
                        }
                    } else {
                        var modalSubmitFormSuccessEvent = jQuery.Event('modalSubmitFormSuccess');
                        form.trigger(modalSubmitFormSuccessEvent);
                        if(!modalSubmitFormSuccessEvent.isDefaultPrevented()){
                            var autocompleteFieldId = $('#gi_modal').data('autocomplete-field-id');
                            var autocompleteField = $('#gi_modal').data('autocomplete-field');
                            if(autocompleteFieldId != undefined && autocompleteFieldId != ''){
                                var fieldObj = $('#' + autocompleteFieldId);
                                var acObj = fieldObj.closest('.form_element').find('.gi_field_autocomplete');
                            } else if(autocompleteField !== undefined && autocompleteField !== ''){
                                var autocompleteFieldId = '#field_' + autocompleteField;
                                
                                var acObj = $('#' + autocompleteField + '_autocomp');
                                var fieldObj = $('#field_' + autocompleteField);
                            }
                            
                            if(acObj !== undefined && fieldObj !== undefined){
                                var fillVal = data.autocompId;
                                if (acObj.data('multiple') !== false) {
                                    var curVal = fieldObj.val();
                                    if (curVal !== undefined && curVal !== '') {
                                        var carVals = curVal.split(',');
                                    } else {
                                        var carVals = [];
                                    }                    
                                    carVals.push(data.autocompId);
                                    fillVal = carVals.join(',');
                                }
                                acObj.trigger('autocompleteFill',{value : fillVal});
                                //giModalClose();
                            }
                            if ($('#gi_modal').hasClass('page_nested_modal')) {
                                //Remove nested form
                                removeNestedForm(form.closest('.nested_form_wrap'));
                            } else {
                                stopPageLoader();
                                elmStopLoading($('#gi_modal'));
                                if(modalClass != undefined){
                                    changeModalClass(modalClass, $('#gi_modal'));
                                }
                            }
                            giModalClose();
                        }
                    }
                } else {
                    stopPageLoader();
                    elmStopLoading($('#gi_modal'));
                    if ($('#gi_modal').hasClass('page_nested_modal')) {
                        var nestedFormWrap = form.closest('.nested_form_wrap');
                        nestedFormWrap.replaceWith(data.mainContent);
                        nestedFormWrap.removeClass('loading');
                    } else {
                        $('#gi_modal').html(data.mainContent);
                    }
                    
                    if(modalClass != undefined){
                        changeModalClass(modalClass, $('#gi_modal'));
                    }
                    giModalAutoTitle($('#gi_modal'));
                    newContentLoaded();
                    $('#gi_modal').trigger('ajaxContentLoaded');
                    form.trigger('modalSubmitFormFail');
                }
                if(data.jqueryAction) {
                    eval(data.jqueryAction);
                }
                
                //Add jquery callback action for modal form
                if(data.jqueryCallbackAction) {
                    eval(data.jqueryCallbackAction);
                }
        }});
    }
});

$(document).on('click', '.open_modal_form:not(.disabled)', function(e){
    var modalOpenFormEvent = jQuery.Event('modalOpenForm');
    $(this).trigger(modalOpenFormEvent);
    if(!modalOpenFormEvent.isDefaultPrevented()){
        var triggerEl = $(this);
        var modalClass = $(this).data('modal-class');
        var autocompleteField = $(this).data('autocomplete-field');
        var autocompleteFieldId = $(this).data('autocomplete-field-id');
        giModalOpenForm($(this), e, modalClass, function(){
            if(autocompleteField !== undefined){
                $('#gi_modal').data('autocomplete-field', autocompleteField);
                $('#gi_modal').data('autocomplete-field-id', autocompleteFieldId);
            } else {
                $('#gi_modal').data('autocomplete-field', '');
                $('#gi_modal').data('autocomplete-field-id', '');
            }
            
            //Nest the modal into a page
            if ($('#gi_modal').hasClass('page_nested_modal')) {
                var targetId = triggerEl.data('targetId');
                var nestType = triggerEl.data('nestType'); // append, replace
                positionNestedForm(targetId, nestType);
                giModalUpdateSeq(triggerEl);
            } else {
                clearNestedFormPosition();
            }
        });
    }
});


$(document).on('click', '.gi_modal_read_more', function(e){
    var modalReadMoreEvent = jQuery.Event('modalReadMore');
    $(this).trigger(modalReadMoreEvent);
    if(!modalReadMoreEvent.isDefaultPrevented()){
        var contentElm = $(this).find('.read_more_content');
        var modalTitle = contentElm.data('gi-modal-title');
        var modalClass = $(this).data('modal-class');
        modalClass += ' read_more_modal';
        var modalContent = giModalContent(modalTitle, contentElm.html());
        giModalOpen(modalContent, undefined, modalClass);
    }
});

$(document).on('click', '.show_content_in_modal', function(e){
    var showModalInContentEvent = jQuery.Event('showModalInContent');
    $(this).trigger(showModalInContentEvent);
    if(!showModalInContentEvent.isDefaultPrevented()){
        var contentElm = $(this).find('.modal_content');
        giModalAutoTitle(contentElm);
        giModalBGOpen(function(){
            contentElm.addClass('gi_modal');
            contentElm.fadeIn('fast', function(){
                contentElm.addClass('opened');
            });
        });
    }
});

$(document).on('showModalInContent', '.show_content_in_modal.disabled', function(e){
    e.preventDefault();
});

$(document).on('click', '.show_content_in_modal .gi_modal', function(e){
    e.stopPropagation();
});

function giModalOpenSubModal(){
    /*
    var curModalHTML = $('#gi_modal').html();
    var curModalData = $('#gi_modal').data();
    var tmpModal = $('#tmp_gi_modal');
    if(!tmpModal.length){
        var tmpModal = $('<div id="tmp_gi_modal"></div>');
    }
    tmpModal.html(curModalHTML);
    tmpModal.data(curModalData);
    $('body').append(tmpModal);
    */
}

function positionNestedForm(targetId, nestType, clearPosition) {
    var targetEl = $('#'+targetId);
    if (targetEl !== undefined) {
        if (clearPosition === undefined || clearPosition === 1) {
            clearNestedFormPosition();
        }
        
        $('#gi_modal').data('nesting-id', targetId);
        if (nestType !== undefined && nestType === 'replace') {
            //Edit
            targetEl.addClass('nest_hidden_element');
        }
        var countOfNestedForms = $('#gi_modal').find('.nested_form_wrap').length;
        if (countOfNestedForms > 0) {
            var offset = targetEl.offset();
            var offsetTop = 0;
            var offsetLeft = 0;
            var baseTop = 0;
            var baseLeft = 0;
            if (offset !== undefined) {
                if ($('#main_window_view_wrap').length) {
                    var offsetMainWindow = $('#main_window_view_wrap').children().first().offset();
                    //If nested form is inside of "main_window_view_wrap", adjust position with main_window_view_wrap element
                    baseTop = offsetMainWindow.top;
                    baseLeft = offsetMainWindow.left;
                }
                offsetTop = offset.top  - baseTop;
                offsetLeft = offset.left - baseLeft;
            }
            
            var targetHeight = targetEl.innerHeight();
            var targetWidth = targetEl.innerWidth();
            var modalHeight = $('#gi_modal').innerHeight();
            var topAdjust = 0;
            if (targetEl.find('.form_group_wrap').length) {
                topAdjust = 15; //Add some adjustment if there are rows in the target
            }
            var leftAdjust = -7;
            if ($(document).height() > $(window).height()) {
                leftAdjust = 0;
            }
            
            var marginBottomAdjust = 30;
            $('#gi_modal').css('margin-top', '0');
            $('#gi_modal').css('margin-left', '0');
            $('#gi_modal').css('top', (parseInt(offsetTop) + parseInt(targetHeight) + topAdjust) + 'px');
            $('#gi_modal').css('left', (parseInt(offsetLeft) + leftAdjust) + 'px');
            $('#gi_modal').css('width', targetWidth + 'px');

            targetEl.css('margin-bottom', (modalHeight + marginBottomAdjust) + 'px');
            targetEl.addClass('has_nested_form');
        }
    }
}
//Show hidden detail view and remove nested form's place holder
function clearNestedFormPosition() {
    $('.nest_hidden_element').removeClass('nest_hidden_element');
    $('.has_nested_form').removeClass('has_nested_form').css('margin-bottom', '');
}
function removeNestedForm(form) {
    if (form !== undefined) {
        $(form).fadeOut('fast', function(){
            $(this).remove();
            //Reposition nesting space
            repositionNestedForm();
        });
    } else {
        //Delete all nested forms
        $('.nested_form_wrap').remove();
        giModalClose();
        clearNestedFormPosition();
    }
    
}
function giModalUpdateSeq(triggerEl) {
    //Update seq number
    var triggerUrl = triggerEl.attr('href');
    if (triggerUrl === undefined) {
        triggerUrl = triggerEl.data('url');
    }
    if (triggerUrl !== undefined) {
        var curSeq = $('#gi_modal').data('seq');
        var nextSeq;
        if (curSeq === undefined) {
            var formLen = $('#gi_modal').find('form').length;
            nextSeq = formLen+1;
        } else {
            nextSeq = parseInt(curSeq)+1;
        }
        
        triggerUrl = replaceUrlParam(triggerUrl, 'seq', nextSeq);
        triggerEl.attr('href', triggerUrl);
        $('#gi_modal').data('seq', nextSeq);
    }
}
function repositionNestedForm(clearPosition) {
    var modal = $('#gi_modal');
    var nestingId = modal.data('nesting-id');
    if (nestingId !== undefined) {
        positionNestedForm(nestingId, null, clearPosition);
    }
}
//Close the add form
$(document).on('click', '.close_nested_form', function(e){
    //Close a nested form
    removeNestedForm($(this).closest('.nested_form_wrap'));
    
});
