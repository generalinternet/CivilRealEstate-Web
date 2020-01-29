$(document).on('change','input[name="selected_card"]', function() {
    var val = $(this).val()
    var form = $(this).closest('form');
    var submitBtn = form.find(".submit_btn");
    if (val !== undefined && val === 'new') {
        submitBtn.text('Save New Card');
    } else {
        submitBtn.text('Use Selected Card');
    }
});
