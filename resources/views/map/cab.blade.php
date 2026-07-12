@extends('layouts.app')



@section('content')



<div class="page-wrapper">



    <div class="row page-titles">



        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.live_tracking_of')}} <span class="section_name"></span></h3>

        </div>



        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item">

                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>

                </li>

                <li class="breadcrumb-item">

                    {{trans('lang.cab_service')}}

                </li>

                <li class="breadcrumb-item active">

                    {{trans('lang.god_eye')}}

                </li>

            </ol>

        </div>

    </div>



    <div class="container-fluid">



        <!-- start row -->

        <div class="card mb-3">



            <div class="card-body">



                <div class="row">



                    <div class="col-lg-4">



                        <div class="table-responsive ride-list">

                            <div class="form-group" id="search-box">

                                <input type="text" name="search" id="search" class="form-control" style="width:90%" placeholder="{{trans('lang.search_driver')}}">

                            </div>



                            <div class="live-tracking-list">



                            </div>



                        </div>



                    </div>



                    <div class="col-lg-8">
                        <div class="cab-map-wrap">
                            <div id="map"></div>
                            <div id="legend" class="cab-map-legend"></div>
                        </div>
                    </div>



                </div>



            </div>



        </div>

    </div>



    <style>
        #append_list12 tr {
            cursor: pointer;
        }

        .cab-map-wrap {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        #map {
            width: 100%;
            height: 520px;
            min-height: 450px;
        }

        .cab-map-legend {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 10px 14px;
            margin: 0;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            line-height: 1.8;
            font-size: 13px;
        }

        .cab-map-legend h3 {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .live-tracking-box.track-from {
            cursor: pointer;
        }

        .live-tracking-box.track-from:hover,
        .live-tracking-box.is-active {
            background: #f0fdf4;
            border-color: #22c55e;
        }

        .live-tracking-box .listicon img {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-right: 8px;
        }

        .live-tracking-box .listicon-off {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-right: 8px;
            border-radius: 50%;
            background: #e5e7eb;
            vertical-align: middle;
        }
    </style>



    @endsection



    @section('scripts')

    <script type="text/javascript">
        var database = kweekDb();
        var map;
        var markers = [];
        var markersByDriverId = {};
        var activeInfoWindow = null;
        var base_url = '{!! asset('/images/') !!}';
        var mapType = 'ONLINE';
        var section_id = getCookie('section_id') || '';
        var pendingMapData = null;

        $(document).ready(function () {
            database.collection('sections').where('id', '==', section_id).get().then(function (snapshots) {
                if (snapshots.docs.length > 0) {
                    snapshots.docs.forEach(function (doc) {
                        $('.section_name').text(doc.data().name);
                    });
                }
            });

            database.collection('settings').doc('DriverNearBy').get().then(function (snapshots) {
                var data = snapshots.data();
                if (data && data.selectedMapType && data.selectedMapType == "osm") {
                    mapType = "OFFLINE";
                }
            });

            var orders = [];
            var drivers = [];
            var ordersDriverIds = [];
            var globalDrivers = {};

            $.get("{{ route('map.cab.data') }}", { section_id: section_id }, function (res) {
                (res.rides || []).forEach(function (ride) {
                    ride.flag = 'in_transit';
                    orders.push(ride);
                    if (ride.driver && ride.driver.id) {
                        ordersDriverIds.push(ride.driver.id);
                    }
                });

                (res.drivers || []).forEach(function (driver) {
                    driver.flag = 'available';
                    driver.location = {
                        latitude: driver.latitude,
                        longitude: driver.longitude
                    };
                    globalDrivers[driver.id] = driver;
                    if ($.inArray(driver.id, ordersDriverIds) === -1) {
                        drivers.push(driver);
                    }
                });

                window.globalDrivers = globalDrivers;
                pendingMapData = $.merge(orders, drivers);
                if (map) {
                    renderCabMapData(pendingMapData);
                }
            });

            setTimeout(function () {
                $(".sidebartoggler").click();
            }, 500);

            $(document).on("click", ".ride-list .track-from", function () {
                var driverId = $(this).data('driver-id');
                if (driverId) {
                    focusDriverOnMap(driverId);
                    return;
                }

                var lat = parseFloat($(this).data('lat'));
                var lng = parseFloat($(this).data('lng'));
                var index = $(this).data('index');
                if (mapType == "OFFLINE") {
                    map.setView([lat, lng], 15);
                    if (markers[index]) {
                        markers[index].openPopup();
                    }
                } else if (map && markers[index]) {
                    map.setZoom(15);
                    map.panTo(new google.maps.LatLng(lat, lng));
                    google.maps.event.trigger(markers[index], 'click');
                }
            });

            $("#search").keyup(function () {
                var filter = $(this).val();
                $('.live-tracking-list .live-tracking-box').each(function () {
                    $(this).toggle($(this).text().search(new RegExp(filter, "i")) >= 0);
                });
            });
        });

        function InitializeGodsEyeMap() {
            var default_lat = parseFloat(getCookie('default_latitude')) || 23.022505;
            var default_lng = parseFloat(getCookie('default_longitude')) || 72.571365;
            var legend = document.getElementById('legend');

            if (mapType == "OFFLINE") {
                map = L.map('map').setView([default_lat, default_lng], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
            } else {
                var myLatlng = new google.maps.LatLng(default_lat, default_lng);
                map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 10,
                    center: myLatlng,
                    streetViewControl: false,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
            }

            var fliter_icons = {
                available: { name: 'Available', icon: base_url + '/available.png' },
                ontrip: { name: 'In Transit', icon: base_url + '/ontrip.png' }
            };

            legend.innerHTML = "<h3>Legend</h3>";
            for (var key in fliter_icons) {
                var type = fliter_icons[key];
                var div = document.createElement('div');
                div.innerHTML = '<img src="' + type.icon + '"> ' + type.name;
                legend.appendChild(div);
            }

            if (mapType == "OFFLINE") {
                var lmaplegend = L.control({ position: 'bottomleft' });
                lmaplegend.onAdd = function () {
                    var div = L.DomUtil.create('div', 'legend');
                    div.innerHTML = "<h4>Map Legend</h4>";
                    div.appendChild(legend);
                    return div;
                };
                lmaplegend.addTo(map);
            } else {
                map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
            }

            if (pendingMapData) {
                renderCabMapData(pendingMapData);
            }
        }

        function hasValidCoords(location) {
            if (!location || location.latitude == null || location.longitude == null) {
                return false;
            }
            var lat = parseFloat(location.latitude);
            var lng = parseFloat(location.longitude);
            if (isNaN(lat) || isNaN(lng)) {
                return false;
            }
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                return false;
            }
            return !(Math.abs(lat) < 0.1 && Math.abs(lng) < 0.1);
        }

        function distanceKm(lat1, lng1, lat2, lng2) {
            var R = 6371;
            var dLat = (lat2 - lat1) * Math.PI / 180;
            var dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function shouldShowOnMap(lat, lng) {
            var centerLat = parseFloat(getCookie('default_latitude')) || 23.022505;
            var centerLng = parseFloat(getCookie('default_longitude')) || 72.571365;
            return distanceKm(centerLat, centerLng, lat, lng) <= 400;
        }

        function focusDriverOnMap(driverId) {
            var marker = markersByDriverId[driverId];
            if (!marker || !map) {
                return;
            }

            $('.live-tracking-box').removeClass('is-active');
            $('.live-tracking-box[data-driver-id="' + driverId + '"]').addClass('is-active');

            if (mapType == "OFFLINE") {
                var latLng = marker.getLatLng();
                map.setView(latLng, 15);
                marker.openPopup();
                return;
            }

            map.setZoom(15);
            map.panTo(marker.getPosition());
            if (activeInfoWindow) {
                activeInfoWindow.close();
            }
            if (marker.__infowindow) {
                marker.__infowindow.open(map, marker);
                activeInfoWindow = marker.__infowindow;
            }
        }

        function fitMapToMarkers() {
            var validMarkers = Object.keys(markersByDriverId).map(function (id) {
                return markersByDriverId[id];
            }).filter(function (m) { return !!m; });
            if (!validMarkers.length || !map) {
                return;
            }

            if (mapType == "OFFLINE") {
                var group = L.featureGroup(validMarkers);
                map.fitBounds(group.getBounds(), { padding: [40, 40] });
                return;
            }

            var bounds = new google.maps.LatLngBounds();
            validMarkers.forEach(function (marker) {
                bounds.extend(marker.getPosition());
            });
            map.fitBounds(bounds, 48);
        }

        async function renderCabMapData(data) {
            $(".live-tracking-list").empty();
            markers = [];
            markersByDriverId = {};

            for (let i = 0; i < data.length; i++) {
                var val = data[i];
                var html = '';
                var driverId = '';
                var hasCoords = false;
                var showOnMap = false;

                if (val.flag == "in_transit") {
                    if (val.driver && val.driver.id) {
                        driverId = val.driver.id;
                    }
                } else {
                    driverId = val.id;
                }

                var driver = await getDriverDetail(driverId);
                if (!driver) {
                    continue;
                }

                if (!driver.location) {
                    driver.location = {
                        latitude: driver.latitude,
                        longitude: driver.longitude
                    };
                }

                hasCoords = hasValidCoords(driver.location);
                if (hasCoords) {
                    driver.location.latitude = parseFloat(driver.location.latitude);
                    driver.location.longitude = parseFloat(driver.location.longitude);
                    showOnMap = shouldShowOnMap(driver.location.latitude, driver.location.longitude);
                }

                if (val.flag == "in_transit") {
                    if (!hasCoords || !showOnMap) {
                        continue;
                    }
                    var user = val.author && val.author.id ? await getUserDetail(val.author.id) : null;
                    if (user) {
                        html += '<div class="live-tracking-box track-from" data-driver-id="' + driverId + '" data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '" title="Click to view on map">';
                        html += '<div class="live-tracking-inner">';
                        html += '<span class="listicon"><img src="' + base_url + '/car_on_trip.png" alt=""></span>';
                        html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                        if (user.firstName || user.lastName) {
                            html += '<h4 class="user-name">{{trans("lang.user_name")}} : ' + user.firstName + ' ' + user.lastName + '</h4>';
                        }
                        html += '<span class="badge badge-danger">In Transit</span>';
                        html += '&nbsp;&nbsp;<a href="/rides/edit/' + val.id + '" class="badge badge-info" target="_blank">{{trans("lang.order_id")}} : ' + String(val.id).substring(0, 7) + '</a>';
                        html += '</div></div>';
                    }
                } else if (driver.firstName || driver.lastName) {
                    var listIconHtml = showOnMap
                        ? '<span class="listicon"><img src="' + base_url + '/car_available.png" alt=""></span>'
                        : '<span class="listicon listicon-off"></span>';
                    html += '<div class="live-tracking-box' + (showOnMap ? ' track-from' : '') + '" data-driver-id="' + driverId + '"' +
                        (showOnMap ? ' data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '" title="Click to view on map"' : '') + '>';
                    html += '<div class="live-tracking-inner">';
                    html += listIconHtml;
                    html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                    html += '<span class="badge badge-success">Available</span>';
                    html += '</div></div>';
                }

                if (html) {
                    $(".live-tracking-list").append(html);
                }

                if (showOnMap && map) {
                    var iconImg = val.flag == "available" ? base_url + '/car_available.png' : base_url + '/car_on_trip.png';
                    var content = '<div class="p-2">' +
                        '<h6>{{trans("lang.driver_name")}} : ' + (driver.firstName || '') + ' ' + (driver.lastName || '') + '</h6>' +
                        '<h6>{{trans("lang.phone")}} : ' + (driver.phoneNumber || '-') + '</h6>' +
                        '<h6>{{trans("lang.car_number")}} : ' + (driver.carNumber || '-') + '</h6>' +
                        '<h6>{{trans("lang.car_name")}} : ' + (driver.carName || '-') + '</h6>' +
                        '</div>';

                    if (mapType == "OFFLINE") {
                        var customIcon = L.icon({ iconUrl: iconImg, iconSize: [36, 36] });
                        var marker = L.marker([driver.location.latitude, driver.location.longitude], { icon: customIcon }).addTo(map);
                        marker.bindPopup(content);
                        markers[i] = marker;
                        markersByDriverId[driverId] = marker;
                    } else {
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(driver.location.latitude, driver.location.longitude),
                            icon: { url: iconImg, scaledSize: new google.maps.Size(36, 36) },
                            map: map,
                            title: (driver.firstName || '') + ' ' + (driver.lastName || '')
                        });
                        var infowindow = new google.maps.InfoWindow({ content: content });
                        marker.__infowindow = infowindow;
                        marker.addListener('click', function () {
                            if (activeInfoWindow) {
                                activeInfoWindow.close();
                            }
                            infowindow.open(map, marker);
                            activeInfoWindow = infowindow;
                            $('.live-tracking-box').removeClass('is-active');
                            $('.live-tracking-box[data-driver-id="' + driverId + '"]').addClass('is-active');
                        });
                        markers[i] = marker;
                        markersByDriverId[driverId] = marker;
                    }
                }
            }

            fitMapToMarkers();
        }

        async function getUserDetail(userId) {
            if (userId != '') {
                return database.collection("users").doc(userId).get().then(function (doc) {
                    return doc.data();
                });
            }
        }

        async function getDriverDetail(driverId) {
            if (driverId != '') {
                return window.globalDrivers ? window.globalDrivers[driverId] : null;
            }
        }
    </script>

    @endsection

