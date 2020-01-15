function convertUnits(val, curUnit, targetUnit, unitType){
    var convertURL = 'index.php?controller=core&action=convert&ajax=1';
    convertURL += '&val=' + val;
    convertURL += '&curUnit=' + curUnit;
    convertURL += '&targetUnit=' + targetUnit;
    convertURL += '&unitType=' + unitType;
    var data = {};
    var result = '';
    jQuery.ajax({
        type: 'POST',
        url: convertURL,
        data: data,
        async: false,
        success: function (data) {
            if(data.success){
                result = data.converted;
            } else {
                result = null;
            }
        }
    });
    return result;
}

function runConversion(form, direction){
    var toSelected = form.find('.convert_to_unit option:selected');
    var toUnit = toSelected.val();
    var toUnitType = toSelected.closest('optgroup').attr('label');
    var fromSelected = form.find('.convert_from_unit option:selected');
    var fromUnit = fromSelected.val();
    var fromUnitType = fromSelected.closest('optgroup').attr('label');
    var val = parseFloatNoNaN(form.find('.convert_from_val').val());
    var updateField = form.find('.convert_to_val');
    var curUnit = fromUnit;
    var targetUnit = toUnit;
    if(direction == 'to'){
        val = parseFloatNoNaN(form.find('.convert_to_val').val());
        updateField = form.find('.convert_from_val');
        curUnit = toUnit;
        targetUnit = fromUnit;
    }
    if(toUnitType != fromUnitType){
        console.log('Units must be of the same type to convert.');
        updateField.val('');
    } else {
        var result;
        if(toUnitType == 'Volume'){
            result = convertUnits(val, curUnit, targetUnit, 'volume');
        } else {
            result = convertUnits(val, curUnit, targetUnit, 'length');
        }
        updateField.val(result);
    }
}

$(document).on('change', '.convert_from_unit', function(){
    var form = $(this).closest('form');
    runConversion(form, 'from');
});

$(document).on('keyup', '.convert_from_val', function(){
    var form = $(this).closest('form');
    runConversion(form, 'from');
});

$(document).on('change', '.convert_to_unit', function(){
    var form = $(this).closest('form');
    runConversion(form, 'to');
});

$(document).on('keyup', '.convert_to_val', function(){
    var form = $(this).closest('form');
    runConversion(form, 'to');
});
