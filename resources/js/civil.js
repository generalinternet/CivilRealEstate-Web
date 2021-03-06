$(document).ready(function () {
    RelistingSlider.init();
    RelistingSearch.init();
    CharityForm.init();
    RelistingDetail.init();
});

var EmbeddedMap = function () {
    var mapFrame = document.getElementById("embedded_map");
    var mapOptions = {
        scrollwheel: false,
        zoom: 14,
        styles: [{
                "elementType": "geometry",
                "stylers": [{
                    "color": "#f5f5f5"
                }]
            },
            {
                "elementType": "labels.icon",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#616161"
                }]
            },
            {
                "elementType": "labels.text.stroke",
                "stylers": [{
                    "color": "#f5f5f5"
                }]
            },
            {
                "featureType": "administrative.land_parcel",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#bdbdbd"
                }]
            },
            {
                "featureType": "poi",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#eeeeee"
                }]
            },
            {
                "featureType": "poi",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#757575"
                }]
            },
            {
                "featureType": "poi.park",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#e5e5e5"
                }]
            },
            {
                "featureType": "poi.park",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            },
            {
                "featureType": "road",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#ffffff"
                }]
            },
            {
                "featureType": "road.arterial",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#757575"
                }]
            },
            {
                "featureType": "road.highway",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#dadada"
                }]
            },
            {
                "featureType": "road.highway",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#616161"
                }]
            },
            {
                "featureType": "road.local",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            },
            {
                "featureType": "transit.line",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#e5e5e5"
                }]
            },
            {
                "featureType": "transit.station",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#eeeeee"
                }]
            },
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#c9c9c9"
                }]
            },
            {
                "featureType": "water",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            }
        ]
    };

    var createMap = function (lat, lng) {
        var centerLatLng = new google.maps.LatLng(lat, lng);
        var mapPin = {
            url: "resources/media/img/art/icon_location_mark.png"
        };
        mapOptions.center = centerLatLng;
        var map = new google.maps.Map(mapFrame, mapOptions);
        var marker = new google.maps.Marker({
            map: map,
            position: centerLatLng,
            icon: mapPin
        });
    };

    var initMap = function () {

        if (
            !mapFrame ||
            typeof google === 'undefined'
        ) {
            return;
        }

        var geocoder = new google.maps.Geocoder();
        var address = mapFrame.dataset.address;

        geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status != 'OK') {
                return;
            }
            var resultLatlng = results[0].geometry.location;
            var lat = resultLatlng.lat();
            var lng = resultLatlng.lng();
            createMap(lat, lng);
        });
    };

    initMap();
}();

var RelistingSlider = function () {
    var ins = {};
    var component = {
        slider: $(".relisting-detail__slider"),
        sliderItems: $(".relisting-detail__slider > *"),
        sliderPrevBtnClass: ".relisting-detail__slider-prev",
        sliderNextBtnClass: ".relisting-detail__slider-next",
        nextArrowKeycode: 39,
        prevArrowKeycode: 37,
    };
    var slickOptions = {
        slidesToShow: 1,
        infinite: true,
        autoplay: true,
        speed: 1000,
        slidesToScroll: 1,
        arrows: true,
        dots: true,
        prevArrow: component.sliderPrevBtnClass,
        nextArrow: component.sliderNextBtnClass,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 1,
            }
        }, ]
    };
    var initArrowKeyNavigation = function(){
        $(document)
            .on('load', function(){
                component.slider.focus();
            })
            .on('keydown', function(e){
                var keycode = e.keyCode;
                if(keycode != component.nextArrowKeycode && keycode != component.prevArrowKeycode){
                    return;
                }

                component.slider.focus();
                if(keycode == component.nextArrowKeycode){
                    component.slider.slick('slickNext');
                }
                if(keycode == component.prevArrowKeycode){
                    component.slider.slick('slickPrev');
                }
            })
        ;
    };
    ins.init = function () {
        if (component.slider.length == 0) {
            return;
        }
        component.slider.slick(slickOptions);
        
        initArrowKeyNavigation();
    };
    return ins;
}();

var RelistingSearch = function (){
    var component = {
        searchWrapClass: '.relisting-search',
        sortFormInput: ".relisting__sortby-list .form__input"
    };
    var ins = {};

    ins.init = function() {
        $(document).on('click', component.searchWrapClass + ' .relisting-search__field-label', function () {
            var parentInput = $(this).parents('.relisting-search__field').first();
            var fieldContent = parentInput.find('.relisting-search__field-wrap').first();
            fieldContent.slideToggle();
            parentInput.toggleClass('open');
        });
        $(document).on('change', component.sortFormInput + ' input', function(){
            var parentForm = $(this).parents('form').first();
            if(parentForm.length !== 0){
                parentForm.submit();
            }
        });
    };

    return ins;
}();

var CharityForm = function(){
    var ins = {};

    var component = {
        nextStepButtonClass: ".charity__button_next-step",
        charityWrapClass: ".charity",
        maxStep: 4
    };

    var charityWrap = null;

    var moveToStep = function(nextStep, currentStep){
        charityWrap.removeClass('charity_step_' + currentStep);
        charityWrap.addClass('charity_step_' + nextStep);
        charityWrap.data('step',nextStep);
        charityWrap.attr('data-step',nextStep);
    };

    var hideLoading = function(){
        setTimeout(function() {
            charityWrap.removeClass('loading');
        }, 300);
    };
    
    var checkErrorField = function (){
        var errFields = charityWrap.find('.form__input.error');
        if(errFields.length != 0){
            var firstErrField = errFields.first();
            var stepWrap = firstErrField.parents('.charity__step');
            if(stepWrap.length !== 0){
                var currentStep = charityWrap.data('step');
                var nextStep = stepWrap.data('step');
                moveToStep(nextStep, currentStep);

                let centerErrorScrollTop = firstErrField.offset().top - ($(window).height()/2) + (firstErrField.outerHeight()/2);
                $("html,body").animate({ scrollTop: centerErrorScrollTop }, 500, 'swing');

                var charityId = $("#field_charity_id");
                if(charityId.length != 0){
                    var autoCompVal = charityId.val();
                    $("#charity_id_autocomp").val(autoCompVal).attr('title', autoCompVal).trigger('change');
                }
            }
        }
        hideLoading();
    }
    ins.init = function(){
        charityWrap = $(component.charityWrapClass);
        if(charityWrap.length == 0){
            return;
        }
        charityWrap.addClass('loading');

        $(document).on('click', component.nextStepButtonClass, function(){
            event.preventDefault();
            charityWrap.addClass('loading');
            var currentStep = charityWrap.data('step');
            var nextStep = currentStep + 1;
            if(nextStep < component.maxStep){
                moveToStep(nextStep, currentStep);
                hideLoading();
            }
        });

        checkErrorField();
    };

    return ins;
}();

var RelistingDetail = function(){
    var ins = {};
    var component = {
        detailContactForm: $(".relisting-detail__contact-form-wrap .contact__form-wrap")
    };
 
    ins.init = function (){
        if(component.detailContactForm.length == 0){
            return;
        }

        if(component.detailContactForm.hasClass('contact__form-wrap_type_thankyou')){
            let centerErrorScrollTop = parseInt(component.detailContactForm.offset().top - ($(window).height()/2)) + (component.detailContactForm.outerHeight()/2);
            $("html,body").animate({ scrollTop: centerErrorScrollTop }, 500, 'swing');
        }
    }

    return ins;
}();