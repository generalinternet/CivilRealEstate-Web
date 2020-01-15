var cadToUSD = 0;
var usdToCAD = 0;

$(document).on('change', '#field_cur_refs', function(){
    var curRef = $(this).val();
    var label = $('#felm_conversion_rate label.main');
    if(curRef == 'cad'){
        $('#field_conversion_rate').val(usdToCAD);
        label.html('USD <span class="r_arrow"></span> CAD @');
    } else {
        $('#field_conversion_rate').val(cadToUSD);
        label.html('CAD <span class="r_arrow"></span> USD @');
    }
});

function calculateOppRate(rate){
    if(rate != 0){
        var oppRate = 1/parseFloat(rate);
        var rNum = 10000000000;
        var roundedOppRate = Math.round(oppRate * rNum)/rNum;
        return roundedOppRate;
    } else {
        return 0;
    }
}

$(document).on('keyup', '#field_conversion_rate', function(){
    var curRef = $('#field_cur_refs').val();
    var rate = $(this).val();
    var oppRate = calculateOppRate(rate);
    if(curRef == 'cad'){
        usdToCAD = rate;
        cadToUSD = oppRate;
    } else {
        cadToUSD = rate;
        usdToCAD = oppRate;
    }
});

$(function(){
    cadToUSD = $('#field_cad_to_usd_rate').val();
    usdToCAD = $('#field_usd_to_cad_rate').val();
    var curRefsField = $('#field_cur_refs');
    if(curRefsField.length){
        var curRef = $('#field_cur_refs').val();
        if(curRef == 'cad'){
            $('#felm_conversion_rate label.main').html('USD <span class="r_arrow"></span> CAD @');
        }
    }
});

function getNewReportList(){
    elmStartLoading($('#saved_report'), 'circle');
    var loc = $('#field_loc_tag_ref').val();
    var curRefs = $('#field_cur_refs').val();
    var year = $('#field_fiscal_year').val();
    var usedReportIds = $('#field_used_report_ids').val();
    jQuery.post('index.php?controller=admin&action=getAccountingReportsForm&ajax=1&loc=' + loc + '&curRefs=' + curRefs + '&year=' + year + '&usedReportIds=' + usedReportIds, function (data) {
        var parsedData = JSON.parse(data);
        if (parsedData.mainContent != undefined) {
            var tabContent = $('#saved_report').closest('.tab_content');
            tabContent.html(parsedData.mainContent);
        }
    });
}

$(document).on('change', '#field_loc_tag_ref, #field_cur_refs, #field_fiscal_year', function(){
    getNewReportList();
});

$(document).on('keyup', '#recalculate_report #field_usd', function(){
    var rate = $(this).val();
    var oppRate = calculateOppRate(rate);
    $('#recalculate_report #field_cad').val(oppRate);
});

$(document).on('keyup', '#recalculate_report #field_cad', function(){
    var rate = $(this).val();
    var oppRate = calculateOppRate(rate);
    $('#recalculate_report #field_usd').val(oppRate);
});
