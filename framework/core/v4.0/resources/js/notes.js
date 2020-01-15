$(document).on('click', '.note_wrap', function (event) {
    $(this).addClass('open');
});

$(document).on('click', '#load_more_notes', function (event) {
    var url = $(this).data('url');
    url += '&ajax=1';
    var loadMoreButton = $('#load_more_notes');
    elmStartLoading(loadMoreButton);
    jQuery.post(url, function (data) {
        if (data.mainContent) {
            var html = data.mainContent;
            loadMoreButton.replaceWith(html);
        }
        elmStopLoading(loadMoreButton);
    });
});


//function postNote(noteHTML) {
//    var thread = $(document).find('.notes_thread');
//    thread.prepend(noteHTML);
//}

function reloadNotesThread() {
    var element = $('#notes_thread');
    var url = element.data('url');
    url += '&ajax=1';
    var elementClass = element.attr('class');
    loadInElement(url, undefined, element, elementClass);
}

function swapNote(noteHTML, targetId) {
    var note = $('#note_id_'+targetId);
    note.replaceWith(noteHTML);
}
