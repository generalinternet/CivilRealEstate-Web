/*
gi_error.js v2.0.0
*/
function closeGIErrors(){
    var errors = $('.gi_errors_wrap');
    errors.slideUp(300, function(){
        errors.remove();
    });
}

$(document).on('click', '.gi_error', function(){
    $(this).toggleClass('focus');
});

$(document).on('click', '.close_gi_error', function(){
    var error = $(this).closest('.gi_error');
    if($('.gi_error').length == 1){
        closeGIErrors();
    } else {
        error.slideUp(300, function(){
            error.remove();
        });
    }
});

$(document).on('click', '.close_gi_errors', function(){
    closeGIErrors();
});
