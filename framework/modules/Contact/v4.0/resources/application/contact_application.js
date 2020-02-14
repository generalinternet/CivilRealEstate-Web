$(document).on('change','input[name="selected_card"]', function() {
    var val = $(this).val();
    var form = $(this).closest('form');
    var submitBtn = form.find(".submit_btn");
    if (val !== undefined && val === 'new') {
        submitBtn.find('.btn_text').text('Save New Card');
    } else {
        submitBtn.find('.btn_text').text('Use Selected Card');
    }
});
$(document).on('change', '.package_option input', function(e){
    $('.package_option').removeClass('selected');
    $(this).parents('.package_option').first().addClass('selected');
});

$(document).on('click', '.package_option', function(e){
    e.stopPropagation();
    $(this).find('input.gi_field_radio').trigger('click');
});

$(document).on('click','.package_option input.gi_field_radio', function(e){
    e.stopPropagation();
});