function updateExportAdjustmentsBtn(){
    var count = 0;
    if(selectedRows['sales_order_lines'] != undefined){
        count = selectedRows['sales_order_lines'].length;
    }
    if (selectedRows['order_return_returned_lines'] != undefined) {
        count += selectedRows['order_return_returned_lines'].length;
    }
    if (selectedRows['order_return_damaged_lines'] != undefined) {
        count += selectedRows['order_return_damaged_lines'].length;
    }
    if (selectedRows['inv_adjustment_waste_lines'] != undefined) {
        count += selectedRows['inv_adjustment_waste_lines'].length;
    }
    $('.export_qb_adjustments').find('.export_count').html(count);
    if (count != undefined && count > 0) {
        $('.export_qb_adjustments').show();
    } else {
        $('.export_qb_adjustments').hide();
    }
}

updateExportAdjustmentsBtn();

$(document).on('selectedRow unselectedRow', '.select_cogs input', function(){
    updateExportAdjustmentsBtn();
});



$(document).on('click', '.export_qb_adjustments', function(){
    var submitUrl = 'index.php?controller=accounting&action=preExportAdjustmentsToQuickbooks&ajax=1';
    var modalClass = '';
    var openTabIndex = $('#cogs_adj_tabs').find('.tab.current').data('tab-index');
    jQuery.post(submitUrl,{
        sales_order_lines : selectedRows['sales_order_lines'],
        order_return_returned_lines: selectedRows['order_return_returned_lines'],
        order_return_damaged_lines: selectedRows['order_return_damaged_lines'],
        inv_adjustment_waste_lines: selectedRows['inv_adjustment_waste_lines'],
        export_adjustments : 1,
        cur_tab_index : openTabIndex
    },
    function(data){
        giModalOpen(data.mainContent, submitUrl, modalClass, function(){
            giModalAutoTitle($('#gi_modal'));
            $('#gi_modal').trigger('ajaxContentLoaded');
        });
    });
});
