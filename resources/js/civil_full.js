$(document).ready(function () {
    NavBar.init();
    HomeSlider.init();
    AccreditationPreview.init();
    StatusProgressBar.init(".status-progress__bar");
    TimeleftCounter.init(".investment__preview-value_type_timeleft");
    StickyMenu.init();
    SignUpForm.init();
});

var StickyMenu = function(){
    var ins = {};
    var toggleOnTopClass = function(){
        var scrollTop = $(document).scrollTop();
        var bodyElm = $('body');
        if(scrollTop == 0){
            bodyElm.addClass('on-top');
        }else{
            bodyElm.removeClass('on-top');
        }
    };
    var initEvents = function(){
        toggleOnTopClass();
        $(document).on('scroll', function(event){
            toggleOnTopClass();
        });
    };
    ins.init = function(){
        initEvents();
    };
    return ins;
}();

var NavBar = function(){
    var ins = {};
    var component = {
        navItemOpenClass: 'open',
        $menuItem: $(".nav__li > .sub_menu")
    };
    var handleNavItemCLick = function(e){
        var $thisElm = $(this);

        if($thisElm.hasClass('sub_menu')){

            var $parentLi = $thisElm.parent('.nav__li').first();
            var $childMenu = $parentLi.children('ul').first();

            // close if menu item is opening
            if($thisElm.hasClass(component.navItemOpenClass)){
                $parentLi.removeClass(component.navItemOpenClass);
                $thisElm.removeClass(component.navItemOpenClass);
                $childMenu.slideUp('slow');
                return;
            }

            // close if other menu item is opening
            var $ulParent = $parentLi.parents('ul').first();
            var $currentSelected = $ulParent.children('li.sub_menu.' + component.navItemOpenClass);
            if($currentSelected.length != 0){
                $currentSelected.removeClass(component.navItemOpenClass);
                $currentSelected.children('span').removeClass(component.navItemOpenClass);
                $currentSelected.children('ul').slideUp();
            }

            // opem menu
            $parentLi.addClass(component.navItemOpenClass);
            $thisElm.addClass(component.navItemOpenClass);
            $childMenu.slideDown('slow');

        }
    };
    var handleOutFocus = function(event){
        var $openedSubMenu = $(".nav__li.sub_menu.open");

        var isNavOpened = $openedSubMenu.length !== 0;
        if(!isNavOpened){
            return;
        }   

        var isClickingOnNav = $(event.target).closest(".nav ul").length !== 0;
        if(isClickingOnNav){
            return;
        }

        $openedSubMenu.each(function(indexInArray, valueOfElement){
            var $thisElm = $(this);
            $thisElm.children('.title').removeClass(component.navItemOpenClass);
            $thisElm.removeClass(component.navItemOpenClass);
            $thisElm.children('ul').slideUp('slow');
            $thisElm.removeClass(component.navItemOpenClass);
        });
    };
    ins.init = function(){
        component.$menuItem.on('click', handleNavItemCLick);
        $(document).on('click', handleOutFocus);
    };
    return ins;
}();

