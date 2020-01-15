/* 
 * Custom Javascript file
 */

 var blueBgPosArray = [];

 $(function(){ 
    $('.blue-bg').each(function( index ) {
        var top = $(this).offset().top;
        var bottom = top + $(this).outerHeight();
        blueBgPosArray.push([top, bottom]);
    });
    
    $(document).on('click', '.check_all', function(e) {
        e.preventDefault();
        var wrapEl = $(this).closest('.check-box-group');
        if ($(this).hasClass('all_checked')) {
            //uncheck all
            $(wrapEl).find('input[type="checkbox"]').prop('checked', false);
            $(this).removeClass('all_checked');
            $(this).html('<i class="fa fa-check-square-o" aria-hidden="true"></i> Check All');
        } else {
            //check all
            $(wrapEl).find('input[type="checkbox"]').prop('checked', true);
            $(this).addClass('all_checked');
            $(this).html('<i class="fa fa-square-o" aria-hidden="true"></i> Uncheck All');
        }
    });
    $(document).on('click', '.my_account_menu .sub_menu', function(e) {
        e.preventDefault();
        $(this).siblings('ul').slideToggle();
    });
    
    $(document).on('change', '.change_signup_nav select', function(e) {
        var navEl = $('.step_nav');
        var baseURL = navEl.data('url');
        var curStepEl = navEl.find('.form_step.current');
        var curStep = 1;
        if (curStepEl.length) {
            curStep = curStepEl.data('step');
        }
        var url = baseURL+'&investorType='+$(this).val()+'&step='+curStep+'&ajax=1';
        elmStartLoading(navEl, 'circle');
        jQuery.ajax({type: 'POST', url: url, success: function (data) {
            if (data.mainContent) {
                navEl.replaceWith(data.mainContent);
            }
        }});
        
    });
    $(document).on('autocompleteSelected', '.copy_to_display', function(e, data) {
        var displayBox = $(this).closest('.form_element').siblings('.display_box');
        
        if (displayBox.length && data.item.content !== undefined) {
            //Clear previouse content 
            displayBox.html('');
            $(this).closest('.autocomplete_box').find('.add_row_wrap').remove();
            //Add selected content
            displayBox.html(data.item.content).fadeIn();
            displayBox.after(data.item.addBtn);
        }
    });
    $(document).on('click', '.add_form_item_to_sortable_list', function(e) {
        if($('#form_item_lines.sortable').length){
            var wrapEl = $(this).closest('.add_row_wrap');
            var position = wrapEl.find('input[name="position"]').val();
            var inputPlaceholder = wrapEl.find('.input-placeholder');
            var eqIdx = 0;
            if (position !== undefined) {
                eqIdx = position - 1;
            }
            var insertEl = $('#form_item_lines li').eq(eqIdx);
            if (!$(this).hasClass('repeatable_item')) {
                var content = $(this).closest('.autocomplete_box').find('.display_box').html();
                $(inputPlaceholder).removeClass('input-placeholder').find('.sort_handle').append(content);
            }
            
            if ($(this).hasClass('repeatable_item')) {
                var addedPlaceholder = inputPlaceholder.clone();
            } else {
                var addedPlaceholder = inputPlaceholder;
            }
            $(addedPlaceholder).find('input').prop("disabled", false);
            if (insertEl.length == 0) {
                $(addedPlaceholder).appendTo($('#form_item_lines'));
            } else {
                $(addedPlaceholder).insertAfter(insertEl);
            }
            if (!$(this).hasClass('repeatable_item')) {
                wrapEl.slideUp();
                $(this).closest('.autocomplete_box').find('.display_box').fadeOut(function(){
                    $(this).html('');
                });
                $(this).closest('.autocomplete_box').find('.gi_field_autocomplete.copy_to_display').val('');
            }
            
        }
    });
    
    $(document).on('click', '#form_item_lines > li .delete_item', function(e) {
        $(this).closest('li.form_item_row').slideUp(function(){
            $(this).remove();
        });
    });
    
    $(document).on('click', '.slider_page_form_body .prev_btn', function(e) {
        var wrapEl = $(this).closest('.slider_page_form_body');
        var curEl = wrapEl.find('.slider_page.current');
        if (curEl.prev().length) {
            var prevEl = curEl.prev();
            curEl.removeClass('current');
            prevEl.addClass('current');
            adjustSliderPageStyleByStep(wrapEl);
        }
    });
    
    $(document).on('click', '.slider_page_form_body .next_btn', function(e) {
        var wrapEl = $(this).closest('.slider_page_form_body');
        var curEl = wrapEl.find('.slider_page.current');
        if (curEl.next().length) {
            var nextEl = curEl.next();
            curEl.removeClass('current');
            nextEl.addClass('current');
            adjustSliderPageStyleByStep(wrapEl);
        }
    });
    
    makeFormItemLinesSortable();
    moveSliderPage();
    $(document).on('actionsBoundToNewContent', function(e, data) {
        makeFormItemLinesSortable(); 
        moveSliderPage();
    });
    
    //Create ref from the title
    $(document).on('blur', '.sanitize_ref', function(e) {
        var formEl = $(this).closest('form');
        var refEl = formEl.find('.target_ref');
        if (refEl.length) {
            var ref = $.trim(refEl.val());
            if (ref == '') {
                var titleStr = $(this).val();
                jQuery.post('index.php?controller=content&action=sanitizeRef&ajax=1'
                    , { titleStr: titleStr}, function (data) {
                    if(data.success) {
                        refEl.val(data.unique_ref);
                    }
                });
            }
        }
    });
    $(document).on('blur', '.target_ref', function(e) {
        var refEl = $(this);
        var ref = $.trim($(this).val());
        if (ref == '') {
            var formEl = $(this).closest('form');
            var titleEl = formEl.find('.sanitize_ref');
            if (titleEl.length) {
                var titleStr = $.trim(titleEl.val());
                if (titleStr != '') {
                    jQuery.post('index.php?controller=content&action=sanitizeRef&ajax=1'
                        , { titleStr: titleStr}, function (data) {
                        if(data.success) {
                            refEl.val(data.unique_ref);
                        }
                    });
                }
            }
        }
    });
    
    /** Progress bar animation(Scrolling)**/
    $(document).scroll(function() {
        changeColours();
    });

    $(document).on('click', '#banner_scroller', function(){
        var refSelector = $(this).data('ref');
        if($(refSelector).length > 0){
            var topPosition = $(refSelector).offset().top;
            $('html, body').animate({ scrollTop: topPosition }, 800);
        }
    });
    
    $(document).on('click', '.accreditation_form_content .show_more', function(e){
        var formContentParent = $(this).parents('.accreditation_form_content').first();
        if(formContentParent.length == 0){
            return;
        }

        formContentParent.toggleClass('full_content');
    });

    if ($('.form_element.error').length) {
        var errorFormElm = $('.form_element.error').first();
        var formOffSet = errorFormElm.offset().top;
        var windownHeight = $(window).height();
        var errorFormHeight = errorFormElm.height();

        var animateOptions = {
            scrollTop: parseInt(formOffSet - (windownHeight/2) + (errorFormHeight/2))
        };

        if(errorFormElm.parents('#main_window_view_wrap').length != 0){
            $("#main_window_view_wrap").animate(animateOptions, 1000, 'swing');
        }else{
            $("html,body").animate(animateOptions, 1000, 'swing');
        }

    }
});

