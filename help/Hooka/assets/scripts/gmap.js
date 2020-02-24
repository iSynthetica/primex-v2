(function($){
    $(document).ready(function(){
        init_map();
    });
})(jQuery); // End of use strict

/* ---------------------------------------------
 Google map
 --------------------------------------------- */

var gmMapDiv = $("#map-canvas");

function init_map(){
    (function($){

        $(".map-section").click(function(){
            $(this).toggleClass("js-active");
            $(this).find(".mt-open").toggle();
            $(this).find(".mt-close").toggle();
        });


        if (gmMapDiv.length) {

            var gmCenterAddress = gmMapDiv.attr("data-address");
            var gmMarkerAddress = gmMapDiv.attr("data-address");


            gmMapDiv.gmap3({
                action: "init",
                marker: {
                    address: gmMarkerAddress,
                    options: {
                        icon: jointsMapObj.icon
                    }
                },
                map: {
                    options: {
                        zoom: 14,
                        zoomControl: true,
                        zoomControlOptions: {
                            style: google.maps.ZoomControlStyle.SMALL,
                            position: google.maps.ControlPosition.LEFT_TOP
                        },
                        mapTypeControl: false,
                        scaleControl: false,
                        scrollwheel: false,
                        streetViewControl: false,
                        draggable: true,
                        styles:
                        [
                            {
                                "featureType":"water",
                                "elementType":"geometry.fill",
                                "stylers":[{"color":"#d3d3d3"}]
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
                                "featureType":"landscape",
                                "elementType":"geometry.fill",
                                "stylers":[{"visibility":"on"},{"color":"#efefef"}]
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

                            },{
                                "featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#dadada"}]
                            }
                        ]
                    }
                }
            });
        }
    })(jQuery);
}