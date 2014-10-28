(function(_, $) {
    (function($) {

        var map = null;
        var saved_point = null;
        var marker = null;
        var map_params = null;

        var latitude = 0;
        var longitude = 0;
        var zoom = 0;

        var latitude_name = '';
        var longitude_name = '';
        var map_container = '';

        function updatePoint(point) {
            if (saved_point && marker) {
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                position: point,
                map: map
            });

            marker.setMap(map);
            saved_point = point;
        }

        function addMapListeners() {
            google.maps.event.addListener(map, 'click', function(event) {
                updatePoint(event.latLng);
            });
        }

        var methods = {

            init: function(options, callback) {

                if (!('google' in window)) {
                    $.getScript('//www.google.com/jsapi', function() {
                        setTimeout(function() { // do not remove it - otherwise it will be slow in ff
                            google.load('maps', '3.0', {
                                other_params: "sensor=false&language=" + options.language,
                                callback: function() {
                                    $.ceMap('init', options, callback);
                                }
                            });
                        }, 0);
                    });

                    return false;
                }

                latitude = options.latitude;
                longitude = options.longitude;
                map_container = options.map_container;

                storeData = options.storeData;
                zoom = options.zoom;

                // Required fields - zoom, mapTypeId, center
                map_params = {
                    zoomControl: true,
                    scaleControl: true,
                    streetViewControl: false,
                    mapTypeControl: false,
                    zoom: 12,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    center: new google.maps.LatLng(latitude, longitude)
                }

                if (_.area == 'A') {
                    $.extend(map_params, {
                        draggableCursor: 'crosshair',
                        draggingCursor: 'pointer'
                    });
                } else {
                    $.extend(map_params, {
                        zoom: zoom,
                        zoomControl: options.zoom_control,
                        mapTypeControl: options.map_type_control,
                        scaleControl: options.scale_control,
                        streetViewControl: options.street_view_control,
                    });
                }

                if (typeof(callback) == 'function') {
                    callback();
                }
            },

            showDialog: function(country_field, city_field, latitude_field, longitude_field) {

                var params_dialog = {
                    href: "",
                    keepInPlace: false,
                    dragOptimize: true
                };

                $('#map_picker').ceDialog('open', params_dialog);

                saved_point = null;
                marker = null;

                latitude_name = latitude_field;
                longitude_name = longitude_field;

                latitude = $('#' + latitude_name + '_hidden').val();
                longitude = $('#' + longitude_name + '_hidden').val();

                var map_center = null;

                map = new google.maps.Map(document.getElementById(options.map_container), map_params);

                if (latitude && longitude) {
                    map_center = new google.maps.LatLng(latitude, longitude);
                    map.setCenter(map_center);
                    updatePoint(map_center);
                    addMapListeners();

                } else if ($('#' + city_field).val() || $('#' + country_field).val()) {
                    var address = '';
                    var value = $('#' + city_field).val();
                    if (value) {
                        var city = value;
                        address = value;
                    }

                    var value = $('#' + country_field).val();
                    if (value) {
                        if (address) {
                            address += ' ';
                        }

                        address += value;
                    }

                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        'address': address
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (city && city.length) {
                                map.setZoom(10);
                            }

                            $('#' + map_container).show();
                            map_center = results[0].geometry.location;
                            map.setCenter(map_center);
                            addMapListeners();
                        } else {
                            fn_alert($.tr('text_address_not_found') + ': ' + address);
                        }
                    });

                } else {
                    map_center = new google.maps.LatLng(latitude, longitude);
                    map.setCenter(map_center);
                    updatePoint(map_center);
                    addMapListeners();
                }
            },

            show: function(options) {

                if (!map_params) {
                    return $.ceMap('init', options, function() {
                        $.ceMap('show', options);
                    });
                }

                map = new google.maps.Map(document.getElementById(options.map_container), map_params);

                bounds = new google.maps.LatLngBounds();
                markers = Array();
                infoWindows = Array();

                var marker;
                var i = 0;

                for (var keyvar = 0; keyvar < storeData.length; keyvar++) {
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(storeData[keyvar]['latitude'], storeData[keyvar]['longitude']),
                        map: map,
                        infoWindowIndex: i
                    });

                    marker.setMap(map);
                    bounds.extend(marker.position);

                    //balloon content collecting
                    var marker_html = '<div style="padding-right: 10px"><strong>' + storeData[keyvar]['name'] + '</strong><p>';

                    if (storeData[keyvar]['city'] != '') {
                        marker_html += storeData[keyvar]['city'] + ', ';
                    }
                    if (storeData[keyvar]['country_title'] != '') {
                        marker_html += storeData[keyvar]['country_title'];
                    }

                    marker_html += '</p><\/div>';

                    var infowindow = new google.maps.InfoWindow({
                        content: marker_html
                    });

                    google.maps.event.addListener(marker, 'click',
                        function(event) {
                            map.panTo(event.latLng);
                            infoWindows[this.infoWindowIndex].open(map, this);
                        }
                    );

                    infoWindows.push(infowindow);
                    markers.push(marker);
                    i++;
                }

                if (storeData.length == 1) {
                    map.setCenter(marker.getPosition());

                    map.setZoom(zoom);

                } else {
                    map.fitBounds(bounds);
                }
            },

            saveLocation: function() {
                if (saved_point) {
                    $('#' + latitude_name).val(saved_point.lat());
                    $('#' + latitude_name + '_hidden').val(saved_point.lat());
                    $('#' + longitude_name).val(saved_point.lng());
                    $('#' + longitude_name + '_hidden').val(saved_point.lng());
                }

                saved_point = null;
            },

            viewLocation: function(latitude, longitude) {
                var latLng = new google.maps.LatLng(latitude, longitude);
                map.setCenter(latLng);
                map.setZoom(zoom);
            },

            viewLocations: function() {
                map.fitBounds(bounds);
            }
        }

        $.extend({
            ceMap: function(method) {
                if (methods[method]) {
                    return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
                } else {
                    $.error('ty.map: method ' + method + ' does not exist');
                }
            }
        });

    })($);

    $(document).ready(function() {

        $('.cm-map-dialog').on('click', function() {
            $.ceMap('showDialog', 'elm_country', 'elm_city', 'elm_latitude', 'elm_longitude');
        });

        $('.cm-map-save-location').on('click', function() {
            $.ceMap('saveLocation');
        });

        $('.cm-map-view-location').on('click', function() {
            var jelm = $(this);
            var latitude = jelm.data('ca-latitude');
            var longitude = jelm.data('ca-longitude');

            $.ceMap('viewLocation', latitude, longitude);
        });

        $('.cm-map-view-locations').on('click', function() {
            $.ceMap('viewLocations');
        });

    });

}(Tygh, Tygh.$));