
/** Specific-use functions that must be customized, depending on the field of the row*/

function updateRowSubtotal(row) {
    var qtyField = $(row).find('.taxable_row_qty');
    var qty = parseFloatNoNaN(qtyField.val());
    var pricePerField = $(row).find('.taxable_row_price_per');
    var pricePer = parseFloatNoNaN(pricePerField.val());
    var subtotal = qty * pricePer;
    var subtotalField = $(row).find('.taxable_row_subtotal');
    subtotalField.val(subtotal.toFixed(2));
    updateRowsTaxSumsAndTotals();
}

$(document).on('keyup', '.taxable_row_qty', function() {
    var row = $(this).closest('.taxable_row');
    updateRowSubtotal(row);
});

$(document).on('keyup', '.taxable_row_price_per', function() {
    var row = $(this).closest('.taxable_row');
    updateRowSubtotal(row);
});