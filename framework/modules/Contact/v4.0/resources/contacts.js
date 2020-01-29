/*
modules/contacts.js v3.0.4
*/

function toggleContactTaxes(){
    var countryCode = $('.field_addr_country').first().val();
    $('.taxable_region').slideUp();
    if($('.taxable_region[data-country-code="' + countryCode + '"]').length){
        $('#taxable_region_wrap').slideDown(function(){
            $('.taxable_region[data-country-code="' + countryCode + '"]').slideDown();
        });
    } else {
        $('#taxable_region_wrap').slideUp();
    }
}

$(function(){
    toggleContactTaxes();
    refreshAllContactInfos();
});

$(document).on('change', '.field_addr_country', function(){
    toggleContactTaxes();
});

$(document).on('click', '.add_contact_info', function(e){
    e.preventDefault();
    var addURL = $(this).attr('href');
    var contactInfosWrap = $(this).parents('.contact_infos_wrap');
    var lastContactInfo = contactInfosWrap.find('.contact_info:last');
    var seqNumber = lastContactInfo.find('.seq_count').val();
    var nextSeqNumber = parseIntNoNaN(seqNumber) + 1;
    jQuery.post(addURL + '&seq=' + nextSeqNumber + '&ajax=1', function (data) {
        //var parsedData = JSON.parse(data);
        var contactInfo = data.contactInfo;
        if(contactInfo != undefined && contactInfo != ''){
            var contactInfoObj = $(contactInfo).hide();
            if(lastContactInfo.length){
                lastContactInfo.after(contactInfoObj);
            } else {
                contactInfosWrap.prepend(contactInfoObj);
            }
            contactInfoObj.slideDown();
            newContentLoaded();
        }
    });
});

$(document).on('click', '.remove_contact_info', function(e){
    e.preventDefault();
    var contactInfo = $(this).parents('.contact_info');
    $(this).remove();
    contactInfo.slideUp(function(){
        contactInfo.remove();
    });
});

/** Change contact category form : radio button**/
$(document).on('change', '.change_category_form', function(e){
    //Swap the form
    var type = $(this).val();
    var addURL = 'index.php?controller=contact&action=addContactCat&type=' + type;
    var contactId = $('input[name="hidden_contact_id"]').val();
    if (contactId != '') {
        addURL += '&contactId='+contactId;
    }
    addURL += '&ajax=1';
    jQuery.post(addURL, function (data) {
        var contactCat = data.contactCat;
        //Remove the previous form first
        $('.contact_cat').slideUp(function(){
            $(this).remove();
        });
        if(contactCat != undefined && contactCat != ''){
            //Swap the form
            var contactCatObj = $(contactCat).hide();
            $("#contact_category_form_wrap").append(contactCatObj);
            contactCatObj.slideDown(function(){
                newContentLoaded();
            });
        }
    });
});

//Quick Book
$(document).on('click', '#qb_info_section_expand', function(e){
    var qbEl = $('#qb_info_section');
    if (qbEl.hasClass('collapsed')) {
        $('#qb_info_content').slideDown();
        qbEl.removeClass('collapsed');
        qbEl.data('zipped', 0);
        $(this).find('.icon').removeClass('arrow_down').addClass('arrow_up');
    } else {
        $('#qb_info_content').slideUp(function () {
            qbEl.addClass('collapsed');
        });
        qbEl.data('zipped', 1);
        $(this).find('.icon').removeClass('arrow_up').addClass('arrow_down');
    }
});
$(document).on('click', '.close_qb_section', function(e){
    closeQBSection();
});
function closeQBSection() {
    var qbEl = $('#qb_info_section');
    $('#qb_info_content').slideUp(function () {
        qbEl.addClass('collapsed');
    });
    qbEl.data('zipped', 1);
    $('#qb_info_section_expand').find('.icon').removeClass('arrow_up').addClass('arrow_down');
}

/** Contact event startdatetime and enddatetime:start **/
function bindContactEventFormElements() {
    $(document).on('change', '.field_start_date', function(e){
        e.preventDefault();
        if(isValidDateString($(this).val())) {//Validation check for date
            var endDateEl = $(this).closest('form').find('.field_end_date');
            if (endDateEl.length) {
                var endDate = endDateEl.val();
                if (endDate == '') {
                    endDateEl.val($(this).val());
                }
            }
        }  
    });
    $(document).on('change', '.field_start_time', function(e){
        e.preventDefault();
        var endTimeEl = $(this).closest('form').find('.field_end_time');
        var startDateEl = $(this).closest('form').find('.field_start_date');
        if (endTimeEl != undefined && startDateEl != undefined) {
            var startDate = startDateEl.val();
            if (startDate == '') {
                startDate = formatDate(new Date());
            }
            var startTime  = $(this).val();
            var startDateTime = createDateTimeByString(startDate, startTime);
            if (startDateTime) {
                //Valid date time
                var endDateTime = new Date(startDateTime.getTime() + (2*60*60*1000));//Add 2 hours
                if (startDateEl.val() == '') {
                    //If there is no start date, set current date
                    startDateEl.val(startDate);
                }
                var endDateEl = $(this).closest('form').find('.field_end_date');
                if (endDateEl != undefined && endDateEl.val() == '') {
                    //If there is no end date, set end date
                    endDateEl.val(formatDate(endDateTime));
                }
                if (endTimeEl.val() == '') {
                    endTimeEl.val(formatTime(endDateTime));
                }
            }
        }
    });
}
//Check valid date
function isValidDateString(dateString) {
  var d = new Date(dateString);
  if(Number.isNaN(d.getTime())) return false; // Invalid date
  return d.toISOString().slice(0,10) === dateString; //Check invalid date such as leap day or the last date of each month. Only it works for date format yyyy-MM-dd
}
function formatDate(d) {
    var yyyy = d.getFullYear();
    var MM = d.getMonth() + 1;
    var dd = d.getDate();
    if (MM < 10) {
        MM = '0' + MM;
    }
    if (dd < 10) {
        dd = '0' + dd;
    }
    return yyyy + '-' + MM + '-' + dd;
}
function formatTime(d) {
    var HH = d.getHours();
    var mm = d.getMinutes();
    var ampm = 'am';
    if (HH == 0) {
        HH = '12';
    } else if (HH > 0 && HH < 10) {
        //HH = '0' + HH;
    } else if (HH == 12) {
        ampm = 'pm';
    } else if (HH > 11) {
        HH = HH-12;
        if (HH > 0 && HH < 10) {
            //HH = '0' + HH;
        }
        ampm = 'pm';
    }

    if (mm < 10) {
        mm = '0' + mm;
    }
    
    return HH + ':' + mm + ' ' + ampm;
}
/*
 * Create Date object by date string, time string
 * @param {type} dateStr yyyy-MM-dd
 * @param {type} timeStr HH:mm am
 * @returns {String}
 */