var HomeSlider = function(){
    var ins = {};
    var component = {
        sliderSection: $("#home_slider"),
        slider: $("#home_slider_wrap"),
        sliderItems: $("#home_slider_wrap > *"),
        sliderPrevBtnId: "#home_slider_wrap_prev",
        sliderNextBtnId: "#home_slider_wrap_next",
    };
    var slickOptions = {
        slidesToShow: 3,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 15000,
        slidesToScroll: 1,
        arrows: true,
        prevArrow: component.sliderPrevBtnId,
        nextArrow: component.sliderNextBtnId,
        responsive: [
            {
                breakpoint: 1024,
                settings: {                  
                    slidesToShow: 2,
                }
            },
            {
                breakpoint: 767,
                // settings: "unslick"
                settings: {                  
                    slidesToShow: 1,
                }
            },
        ]
    };
    // if(component.sliderItems.length < 3){
    //     slickOptions.slidesToShow = component.sliderItems.length;
    // }
    var reInitSlick = function(){
        if(component.slider.hasClass('slick-initialized')){
            return;
        }
        component.slider.slick(slickOptions);
    };
    var turnOffSlickAutoplay = function(){
        component.slider.slick('slickPause');
    };
    var afterInitSlider = function(event, slick){
        $(document).on('click', '.investment .youtube-embeded', turnOffSlickAutoplay);
        $(window).resize(reInitSlick);
    };
    var setSliderSectionComponents = function(){
        component.sliderButtonWrap = component.sliderSection.find('.section__slider-buttons');
        component.sliderSectionOffset = component.sliderSection.offset();
        component.sliderSectionOffsetTop = component.sliderSectionOffset.top;
        component.sliderSectionOffsetBottom = component.sliderSectionOffset.top + component.sliderSection.height();
    };
    var onWindowScroll = function(event){
        if(component.sliderSection.length == 0){
            return;
        }
        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();
        var scrollBottom = scrollTop + windowHeight;
        if(
            scrollBottom > component.sliderSectionOffsetTop &&
            scrollTop < component.sliderSectionOffsetBottom
        ){
            component.sliderButtonWrap.fadeIn();
        }else{
            component.sliderButtonWrap.fadeOut();
            return;
        }

        var sliderSectionHeight = component.sliderSection.height() - 100;
        var buttonTopAbsolutePosition = scrollTop - component.sliderSectionOffsetTop + windowHeight/2;
        if(buttonTopAbsolutePosition > 100 && buttonTopAbsolutePosition < sliderSectionHeight){
            component.sliderButtonWrap.css('top', buttonTopAbsolutePosition);
        }
    };
    ins.init = function(){
        if(component.slider.length == 0){
            return;
        }
        component.slider.on('init', afterInitSlider);
        component.slider.slick(slickOptions);

        if(component.sliderSection.length !== 0){
            setSliderSectionComponents();
        }
        $(window).scroll(onWindowScroll);
        $(window).resize(setSliderSectionComponents);
    };
    return ins;
}();

var AccreditationPreview = function(){
    var ins = {};
    var component = {
        previewElm: $(".accreditation-preview"),
        showMoreElm: $(".accreditation-preview__show_more"),
        fullContentClass: "full-content",
    };
    ins.init = function(){
        if(component.previewElm.length == 0){
            return;
        }
        component.showMoreElm.click(function (e) { 
            e.preventDefault();
            component.previewElm.toggleClass(component.fullContentClass);
        });
    };
    return ins;
}();

var StatusProgressBar = function(){
    var ins ={};
    var component = {};
    component.element = $(".status-progress__bar");
    /** Progress bar animation in Listing item when it shows on screen**/
    var showProgressBar = function() {
        component.element.each(function() {
            var screenH = $(document).scrollTop();
            var windowH = $(window).height();
            var screenBottomH = screenH + windowH;
            var proBarH = $(this).offset().top;
            //Trigger animation in the half size of window height
            if (!$(this).hasClass('animated') && (screenBottomH > proBarH)){
                $(this).addClass('animated');
                $(this)
                    .data('origWidth', $(this).width())
                    .width(0)
                    .animate(
                        { width: $(this).data('origWidth') },
                        {
                            duration: 1000,
                            specialEasing: {
                                width: 'easeOutBounce'
                            },
                            // complete:function(){
                            // }
                        }
                    )
                ;
            }
        });
    };
    var initElements = function(){
        /** Set Progress bar's width by rate**/
        component.element.each(function() {
            var rate =  $(this).data('rate');
            $(this).css('width', rate + '%');
        });
    };
    ins.init = function(selectorString){
        if(typeof selectorString !== "undefined" && selectorString){
            component.element = $(selectorString);
        }
        if(component.element.length == 0){
            return;
        }
        /** Progress bar animation(Onload)**/
        initElements();
        showProgressBar();
        $(document).scroll(function () { 
            // and onload
            showProgressBar();
        });
    };
    return ins;
}();

