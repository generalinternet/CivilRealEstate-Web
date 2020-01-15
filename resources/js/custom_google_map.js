var geocoder;
var map;

var mapOptions = {
    scrollwheel: false,
    zoom: 14,
    styles: [{
    "featureType":"all",
    "elementType":"labels.text.fill",
    "stylers":[ //stylers color the map
        {"saturation":36},
        {"color":"#000000"},
        {"lightness":55}
    ]},
    {
        "featureType":"all",
        "elementType":"labels.text.stroke",
        "stylers":[
            {"visibility":"on"},
            {"color":"#000000"},
            {"lightness":18}
        ]
    },
    {
        "featureType":"all",
        "elementType":"labels.icon",
        "stylers":[
            {"visibility":"off"}
        ]
    },
    {
        "featureType":"administrative",
        "elementType":"geometry.fill",
        "stylers":[
            {"color":"#000000"},
            {"lightness":28}
        ]
    },
    {
        "featureType":"administrative",
        "elementType":"geometry.stroke",
        "stylers":[
            {"color":"#000000"},
            {"lightness":18},
            {"weight":1.2}
        ]
    },
    {
        "featureType":"landscape",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":22}
        ]
    },
    {
        "featureType":"poi",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":21}
        ]
    },
    {
        "featureType":"road.highway",
        "elementType":"geometry.fill",
        "stylers":[
            {"color":"#000000"},
            {"lightness":18}
        ]
    },
    {
        "featureType":"road.highway",
        "elementType":"geometry.stroke",
        "stylers":[
            {"color":"#000000"},
            {"lightness":25},
            {"weight":0.2}
        ]
    },
    {
        "featureType":"road.arterial",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":16}
        ]
    },
    {
        "featureType":"road.local",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":19}
        ]
    },
    {
        "featureType":"transit",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":22}
        ]
    },
    {
        "featureType":"water",
        "elementType":"geometry",
        "stylers":[
            {"color":"#000000"},
            {"lightness":18}
        ]
    }]
};
if (typeof google !== 'undefined') {
    google.maps.event.addDomListener(window, "load", googleMapInit);
}
function googleMapInit() {
    var mapElement = document.getElementById("google_map");
    if (mapElement != undefined) {
        geocoder = new google.maps.Geocoder();
        var markerTitle = '';
        var centerLatlng = new google.maps.LatLng(49.0606704, -122.4981467);
        var markerLatlng = new google.maps.LatLng(49.0606704, -122.4981467);
        var mapPin = {
            url: "resources/media/img/art/icon_location_mark.png", /*path to pin image*/
        };
        
        var title = mapElement.dataset.title;
        if (title != undefined) {
            markerTitle = title;
        }

        var address = mapElement.dataset.addr;
        if (address != undefined) {
            //Get coordinates by address
            geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == 'OK') {
                var resultLatlng = results[0].geometry.location;
                centerLatlng = resultLatlng;
                markerLatlng = resultLatlng;
                mapOptions.center = centerLatlng;

                map = new google.maps.Map(mapElement, mapOptions);

                var marker = new google.maps.Marker({
                    map: map,
                    position: markerLatlng,
                    title: markerTitle,
                    icon: mapPin
                });
            } else {
                var mapWrap = $('#google_map').closest('.google_map_wrap');
                //mapWrap.hide();
                $('#google_map').html('<div class="error_message"><p class="content_block">'+status+'</p></div>');
                console.log('Geocode was not successful for the following reason: ' + status);
                return null;
            }
          });
        }
    }//if (mapElement != undefined)
} 