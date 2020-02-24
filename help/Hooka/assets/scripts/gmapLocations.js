(function($) {
    $(document).ready(function() {

        $(".map-section").click(function(){
            $(this).toggleClass("js-active");
            $(this).find(".mt-open").toggle();
            $(this).find(".mt-close").toggle();
        });

        $('#map-canvas').each(function(){
            map = new_map( $(this) );
        });
    });

    /**
     * Create Google Map
     *
     * @param $el
     * @returns {google.maps.Map}
     */
    function new_map($el) {
        let $markers = jointsMapObj.markers;
        let centerLat = jointsMapObj.center.lat;
        let centerLng = jointsMapObj.center.lng;

        let args = {
            zoom		        : parseInt(jointsMapObj.zoom),
            center		        : new google.maps.LatLng(centerLat, centerLng),
            styles              : getMapStyle(),
            mapTypeId	        : google.maps.MapTypeId.ROADMAP
        };

        let map = new google.maps.Map( $el[0], args);

        map.markers = [];

        let myOptions = {
            disableAutoPan: false,
            maxWidth: 0,
            pixelOffset: new google.maps.Size(0, -60),
            boxStyle: {
                padding: "0px 0px 0px 0px",
                width: "250px",
                height: "auto"
            },
            infoBoxClearance: new google.maps.Size(1, 1),
            pane: "floatPane",
            alignBottom: true,
            closeBoxMargin: "6px 6px 2px 2px",
            enableEventPropagation: true
        };

        let ib = new InfoBox(myOptions);

        var markers = [];

        $.each( $markers, function( index, value ){
            var marker  = add_marker( value, map, ib );

            markers.push(marker);
        });

        var markerCluster = new MarkerClusterer(map, markers, {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});

        return map;
    }

    /**
     * Adding Markers
     *
     * @param $marker
     * @param map
     * @param ib
     */
    function add_marker($marker, map, ib ) {
        let latlng = new google.maps.LatLng( $marker.marker.lat, $marker.marker.lng );
        let $icon = jointsMapObj.icon;

        let marker = new google.maps.Marker({
            position	: latlng,
            map			: map,
            label: {
                text: $marker.title,
                color: "#000",
                fontSize: "12px"
            },
            icon: {
                url: $icon,
                size: new google.maps.Size(60, 60),
                anchor: new google.maps.Point(30,50),
                origin: new google.maps.Point(0, 0),
                labelOrigin:  new google.maps.Point(30,2),
            }
        });

        map.markers.push( marker );

        if($marker.info) {
            google.maps.event.addListener(marker, 'click', function(e) {
                // map.clear();
                ib.setContent($marker.info);
                ib.open(map, this);
                map.panTo(ib.getPosition());
            });

            google.maps.event.addListener(ib, 'closeclick', function(e) {
                //center_map( map );
            });
        }

        return marker;
    }

    var getMapStyle = function() {
        return [
            {
                "stylers": [
                    {
                        "saturation": -100
                    }
                ]
            },
            {
                "featureType": "poi",
                "stylers": [
                    {
                        "visibility": "off"
                    }
                ]
            }
        ];
    }

    var getMapStyleOld = function() {
        return [
            {
                "featureType":"water",
                "elementType":"geometry.fill",
                "stylers":[{"color":"#d3d3d3"}]
            },{
                "featureType":"landscape",
                "elementType":"geometry.fill",
                "stylers":[{"visibility":"on"},{"color":"#efefef"}]
            },{
                "featureType":"transit",
                "stylers":[{"color":"#808080"},{"visibility":"off"}]
            },{
                "featureType":"road.highway",
                "elementType":"geometry.stroke",
                "stylers":[{"visibility":"on"},{"color":"#b3b3b3"}]
            },{
                "featureType":"road.highway",
                "elementType":"geometry.fill",
                "stylers":[{"color":"#ffffff"}]
            },{
                "featureType":"road.local",
                "elementType":"geometry.fill",
                "stylers":[{"visibility":"on"},{"color":"#ffffff"},{"weight":1.8}]
            },{
                "featureType":"road.local",
                "elementType":"geometry.stroke","stylers":[{"color":"#d7d7d7"}]
            },{
                "featureType":"poi",
                "elementType":"geometry.fill",
                "stylers":[{"visibility":"on"},{"color":"#ebebeb"}]
            },{
                "featureType":"administrative",
                "elementType":"geometry",
                "stylers":[{"color":"#a7a7a7"}]
            },{
                "featureType":"road.arterial",
                "elementType":"geometry.fill",
                "stylers":[{"color":"#ffffff"}]
            },{
                "featureType":"road.arterial",
                "elementType":"geometry.fill",
                "stylers":[{"color":"#ffffff"}]
            },{
                "featureType":"road",
                "elementType":"labels.text.fill",
                "stylers":[{"color":"#696969"}]
            },{
                "featureType":"administrative",
                "elementType":"labels.text.fill",
                "stylers":[{"visibility":"on"},{"color":"#737373"}]
            },{
                "featureType":"poi",
                "elementType":"labels.icon",
                "stylers":[{"visibility":"off"}]
            },{
                "featureType":"poi",
                "elementType":"labels",
                "stylers":[{"visibility":"off"}]
            },{
                "featureType":"road.arterial",
                "elementType":"geometry.stroke",
                "stylers":[{"color":"#d6d6d6"}]
            },{
                "featureType":"road",
                "elementType":"labels.icon",
                "stylers":[{"visibility":"off"}]
            },{
                "featureType":"poi",
                "elementType":"geometry.fill",
                "stylers":[{"color":"#dadada"}]
            }
        ];
    }
})(jQuery);