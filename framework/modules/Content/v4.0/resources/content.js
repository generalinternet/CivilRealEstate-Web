/*
modules/content.js v4.0.1
*/
$(document).on('change', '.change_type', function(){
    var typeRef = $(this).val();
    var wrap = $(this).closest('.content_form_block_wrap');
    var block = $(this).closest('.content_form_block');
    var contentNumber = wrap.data('content-number');
    var parentNumber = wrap.data('parent-number');
    jQuery.post('index.php?controller=content&action=addInContent&ajax=1&type=' + typeRef + '&contentNumber=' + contentNumber + '&parentNumber=' + parentNumber, function (data) {
        //var parsedData = JSON.parse(data);
        var content = data.content;
        console.log(data);
        if(content != undefined && content != ''){
            block.replaceWith(content);
            newContentLoaded();
        }        
    });
});

function verifyChildrenLimits(addBtn){
    var typeRef = addBtn.data('type-ref');
    var min = addBtn.data('min-children');
    var max = addBtn.data('max-children');
    var contentInContent = addBtn.closest('.content_in_content_form');
    var curCount = contentInContent.find('> .content_form_block_wrap[data-type-ref="' + typeRef + '"]').length;
    if(max > 0 && curCount >= max){
        addBtn.addClass('disabled');
    } else {
        addBtn.removeClass('disabled');
    }
    
    if(min > 0 && curCount <= min){
        contentInContent.find('> .content_form_block_wrap[data-type-ref="' + typeRef + '"]').addClass('not_deletable');
    } else {
        contentInContent.find('> .content_form_block_wrap[data-type-ref="' + typeRef + '"]').removeClass('not_deletable');
    }
}

$(document).on('click', '.add_content:not(.disabled)', function(){
    var addBtn = $(this);
    var typeRef = $(this).data('type-ref');
    var typeRefAttr = '';
    if(typeRef != undefined && typeRef != ''){
        typeRefAttr = '&type=' + typeRef;
    }
    var contentParent = $(this).closest('.content_form_parent');
    var contentNumber = contentParent.data('cur-content-number') +1;
    var parentNumber = $(this).closest('.content_form_block_wrap').data('content-number');
    jQuery.post('index.php?controller=content&action=addInContent&ajax=1&inContent=1' + typeRefAttr + '&contentNumber=' + contentNumber + '&parentNumber=' + parentNumber, function (data) {
        //var parsedData = JSON.parse(data);
        var content = data.content;
        if(content != undefined && content != ''){
            //addBtn.before(content);
            addBtn.closest('.add_content_in_content').before(content);
            contentParent.data('cur-content-number', contentNumber);
            newContentLoaded();
            if(data.jqueryAction) {
                eval(data.jqueryAction);
            }
            verifyChildrenLimits(addBtn, typeRef);
        }        
    });
});

$(document).on('click', '.remove_content', function(){
    var wrap = $(this).closest('.content_form_block_wrap');
    var typeRef = wrap.data('type-ref');
    var parent = wrap.closest('.content_in_content_form');
    var addBtn = parent.find('> .add_content_in_content .add_content[data-type-ref="' + typeRef + '"]');
    giModalConfirm('Remove Content?', 'Are you sure you want to remove this content?', 'Yes', function(){
        wrap.slideUp(function(){
            wrap.remove();
            verifyChildrenLimits(addBtn);
        });
    }, 'No');
});

function makeContentSortable(){
    if($('.content_in_content_form.sortable').length){
        $('.content_in_content_form.sortable').sortable({
            handle: '.sort_handle',
            items: '> .content_form_block_wrap',
            opacity: 0.5,
            tolerance: 'pointer',
            placeholder: 'content_placeholder',
            cursor: 'grabbing',
            start: function(e, ui){
                $(this).closest('.content_in_content_form').find('.content_form_block_wrap').each(function(){
                    var contentWrap = $(this);
                    contentWrap.addClass('sorting_now');
                    var preview = contentWrap.find('.sort_preview');
                    if(!preview.length){
                        preview = $('<div class="sort_preview"></div>');
                        contentWrap.prepend(preview);
                    }
                    if(contentWrap.is('.text_wysiwyg')){
                        var previewHTML = contentWrap.find('.trumbowyg-editor').html();
                        if(previewHTML == ''){
                            previewHTML = '<p>No content added yet</p>';
                        }
                    } else if(contentWrap.is('.text_code')){
                        var previewHTML = contentWrap.find('textarea').val();
                        if(previewHTML == ''){
                            previewHTML = 'No code added yet';
                        }
                        previewHTML = '<pre><code>' + previewHTML + '</code></pre>';
                    }
                    preview.html(previewHTML);
                });
                
                $(this).sortable('refreshPositions');
                
            },
            stop: function(e, ui){
                $(this).closest('.content_in_content_form').find('.content_form_block_wrap').removeClass('sorting_now');
            }
        });
    }
}

$(function(){
    makeContentSortable();
    if($('.add_content').length){
        $('.add_content').each(function(){
            verifyChildrenLimits($(this));
        });
    }
});

$(document).on('click', '.content_slider_change_slide', function(e){
    e.preventDefault();
    var img = $(this).attr('href');
    var sliderWrap = $(this).closest('.content_slider_wrap');
    var slider = sliderWrap.find('.content_slider');
    slider.find('img').attr('src', img);
});