function createDateTimeByString(dateStr, timeStr) {
    var dateRegEx = /^\d{4}-\d{2}-\d{2}$/;
    if(!dateStr.match(dateRegEx)) {
        return false;// Invalid date format
    }
    var timeRegEx = /\b((1[0-2]|0?[1-9]):([0-5][0-9]) ([ap][m]))/;
    if(!timeStr.match(timeRegEx)) {
        return false;// Invalid time format
    }
    if (!isValidDateString(dateStr)) {
        return false; // Invalid date
    }
    
    var dateArr = dateStr.split("-");
    var yyyy = dateArr[0];
    var MM = parseInt(dateArr[1]) - 1;
    var dd = parseInt(dateArr[2]);
    
    var timeArr = timeStr.split(" ");
    var HHmmStr = timeArr[0];
    var ampm = timeArr[1];
    var HHmmArr = HHmmStr.split(":");
    var HH = HHmmArr[0];
    var mm = HHmmArr[1]
    if (ampm == 'pm' && HH != '12') {
        HH = parseInt(HH) + 12;
    }
    if (ampm == 'am' && HH == '12') {
        HH = 0;
    }
    return new Date(yyyy, MM, dd, HH, mm, 0, 0);
}
/** Contact event startdatetime and enddatetime:end **/

//Conatct Org's interest rate section
$(document).on('change', 'input[name="use_default_rate"]', function(e){
    e.preventDefault();
    if ($(this).prop('checked')) {
        //Hide input form and show the description
        $('.def_int_rate_form').hide();
        $('.def_int_rate_desc').slideDown();
    } else {
        //Show input form and hide the description
        $('.def_int_rate_desc').hide();
        $('.def_int_rate_form').slideDown();
    }
});


//Quickbooks Import/Export Forms
$(document).on('change', 'input.qb_contact_info', function (e) {
    refreshAllContactInfos($(this));
});

function refreshAllContactInfos(t) {
    $('.qb_contact_info').each(function (i, obj) {
        $(this).closest("label").show();
    });
    var checkedVals = [];
    var checkedNames = [];
    $('.qb_contact_info:checked').each(function (i, obj) {
        var name = $(this).attr('name');
        var value = $(this).val();
        if (value !== 'new' && value !== 'dne' && value !== 'sab') {
            var index = jQuery.inArray(value, checkedVals);
            if (index === -1) {
                checkedNames.push(name);
                checkedVals.push(value);
            } else if (t !== undefined && name === t.attr('name')) {
                checkedNames[index] = name;
                checkedVals[index] = t.val();
            }

        }
    });
    $('.qb_contact_info').each(function (i, obj) {
        var value = $(this).val();
        var name = $(this).attr('name');
        var index = jQuery.inArray(value, checkedVals);
        if (index === -1) {
            $(this).closest("label").show();
        } else {
            if (name !== checkedNames[index]) {
                $(this).prop('checked', false);
                $(this).closest("label").hide();
            }
        }
    });

}

$(document).on('autocompleteSelected', '.price_sheet_item_ac', function(e, data) {
    var form = $(this).closest('form');
    var rateField = form.find('input[name="rate"]');
    var rate = null;
    if(data.item != undefined){
        if(data.item.costPerUnit != undefined && data.item.costPerUnit != ''){
            rate = data.item.costPerUnit;
        }
    } else {
        if(data.costPerUnit != undefined && data.costPerUnit != ''){
            rate = data.costPerUnit;
        }
    }
    removePotentialValue(rateField);
    if(rate != null){
        rateField.val(makeItMoney(rate)).trigger('keyup');
    }
});

$(document).on('autocompleteclose', '.price_sheet_item_ac', function(e, data) {
    var form = $(this).closest('form');
    var rateField = form.find('input[name="rate"]');
    
    removePotentialValue(rateField);
});

$(document).on('autocompletefocus', '.price_sheet_item_ac', function (e, data){
    var form = $(this).closest('form');
    var rateField = form.find('input[name="rate"]');
    var rate = null;
    if(data.item.costPerUnit != undefined && data.item.costPerUnit != ''){
        rate = data.item.costPerUnit;
    }
    removePotentialValue(rateField);
    if(rate != null && rateField.length){
        setPotentialValue(rateField, makeItMoney(rate));
    }
});

$(document).on('change', '.package_option input', function(e){
    $(".package_option").removeClass('selected');
    $(this).parents('.package_option').first().addClass('selected');
});