var TimeleftCounter = function(){
    var ins = {};
    var component = {};
    component.element = $(".investment__timeleft");
    var initElements = function(){
        /** Set countdown to time-left element**/
        component.element.each(function() {
            var datetime = $(this).data('end-datetime');
            var timerEl = $(this).find('.timer');
            countDown(datetime, timerEl[0]);
        });
    };
    var countDown = function(datetime, element) {
        // Set the date we're counting down to
        var countDownDate = new Date(datetime).getTime();
        // Update the count down every 1 second
        var x = setInterval(function() {
        // Get today's date and time
        var now = new Date().getTime();
        // Find the distance between now and the count down date
        var distance = countDownDate - now;
        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        // var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        // var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        // var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        var unit = "day";
        if(days > 1){
            unit += 's';
        }

        // Display the result in the element with id="demo"
        element.innerHTML = days + " " + unit;
        // + hours + "h &#183; " + minutes + "m &#183; " + seconds + "s ";
        // If the count down is finished, write some text 
        if (distance < 0) {
            clearInterval(x);
            element.innerHTML = "CLOSED";
            element.classList.add("closed");
        }
        }, 1000);
    }
    ins.init = function(selectorString){
        if(typeof selectorString !== 'undefined' && selectorString){
            component.element = $(selectorString);
        }
        initElements();
    };
    return ins;
}();

