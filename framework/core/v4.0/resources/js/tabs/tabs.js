/*
tabs.js v3.0.3
*/
function loadTabContent(tab, scrollTo){
    var tabWrap = tab.closest('.tabs_wrap');
    var tabIndex = tab.data('tab-index');
    var tabContent = tab.find('.tab_content');
    var tabLabel = tabWrap.closestChild('.tab_label[data-tab-index="' + tabIndex + '"]');
    if(tabContent.is('.empty')){
        elmStartLoading(tabContent);
      //  var contentURL = tabContent.attr('data-content-url')+ '&ajax=1';
        var contentURL = tabLabel.attr('data-content-url')+ '&ajax=1';
        jQuery.post(contentURL, function (data) {
            //var parsedData = JSON.parse(data);
            if (data.mainContent != undefined) {
                var contentLoaded = jQuery.Event('contentLoaded');
                tab.trigger(contentLoaded);
                tabContent.html(data.mainContent);
                tabContent.removeClass('empty');
                elmStopLoading(tabContent);
                newContentLoaded();
                if(scrollTo){
                    if (typeof scrollToTargetEl === 'function') {
                        //In case of main_content with scrollbar
                        scrollToTargetEl(tabLabel);
                    } else {
                        $('html, body').animate({
                            scrollTop: tabLabel.offset().top
                        }, 500, 'swing');
                    }
                }
            }
        });
    }
}

$(document).on('click', 'div.tab_label', function (e) {
    e.preventDefault();
    var loadTabEvent = jQuery.Event('loadTab');
    $(this).trigger(loadTabEvent);
    if(!loadTabEvent.isDefaultPrevented()){
        var tabWrap = $(this).closest('.tabs_wrap');
        var tabIndex = $(this).data('tab-index');
        var tabs = tabWrap.find('.tab[data-tab-index="' + tabIndex + '"]');
        var tab = null;
        for (var i=0; i<tabs.length; i++) {
            var checkTab = tabs.eq(i);
            var closestTabWrap = checkTab.closest('.tabs_wrap');
            if(closestTabWrap[0] === tabWrap[0]){
                tab = checkTab;
            }
        }
        if (tab != null && !tab.is('.current')) {
            tabWrap.find('.tab_label.current').removeClass('current');
            tabWrap.find('.tab.current').removeClass('current');
            tab.addClass('current');
            $(this).addClass('current');
            loadTabContent(tab, true);
        }
    }
});

$(document).on('loadTab', 'div.tab_label.disabled', function(e){
    e.preventDefault();
});

$(document).on('bindActionsToNewContent',function(){
    $('.tabs_wrap').not('.loaded').each(function(){
        var currentTab = $(this).find($('.tab.current'));
        if(currentTab.length){
            loadTabContent(currentTab);
        } else {
            currentTab = $(this).find($('.tab')).eq(0);
            if(currentTab.length){
                currentTab.addClass('current');
                loadTabContent(currentTab);
            }
        }
        $(this).addClass('loaded');
    });
});
