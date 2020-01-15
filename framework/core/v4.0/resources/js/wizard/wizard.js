/*
wizard.js v3.0.0
*/

$(function(){
    $('.wiz_step_wrap.wizard').each(function(){
        $('.wiz_step_wrap.wizard').find('.wiz_step:first .prev_wiz_step').remove();
        $('.wiz_step_wrap.wizard').find('.wiz_step:last .next_wiz_step').remove();
        if($(this).find('.form_element.error').length){
            var errorStep = $(this).find('.form_element.error').first().closest('.wiz_step');
            loadWizardStep(errorStep, true);
        } else {
            var currentStep = $(this).find($('.wiz_step.current'));
            if(!currentStep.length){
                loadWizardStep(getFirstWizardStep($(this)));
            }
        }
    });
});

function getFirstWizardStep(stepWrap){
    var startStepRef = stepWrap.data('start-step');
    var startStep = null;
    if(startStepRef != undefined && startStepRef != ''){
        startStep = stepWrap.find('.wiz_step[data-ref="' + startStepRef + '"]');
    }
    if(startStep == null){
        startStep = stepWrap.find($('.wiz_step:first'));
    }
    
    if(startStep.length){
        return startStep;
    }
    
    return null;
}

function loadWizardStep(step, scrollTo){
    if(step.length){
        var loadEvent = jQuery.Event('loadWizStep');
        step.trigger(loadEvent);
        if(!loadEvent.isDefaultPrevented()){
            var stepRef = step.data('ref');
            var stepWrap = step.closest('.wiz_step_wrap');
            var curStepRef = stepWrap.data('cur-step-ref');
            if(curStepRef != undefined){
                stepWrap.removeClass('cur_step_' + curStepRef);
            }
            stepWrap.find('.wiz_step').removeClass('current');
            stepWrap.find('.go_to_wiz_step').removeClass('current');
            step.addClass('current');
            stepWrap.data('cur-step-ref', stepRef);
            stepWrap.addClass('cur_step_' + stepRef);
            stepWrap.find('.go_to_wiz_step[data-step="' + stepRef + '"]').addClass('current');
            var loadedEvent = jQuery.Event('loadedWizStep');
            step.trigger(loadedEvent);
            newContentLoaded();
            if(scrollTo){
                $('html, body').animate({
                    scrollTop: step.offset().top
                }, 500, 'swing');
            }
        }
    }
}

$(document).on('click tap', '.next_wiz_step', function (e) {
    e.preventDefault();
    var nextEvent = jQuery.Event('nextWizStep');
    $(this).trigger(nextEvent);
    if(!nextEvent.isDefaultPrevented()){
        var stepWrap = $(this).closest('.wiz_step_wrap');
        var currentStep = stepWrap.find($('.wiz_step.current'));
        var nextStep = currentStep.next('.wiz_step');
        loadWizardStep(nextStep, true);
    }
});

$(document).on('click tap', '.prev_wiz_step', function (e) {
    e.preventDefault();
    var prevEvent = jQuery.Event('prevWizStep');
    $(this).trigger(prevEvent);
    if(!prevEvent.isDefaultPrevented()){
        var stepWrap = $(this).closest('.wiz_step_wrap');
        var currentStep = stepWrap.find($('.wiz_step.current'));
        var prevStep = currentStep.prev('.wiz_step');
        loadWizardStep(prevStep, true);
    }
});

$(document).on('click tap', '.go_to_wiz_step', function (e) {
    e.preventDefault();
    var goToEvent = jQuery.Event('goToWizStep');
    $(this).trigger(goToEvent);
    if(!goToEvent.isDefaultPrevented() && !$(this).is('.disabled')){
        var stepWrap = $(this).closest('.wiz_step_wrap');
        var stepRef = $(this).data('step');
        var step = stepWrap.find('.wiz_step[data-ref="' + stepRef + '"]');
        loadWizardStep(step, true);
    }
});