var SignUpForm = function(){
    var ins = {};

    var component = {};
    component.signatureShowErrorClass = 'show-error-after-init';
    component.ajaxContentWrap = $(".ajaxed_contents");
    component.signUpAccreditationStepWrap = $(".sign_up_step_5");
    component.signUpSignatureInputSelector = "#signup .form__input_type_signature";
    component.signUpPhoneFieldClass = ".sign_up_phone_input";
    component.signUpOTPMessageFieldClass = ".sign_up_form_otp_msg_input";
    component.signUpAjaxContentWrap = $(".form_type_sign-up.ajaxed_contents");
    component.stepFormWrapId = "#step_form_wrap";
    component.signUpCompleteButtonId = "#signup_form_complete_button";
    component.signaturePrintInputName = 'input[name="signature_agreentment_print_name"]';
    component.signatureAgreementInputName = 'input[name="signature_agreentment"]';
    component.signatureElementClass = '.signature_element';
    component.signatureAreaClass = '.jsignature_signarea ';
    component.fieldErrorClass = '.field_error';

    var isSignatureStep = function(){
        component.signUpAccreditationStepWrap = $(".sign_up_step_5");
        return component.signUpAccreditationStepWrap.length != 0;
    };

    var setComponents = function() {

        if(!isSignatureStep()){
            return;
        }

        component.signatureCanvas = $(".jSignature");
        component.signatureSlider = component.signatureCanvas.parents('.slider_page').first();
    };
    
    var initSignature = function(){
        if(component.signUpAccreditationStepWrap.length == 0){
            return;
        }

        if(
            component.signatureCanvas.length == 0 ||
            component.signatureSlider.length == 0 ||
            !component.signatureSlider.hasClass("current")
        ){
            return;
        }

        // reinit jSignature
        createJSignatures();
    };

    var afterGoToNextStep = function (e) {
        setComponents();
        initSignature();
        scrollToFormTop();
    };

    var onAjaxContentFailed = function(e, returnData){
        if(
            typeof returnData == 'undefined' ||
            typeof returnData.success == 'undefined'
        ){
            window.location.href = './error';
        }

        if(!returnData.success){
            var signatureInput = $(component.signUpSignatureInputSelector);
            if(signatureInput.length == 0){
                return;
            }
            var errorField = signatureInput.find(component.fieldErrorClass).first();
            if(errorField.length == 0){
                return;
            }
            errorField.show();
        }
    };

    var togglePhoneInput = function(){
        var phoneFieldElm = $(component.signUpPhoneFieldClass);
        phoneFieldElm.hide();

        var otpMessageElm = $(component.signUpOTPMessageFieldClass);
        if(otpMessageElm.length == 0){
            return;
        }

        var otpMessageInputs = otpMessageElm.find("input[type='radio']");
        var checkedInput = otpMessageInputs.filter(':checked');
        if(checkedInput.length == 0){
            return;
        }

        var selectedVal = checkedInput.val();
        if(selectedVal == 'phone'){
            if(phoneFieldElm.length == 0){
                return;
            }
            phoneFieldElm.show();
        }
    }

    var initOtpProcess = function(){
        // toggle on load
        togglePhoneInput();
        // toggle on change
        var otpMessageElm = $(component.signUpOTPMessageFieldClass);
        var otpMessageInputs = otpMessageElm.find("input[type='radio']");
        otpMessageInputs.on('change', togglePhoneInput);
    };

    var scrollToFormTop = function(){
        // debugger;
        component.signUpAjaxContentWrap = $(component.stepFormWrapId)
        if(component.signUpAjaxContentWrap.length == 0){
            return;
        }

        var formOffSet = component.signUpAjaxContentWrap.offset().top;
        var windownHeight = $(window).height();
        var errorFormHeight = component.signUpAjaxContentWrap.height();

        var animateOptions = {
            scrollTop: parseInt(formOffSet - (windownHeight/2) + (errorFormHeight/2))
        };
        $("html,body").animate(animateOptions, 500, 'swing');
    }

    var afterAjaxContentLoaded = function(){
        initOtpProcess();
    };

    var showSignatureError = function(errorElm, errorMsg){
        var fieldError = errorElm.find(component.fieldErrorClass);
        if(fieldError.length == 0){
            errorElm.append('<div class="field_error"></div>');
            fieldError = errorElm.find(component.fieldErrorClass);
        }
        fieldError.text(errorMsg);
        fieldError.show();
        errorElm.addClass('error');
    };

    var onCompleteBtnClick = function(event){
        $(this).removeClass('disabled')
        if(!isSignatureStep()){
            return;
        }
        var signatureElm = component.signUpAccreditationStepWrap.find(component.signatureElementClass);
        if(signatureElm.length == 0){
            return;
        }

        var isError = false;

        // empty signature
        var signatureCheck =  signatureElm.jSignature('getData', 'native');
        if(signatureCheck.length == 0){
            isError = true;
            showSignatureError(signatureElm.find(component.signatureAreaClass), 'Signature is required');
        }

        // empty print name
        var printNameElm = signatureElm.find(component.signaturePrintInputName);
        if(printNameElm.length !== 0){
            var printNameValue = printNameElm.val();
            if(printNameValue.trim() == ''){
                isError = true;
                showSignatureError(printNameElm.closest('.form_element'), 'Print name is required');
            }
        }

        // empty checked agreement
        var agreementCheckboxElm = signatureElm.find(component.signatureAgreementInputName);
        if(agreementCheckboxElm.length !== 0){
            if(!agreementCheckboxElm.is(':checked')){
                isError = true;
                showSignatureError(agreementCheckboxElm.closest('.form_element'), 'Agreement is required');
            }
        }

        if(isError){
            return;
        }

        var realSubmitBtn = component.signUpAccreditationStepWrap.find('.submit_btn');
        realSubmitBtn.trigger('click');
    };

    var onSignatureInputsChange = function(event){
        var formElementParent = $(this).closest('.form_element');
        if(formElementParent.hasClass('error')){
            formElementParent.find(component.fieldErrorClass).hide();
            formElementParent.removeClass('error');
        }
    };

    var initSignatureFormVerification = function(){
        // on submit btn click
        $(document).on('click', component.signUpCompleteButtonId, onCompleteBtnClick);

        // on input change
        $(document).on('change', component.stepFormWrapId + ' .form_element input', onSignatureInputsChange)
    };

    ins.init = function(){
        setComponents();

        // accredition step form
        $(document).on('afterGoToNextStep', afterGoToNextStep);

        // sign up form error handling
        $(document).on('ajaxedContentsSubmitFormFail', onAjaxContentFailed);

        // init sign up form otp process
        initOtpProcess()

        // toggle on ajax content loaded
        $(document).on('ajaxContentLoaded', afterAjaxContentLoaded);

        // init signature form verification
        initSignatureFormVerification();

    };
    return ins;
}();