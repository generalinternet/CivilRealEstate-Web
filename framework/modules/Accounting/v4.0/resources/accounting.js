$(function () {
    initAccountingScripts();
});

function initAccountingScripts() {
    initTaxRates();
    updateAllTaxableElements();
}
function initTaxRates() {
    var taxables = $('.taxable_element');
    for (var i = 0; i < taxables.length; i++) {
        var taxable = taxables.eq(i);
        var taxRates = [];
        var taxTotalRows = taxable.find('.tax_total_row');
        for (var j = 0; j < taxTotalRows.length; j++) {
            var taxTotalRow = taxTotalRows.eq(j);
            var rateId = taxTotalRow.data('rate-id');
            var rate = taxTotalRow.data('rate');
            taxRates[rateId] = rate;
        }
        taxable.data('tax-rates', taxRates);
    }
}

function updateAllTaxableElements() {
    $('.taxable_element').each(function () {
        updateRowsTaxSumsAndTotals($(this), true);
    });
}

$(document).on('change', '.tax_code_select', function () {
    var row = $(this).closest('.taxable_row');
    if (row != undefined) {
        var taxableElement = row.closest('.taxable_element');
        if (taxableElement != undefined) {
            updateRowsTaxSumsAndTotals(taxableElement);
        }
    }
});

$(document).on('keyup', '.taxable_row_subtotal', function () {
    rowSubtotalUpdated($(this), true);
});

$(document).on('modalClose', '.qb_modal', function(){
    location.reload();
}); 

function rowSubtotalUpdated(field, triggerEvent, updateRowTotal) {
    var row = field.closest('.taxable_row');
    if (row != undefined) {
        var taxableElement = $(row).closest('.taxable_element');
        updateRowsTaxSumsAndTotals(taxableElement, updateRowTotal);
        if (triggerEvent != undefined && triggerEvent) {
            var subtotalUpdateEvent = jQuery.Event('subtotalUpdate');
            row.trigger(subtotalUpdateEvent);
            if (!subtotalUpdateEvent.isDefaultPrevented()) {

            }
        }
    }
}

function updateRowTaxSumsAndTotal(row, updateRowTotal) {
    var taxableElement = row.closest('.taxable_element');
    var subtotal = taxableElement.data('subtotal');
    var taxTotals = taxableElement.data('tax-totals');
    var total = taxableElement.data('total');
    var taxRates = taxableElement.data('tax-rates');
    var rowTaxSum = 0;
    var subtotalField = row.find('.taxable_row_subtotal');
    var rowSubtotal = parseFloatNoNaN(subtotalField.val());
    subtotal += rowSubtotal;
    var totalField = row.find('.taxable_row_total');
    var selectedCode = row.find('.tax_code_select option:selected');
    var ratesString = String(selectedCode.data('rates'));
    if (ratesString !== 'undefined' && ratesString !== '') {
        var ratesArray = ratesString.split(',');
        var numOfRates = ratesArray.length;
        
        for (i=0;i<numOfRates;i++) {
            var rateId = ratesArray[i];
            var rate = taxRates[rateId];
            var taxAmount = parseFloatNoNaN((rate * rowSubtotal) / 100);
          //  var taxAmountString = taxAmount.toFixed(2);
            var taxAmountString = taxAmount.toFixed(7);
            taxAmount = parseFloatNoNaN(taxAmountString);
            rowTaxSum += taxAmount;
            var oldTotal = parseFloatNoNaN(taxTotals[rateId]);
            taxTotals[rateId] = oldTotal + taxAmount;
        }
    }
    
    var rowTotal = rowTaxSum + rowSubtotal;
    
    if (updateRowTotal == undefined || updateRowTotal) {
        if (totalField.hasClass('gi_field_money_rate')) {
            totalField.val(rowTotal.toFixed(7));
        } else {
            totalField.val(rowTotal.toFixed(2));
        }
    }
    
    total += rowTotal;
   
    taxableElement.data('subtotal', subtotal);
    taxableElement.data('tax-totals', taxTotals);
    taxableElement.data('total', total);
}

