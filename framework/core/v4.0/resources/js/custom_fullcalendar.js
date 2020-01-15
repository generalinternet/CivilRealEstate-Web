/* 
 * Custom Javascript file for fullcalendar
 * Ref: https://fullcalendar.io/docs/
 */

/**
 * Tooltip
 */
function initializeTooltipster(tooltip_el, target_url, event) {
    if ($.isFunction($.fn.tooltipster)) {
        //Binding tooltips
        $(tooltip_el).tooltipster({
            animation: 'fade',
            theme: 'tooltipster-shadow',
            interactive: true,
            side:  ['right', 'left', 'top', 'bottom'],
            trigger: 'custom',
            triggerOpen: {
                click: true,
                tap: true,
                mouseenter: true,
            },
            triggerClose: {
                click: false,
                tap: true,
                mouseleave: true,
                touchleave: true,
                originClick: false,
            },
            contentCloning: true,
            content: 'Loading...',
            zIndex: 8400, //Lower than GI_modal
            functionReady: function(instance, helper){
                getTooltipContent(tooltip_el, target_url, event);
            },
        });
    }
}

/** Open tooltip **/
var tooltipScroll;

function getTooltipURL(event) {
    var target_url = '';
    if (event.view_url != undefined) {
        target_url += event.view_url;
    }
    if (event.start != undefined) {
        target_url += '&start=' + event.start.format();
    }
    if (event.end != undefined) {
        target_url += '&end=' + event.end.format();
    }
    target_url += '&ajax=1';
    return target_url;
}
function getTooltipContent(tooltip_el, target_url, event) {
    $.ajax({
        url: target_url,
        success: function (data) {
            // Update tooltip's content
            $('#tooltip_content').html(data.mainContent);
            if ($.isFunction($.fn.tooltipster)) {
                if ($(tooltip_el).hasClass('tooltipstered')) {
                    $(tooltip_el).tooltipster('content', $('#tooltip_content'));
                    $('.tooltipster-arrow-border').css('border-right-color', event.color);
                }
            }
        },
        error: function (e) {
            console.log(e);
        }
    });
}

$(document).ready(function() {
    //Advanced open/close event in the tooltip -> resize the height of tooltip scroll
    $(document).on('advancedOpened', '.tooltipster-content', function(e){
        if(typeof tooltipScroll != 'undefined') {
            setTimeout(function(){  // Set timeout because the dalay of sildeup/down
                tooltipScroll.refresh();
            }, 1000);
        }
    });
    $(document).on('advancedClosed', '.tooltipster-content', function(e){
        if(typeof tooltipScroll != 'undefined') {
            setTimeout(function(){ // Set timeout because the dalay of sildeup/down
                tooltipScroll.refresh();
            }, 1000);
        }
    });
    //Close tooltip by x button
    $(document).on('click', '.close_tooltip', function(e){
        $('.event_tooltip').tooltipster('hide');
    });
});

/**Tootip:end **/

