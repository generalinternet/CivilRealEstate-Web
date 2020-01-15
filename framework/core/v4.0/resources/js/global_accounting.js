function updateTaxFieldPreview(field){
    var fieldName = field.attr('name');
    var fieldPreviewElm = field.closest('.show_content_in_modal').find('.modal_preview .tax_string');
    if($('[name="' + fieldName + '"]:checked').length){
        fieldPreviewElm.html('');
        $('[name="' + fieldName + '"]:checked').each(function() {
            var curString = fieldPreviewElm.html();
            if(curString != ''){
                curString += ', ';
            }
            var label = $(this).siblings('.checkbox_label').html();
            curString += label;
            fieldPreviewElm.html(curString);
        });
    } else {
        fieldPreviewElm.html('No taxes');
    }
}

$(document).on('change', '.update_tax_modal_preview', function(e){
    updateTaxFieldPreview($(this));
});

var curTaxRegionCode = '';
var curTaxCountryCode = '';
var curTaxRegions = [];

function updateTaxOptions(regionCode, countryCode, regionId){
    var ajaxURL = 'index.php?&controller=accounting&action=getTaxRegionData&ajax=1';
    if(regionId != undefined){
        ajaxURL += '&regionId=' + regionId;
    } else {
        if(regionCode == undefined){
            regionCode = curTaxRegionCode;
        }
        ajaxURL += '&regionCode=' + regionCode;
        if(countryCode == undefined){
            countryCode = curTaxCountryCode;
        }
        ajaxURL += '&countryCode=' + countryCode;
    }
    
    jQuery.post(ajaxURL, function(data) {
        curTaxRegionCode = data.regionCode;
        curTaxCountryCode = data.countryCode;
        curTaxRegions = data.taxRegions;
        
        $('.tax_options').each(function(){
            if(!$(this).is('.submitted')){
                updateTaxOptionObj($(this));
            } else {
                $(this).removeClass('submitted');
            }
        });
        
        $('tbody.tax_totals').each(function(){
            if(curTaxRegions == undefined){
                $(this).html('<tr></tr>');
            } else {
                $(this).html('');
                for(var key in curTaxRegions){
                    var taxRegion = curTaxRegions[key];
                    var taxRegionId = taxRegion.taxRegionId;
                    var taxRow = $('<tr></tr>');
                    taxRow.append('<th>' + taxRegion.titleWithRate + '</th>');
                    taxRow.append('<td id="tax_' + taxRegionId + '" data-tax-region-id="' + taxRegionId + '" data-tax-rate="' + taxRegion.rate + '" class="tax_total" ></td>');
                    $(this).append(taxRow);
                }
            }
            var taxOptionTotalsUpdatedEvent = jQuery.Event('taxOptionTotalsUpdated');
            $(this).trigger(taxOptionTotalsUpdatedEvent);
        });
        
        newContentLoaded();
    });
}

function updateTaxOptionObj(taxOptionObj){
    var firstField = taxOptionObj.find('.gi_field_checkbox:first');
    var fieldName = firstField.attr('name');
    var fieldClass = firstField.attr('class');
    var selectedTaxIds = $(this).data('selectedTaxIds');
    if(selectedTaxIds == undefined){
        selectedTaxIds = [];
    }
    var selectedTaxRegionIds = taxOptionObj.data('selectedTaxRegionIds');
    if(selectedTaxRegionIds == undefined){
        selectedTaxRegionIds = [];
    }
    $("input[name='" + fieldName + "']").map(function(){
        var selectedTaxRegionId = $(this).val();
        var selectedTaxId = $(this).data('tax-id');
        if($(this).is(':checked')){
            if(selectedTaxId != undefined || jQuery.inArray(selectedTaxId, selectedTaxIds) === -1){
                selectedTaxIds.push(selectedTaxId);
            }
            if(jQuery.inArray(selectedTaxRegionId, selectedTaxRegionIds) === -1){
                selectedTaxRegionIds.push(selectedTaxRegionId);
            }
        }
    });

    taxOptionObj.data('selectedTaxIds', selectedTaxIds);
    taxOptionObj.data('selectedTaxRegionIds', selectedTaxRegionIds);

    var taxOptionsWrap = $('<div class="options_wrap"></div>');
    if(curTaxRegions == undefined){
        taxOptionsWrap.append('<label><input class="' + fieldClass + '" name="' + fieldName + '" type="hidden" /><span class="checkbox_label">No taxes available.</span></label></label>');
    } else {
        for(var key in curTaxRegions){
            var taxRegion = curTaxRegions[key];
            var taxRegionId = taxRegion.taxRegionId;
            var taxId = taxRegion.taxId;
            var defaultOn = taxRegion.defaultOn;
            var checkedOpt = '';
            var checkTaxRegionId = jQuery.inArray(taxRegionId, selectedTaxRegionIds);
            var checkTaxId = jQuery.inArray(taxId, selectedTaxIds);
            //@todo verify it hasn't been UNchecked
            if(checkTaxRegionId !== -1 || checkTaxId !== -1 || defaultOn == 1){
                checkedOpt = 'checked="checked"';
            }
            var taxOption = $('<label><input class="' + fieldClass + '" name="' + fieldName + '" type="checkbox" value="' + taxRegionId + '" ' + checkedOpt + ' data-tax-id="' + taxId + '"/><span class="checkbox_label">' + taxRegion.title + '</span></label>');
            taxOption.find('input').removeClass('gi_styled');
            taxOptionsWrap.append(taxOption);
        }
    }

    taxOptionObj.find('.options_wrap').replaceWith(taxOptionsWrap);

    updateTaxFieldPreview(taxOptionObj.find('.update_tax_modal_preview'));

    var taxOptionsUpdatedEvent = jQuery.Event('taxOptionsUpdated');
    taxOptionObj.trigger(taxOptionsUpdatedEvent);
}

$(function(){
    if($('.update_tax_modal_preview').length){
        $('.update_tax_modal_preview').each(function() {
            updateTaxFieldPreview($(this));
        });
    }

    if(!$('.tax_region_id').length){
        updateTaxOptions();
    } else {
        var regionId = $('.tax_region_id').val();
        updateTaxOptions(undefined, undefined, regionId);
    }
});

$(document).on('change', '.tax_region_id', function(){
    var regionId = $(this).val();
    updateTaxOptions(undefined, undefined, regionId);
});
