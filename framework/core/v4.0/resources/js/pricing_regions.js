$(document).on('ready', function (event) {
    updateRegionOptions();
});

$(document).on('change', '#field_country_refs', function (event, element, selectric) {
    updateRegionOptions();
});

function updateRegionOptions() {
    var selectedCountryRefs = $('#field_country_refs').val();
    var isItAnArray = false;
    if (selectedCountryRefs instanceof Array) {
        isItAnArray = true;
    } 
    var regionsField = $('#field_region_refs');
    
    var optGroups = regionsField.find('optgroup');
    optGroups.each(function(){
        var value = $(this).attr('label');
        if (isItAnArray && (selectedCountryRefs.indexOf(value) !== -1)) {
            $(this).attr('disabled', false);
        } else {
            $(this).attr('disabled', 'disabled');
        }
        regionsField.selectric('refresh');
    });
}