function updateRowsTaxSumsAndTotals(taxableElement, updateRowTotal) {
    var taxTotals = [];
    var subtotal = 0;
    var total = 0;
    taxableElement.data('tax-totals', taxTotals);
    taxableElement.data('subtotal', subtotal);
    taxableElement.data('total', total);
    taxableElement.find('.taxable_row').each(function () {
        updateRowTaxSumsAndTotal($(this), updateRowTotal);
    });
    updateSubtotalTableRow(taxableElement);
    updateTaxTotalsTableRows(taxableElement);
    updateTotalTableRow(taxableElement);
    var taxableElementUpdateEvent = jQuery.Event('taxableElementUpdate');
    taxableElement.trigger(taxableElementUpdateEvent);
}

function reverseUpdateRowSubtotal(row) {
    var taxableElement = $(row).closest('.taxable_element');
    var taxRates = taxableElement.data('tax-rates');
    var totalField = $(row).find('.taxable_row_total');
    var subtotalField = $(row).find('.taxable_row_subtotal');
    var selectedCode = row.find('.tax_code_select option:selected');
    var ratesString = String(selectedCode.data('rates'));
    var combinedRate = 0;
    var total = totalField.val();
    if (ratesString !== 'undefined' && ratesString !== '') {
        var ratesArray = ratesString.split(',');
        var numOfRates = ratesArray.length;
        for (i = 0; i < numOfRates; i++) {
            var rateId = ratesArray[i];
            var rate = taxRates[rateId];
            combinedRate += rate;
        }
        if (combinedRate > 0) {
            var newSubtotal = total / (1 + (combinedRate / 100));
            if (subtotalField.hasClass('gi_field_money_rate')) {
                newSubtotal = newSubtotal.toFixed(7);
            } else {
                newSubtotal = newSubtotal.toFixed(2);
            }
            subtotalField.val(newSubtotal);
            rowSubtotalUpdated(subtotalField, true, false);
        }
    } else {
        subtotalField.val(total);
        rowSubtotalUpdated(subtotalField, true, false);
    }
}

function updateTaxTotalsTableRows(taxableElement) {
    var taxTotals = taxableElement.data('tax-totals');
    $(taxableElement).find('.tax_total_row').each(function () {
        var rateId = $(this).data('rate-id');
        var taxTotal = parseFloatNoNaN(taxTotals[rateId]);
        var taxTotalCol = $(this).find('.tax_total_col');
        $(taxTotalCol).html('$' + makeItMoneyForDisplay(taxTotal));
        if (taxTotal != '0') {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function updateSubtotalTableRow(taxableElement) {
    var subtotal = taxableElement.data('subtotal');
    $(taxableElement).find('.subtotal_row').each(function () {
        var valueCol = $(this).find('.subtotal_value_col');
        $(valueCol).html('$' + makeItMoneyForDisplay(subtotal));
    });
}

function updateTotalTableRow(taxableElement) {
    var total = taxableElement.data('total');
    $(taxableElement).find('.total_row').each(function () {
        var valueCol = $(this).find('.total_value_col');
        $(valueCol).html('$' + makeItMoneyForDisplay(total));
    });
}

function updateSelectedTaxCodes(taxableElement) {
    var defaultTaxCodeIdField = taxableElement.find('.default_tax_code_id');
    var defaultTaxCodeId = defaultTaxCodeIdField.val();
    if (defaultTaxCodeId != undefined && defaultTaxCodeId != '') {
        taxableElement.find('.tax_code_select').each(function () {
            var currentVal = $(this).val();
            if (currentVal == undefined || currentVal == 'NULL' || currentVal == 'null') {
                $(this).val(defaultTaxCodeId).trigger('change').selectric('refresh');
            }
        });
        taxableElement.find('.tax_code_check').each(function () {
            if (defaultTaxCodeId) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
}

$(document).on('keyup', '.taxable_row_total', function() {
    var row = $(this).closest('.taxable_row');
    if (row !== 'undefined') {
        reverseUpdateRowSubtotal(row);
        var taxableElement = row.closest('.taxable_element');
        updateRowsTaxSumsAndTotals(taxableElement, false);
    }
});

$(document).on('change', '#field_report_type_select', function () {
    var val = $(this).val();
    $('.report_section').hide();
    $('#' + val).show();
    visibleContentUpdate();
    var reportsBar = $('#report_bar_main');
    reportsBar.data('active-type', val);
});

$(document).on('click', '#report_export_csv', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var reportsBar = $('#report_bar_main');
    var type = reportsBar.data('active-type');
    $(location).attr('href', url+'&type='+type);
});

$(document).on('click', '#report_change_dates', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var reportsBar = $('#report_bar_main');
    var type = reportsBar.data('active-type');
    $(location).attr('href', url+'&type='+type);
});