function makeFormItemLinesSortable(){
    if($('#form_item_lines.sortable').length){
        $('#form_item_lines.sortable').sortable({
            handle: '.sort_handle',
            helper: function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            stop: function(e, ui) {
                //
            }
        });
    }
}

function moveSliderPage(){
    var wrapEl = $('.slider_page_form_body');
    if($('.slider_page_wrap .form_element.error').length){
        var errorPage = $('.slider_page_wrap .form_element.error').first().closest('.slider_page');
        $('.slider_page_wrap .slider_page').removeClass('current');
        errorPage.addClass('current');
    }
    adjustSliderPageStyleByStep(wrapEl);
}
function adjustSliderPageStyleByStep(wrapEl){
    var lastPage = $(wrapEl).find('.slider_page_wrap .slider_page:last-child').data('page');

    var stepFormWrap =$("#step_form_wrap");
    
    //Prev/Next buttons
    var curEl = $(wrapEl).find('.slider_page.current');
    // if(curEl.length == 0){
    //     return;
    // }
    var curPage = curEl.data('page');
    if (curEl.prev('.slider_page').length) {
        wrapEl.find('.prev_btn').removeClass('hide');
        stepFormWrap.addClass('show_prev_btn');
    } else {
        wrapEl.find('.prev_btn').addClass('hide');
        stepFormWrap.removeClass('show_prev_btn');
    }
    if (curEl.next('.slider_page').length) {
        stepFormWrap.removeClass('show_complete_btn');
        wrapEl.find('.next_btn').removeClass('hide');
    } else {
        stepFormWrap.addClass('show_complete_btn');
        wrapEl.find('.next_btn').addClass('hide');
    }
    //Page number
    $(wrapEl).find('.form_title .page').text('.' + curEl.data('page'));
    
    var formEl = $(wrapEl).closest('form');
    var formSubmitBtnEl = formEl.find('.form-btns .submit_btn');
    if (lastPage == curPage) {
        //Show submit button only at the last page
        formSubmitBtnEl.fadeIn();
    } else {
        formSubmitBtnEl.fadeOut();
    }
    $(wrapEl).find('.form_title .page').text('.' + curEl.data('page'));

    $(document).trigger('afterGoToNextStep');
}

function changeColours() {
    var menuBtn = $('#menu_btn');
    if(menuBtn.is(':visible') && !menuBtn.hasClass('open') && blueBgPosArray.length > 0){
        var scrollTop = $(this).scrollTop();
        var windowHeight = $(window).height();
        //var scrollBottom = scrollTop + windowHeight;
        var inTheSection = false;
        var adjTopBtn = 30; //Bottom position to adjust
        var top;
        var bottom;
        for (var i=0; i< blueBgPosArray.length ; i++) {
            position = blueBgPosArray[i];
            top = position[0];
            bottom = position[1];
            if ((scrollTop - adjTopBtn) > top && (scrollTop - adjTopBtn) < bottom) {
                $('#menu_btn').addClass('over-blue-bg');
                inTheSection = true;
                break;
            }
        }
        if (!inTheSection) {
            $('#menu_btn').removeClass('over-blue-bg');
        }
    }
}


