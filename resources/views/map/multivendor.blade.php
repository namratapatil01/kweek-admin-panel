@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.live_tracking_multivendor')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>
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
                                <input type="text" name="search" id="search" class="form-control" style="width:90%" placeholder="Search Driver">
                            </div>

                            <div class="live-tracking-list">

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-8">

                        <div id="map" style="height:450px"></div>

                        <div id="legend">
                            <h3>Legend</h3>
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

        #legend {
            font-family: Arial, sans-serif;
            background: #fff;
            padding: 10px;
            margin: 11px;
            border: 1px solid #000;
        }

        #legend h3 {
            margin-top: 0;
        }

        #legend img {
            vertical-align: middle;
        }
    </style>

    @endsection

    @section('scripts')

    <script type="text/javascript">

        var database = kweekDb();

        var map;
        var marker;
        var markers = [];
        var map_data = [];
        var base_url = '{!! asset('/images/') !!}';
        var mapType = 'ONLINE';
        var globalDrivers = {};
        var mapDataUrl = '/map/multivendor/data';
        var godsEyeMapReady = false;
        var mapDataLoaded = false;

        function getDefaultMapCenter() {
            var default_lat = parseFloat(getCookie('default_latitude'));
            var default_lng = parseFloat(getCookie('default_longitude'));
            if (isNaN(default_lat) || isNaN(default_lng) || (Math.abs(default_lat) < 0.1 && Math.abs(default_lng) < 0.1)) {
                return { lat: 23.0225, lng: 72.5714 }; // Ahmedabad fallback
            }
            return { lat: default_lat, lng: default_lng };
        }

        function fetchMultivendorMapData() {
            if (mapDataLoaded) {
                return;
            }
            mapDataLoaded = true;

            $.ajax({
                url: mapDataUrl,
                method: 'GET',
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var orders = [];
                    var drivers = [];
                    var ordersDriverIds = [];

                    (res.orders || []).forEach(function (order) {
                        order.flag = 'in_transit';
                        orders.push(order);
                        if (order.driver && order.driver.id) {
                            ordersDriverIds.push(order.driver.id);
                        }
                    });

                    (res.drivers || []).forEach(function (driver) {
                        driver.flag = 'available';
                        if (!driver.location) {
                            driver.location = {
                                latitude: driver.latitude,
                                longitude: driver.longitude
                            };
                        }
                        globalDrivers[driver.id] = driver;
                        if ($.inArray(driver.id, ordersDriverIds) === -1) {
                            drivers.push(driver);
                        }
                    });

                    window.globalDrivers = globalDrivers;
                    $(".live-tracking-list").empty();
                    loadData($.merge(orders, drivers)).then(function () {
                        fitMapToMarkers();
                    });
                },
                error: function (xhr) {
                    mapDataLoaded = false;
                    console.error('Failed to load multivendor map data', xhr && xhr.status, xhr && xhr.responseText);
                    $(".live-tracking-list").html('<div class="p-3 text-danger">Failed to load drivers. Please refresh.</div>');
                }
            });
        }

        function fitMapToMarkers() {
            var points = [];
            markers.forEach(function (m) {
                if (!m) return;
                if (mapType === 'OFFLINE' && m.getLatLng) {
                    points.push(m.getLatLng());
                } else if (m.getPosition) {
                    points.push(m.getPosition());
                }
            });
            if (!points.length || !map) {
                return;
            }
            if (mapType === 'OFFLINE') {
                var bounds = L.latLngBounds(points);
                map.fitBounds(bounds.pad(0.2));
            } else if (window.google && google.maps) {
                var bounds = new google.maps.LatLngBounds();
                points.forEach(function (p) { bounds.extend(p); });
                map.fitBounds(bounds);
            }
        }

        $(document).ready(async function () {
            try {
                var snapshots = await database.collection('settings').doc('DriverNearBy').get();
                var data = snapshots.data();
                if (data && data.selectedMapType && data.selectedMapType == "osm") {
                    mapType = "OFFLINE";
                }
            } catch (e) {
                console.warn('DriverNearBy settings load failed, using default map type', e);
            }

            // Wait briefly for Google Maps if needed; otherwise use OSM (already loaded in layout).
            if (mapType !== "OFFLINE") {
                for (var wait = 0; wait < 40; wait++) {
                    if (typeof google !== 'undefined' && google.maps) {
                        break;
                    }
                    await new Promise(function (resolve) { setTimeout(resolve, 100); });
                }
                if (typeof google === 'undefined' || !google.maps) {
                    mapType = "OFFLINE";
                }
            }

            try {
                InitializeGodsEyeMap();
            } catch (e) {
                console.error('Map init failed, falling back to OSM', e);
                mapType = "OFFLINE";
                godsEyeMapReady = false;
                InitializeGodsEyeMap();
            }

            fetchMultivendorMapData();

            setTimeout(function () {
                $(".sidebartoggler").click();
            }, 500);

            $(document).on("click", ".ride-list .track-from", function () {
                var lat = $(this).data('lat');
                var lng = $(this).data('lng');
                var index = $(this).data('index');
                if (mapType == "OFFLINE" ){
                    map.setView([lat, lng], map.getZoom());
                    if(markers[index]){
                       markers[index].openPopup();
                    } else {
                       console.log("Marker at index " + index + " is undefined.");
                    }
                } else{
                    map.panTo(new google.maps.LatLng(lat, lng));
                    google.maps.event.trigger(markers[index], 'click');
                }
            });

            $("#search").keyup(function() {
                var filter = $(this).val(),
                count = 0;
                $('.live-tracking-list .live-tracking-box').each(function() {
                    if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                        count++;
                    }
                });
            });
        });

        function InitializeGodsEyeMap() {
            // Layout also calls this after Google/Leaflet script load — do not wipe an existing map.
            if (godsEyeMapReady && map) {
                return;
            }

            var center = getDefaultMapCenter();
            var legend = document.getElementById('legend');

            if (mapType == "OFFLINE" || typeof google === 'undefined' || !google.maps) {
                mapType = "OFFLINE";

                if (map && map.remove) {
                    map.remove();
                }
                if (typeof L !== 'undefined' && L.DomUtil.get('map') != null) {
                    L.DomUtil.get('map')._leaflet_id = null;
                }

                map = L.map('map').setView([center.lat, center.lng], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
            } else {
                var myLatlng = new google.maps.LatLng(center.lat, center.lng);
                var mapOptions = {
                    zoom: 10,
                    center: myLatlng,
                    streetViewControl: false,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                map = new google.maps.Map(document.getElementById("map"), mapOptions);
            }
            var mapDiv = L.DomUtil.get('map');
            if (mapDiv) {
                mapDiv._leaflet_id = null;
            }

            map = L.map('map').setView([default_lat, default_lng], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            godsEyeMapReady = true;

            var fliter_icons = {
                available: {
                    name: 'Available',
                    icon: base_url + '/available.png'
                },
                ontrip: {
                    name: 'In Transit',
                    icon: base_url + '/ontrip.png'
                }
            };

            legend.innerHTML = "<h3>Legend</h3>";
            for (var key in fliter_icons) {
                var type = fliter_icons[key];
                var name = type.name;
                var icon = type.icon;
                var div = document.createElement('div');
                div.innerHTML = '<img src="' + icon + '" style="width:14px; margin-right:5px;"> ' + name;
                legend.appendChild(div);
            }

            var lmaplegend = L.control({ position: 'bottomleft' });
            lmaplegend.onAdd = function (map) {
                var div = L.DomUtil.create('div', 'legend');
                div.innerHTML = "<h4>Map Legend</h4>";
                div.appendChild(legend);
                return div;
            };
            lmaplegend.addTo(map);
        }

        async function loadData(data) {
            markers = [];
            for (let i = 0; i < data.length; i++) {
                val = data[i];

                var html = '';
                var driverId='';
                var userId='';
                if (val.flag == "in_transit") {
                     if(val.hasOwnProperty('driver') && val.driver){
                         driverId = val.driver.id;
                     }
                } else {
                     driverId = val.id;
                }

                let driver = await getDriverDetail(driverId);
                if(driver!='' && driver != undefined){
                    if (!(driver && driver.location &&
                        driver.location.latitude != null &&
                        driver.location.longitude != null &&
                        !isNaN(parseFloat(driver.location.latitude)) &&
                        !isNaN(parseFloat(driver.location.longitude)))) {
                        continue;
                    }

                    driver.location.latitude = parseFloat(driver.location.latitude);
                    driver.location.longitude = parseFloat(driver.location.longitude);

                    if (val.flag == "in_transit") {
                        if(val.hasOwnProperty('author') && val.author){
                            userId=val.author.id;
                        }

                        let user = userId ? await getUserDetail(userId) : undefined;

                        if (user != undefined && user!='') {
                            html += '<div class="live-tracking-box track-from" data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '">';
                            html += '<div class="live-tracking-inner">';
                            html += '<span class="listicon"></span>';
                            html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                            if (user.firstName || user.lastName) {
                                html += '<h4 class="user-name">{{trans("lang.user_name")}} : ' + user.firstName + ' ' + user.lastName + '</h4>';
                            }
                            html += '<span class="badge badge-danger">In Transit</span>';
                            html += '&nbsp;&nbsp;<a href="/orders/edit/' + val.id + '" class="badge badge-info" target="_blank">{{trans("lang.order_id")}} : ' + String(val.id).substring(0, 7) + '</a>';
                            html += '</div>';
                            html += '</div>';
                        }
                    } else {
                        if (driver.firstName || driver.lastName) {
                            html += '<div class="live-tracking-box track-from" data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '">';
                            html += '<div class="live-tracking-inner">';
                            html += '<span class="listicon"></span>';
                            html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                            html += '<span class="badge badge-success">Available</span>';
                            html += '</div>';
                            html += '</div>';
                        }
                    }
                }

                if (html) {
                    $(".live-tracking-list").append(html);
                }

                if(driver!=undefined && driver!=''){
                    if (typeof driver.location.latitude != 'undefined' && typeof driver.location.longitude != 'undefined') {
                        let iconImg = '';
                        if (val.flag == "available") {
                            iconImg = base_url + '/car_available.png';
                        } else {
                            iconImg = base_url + '/car_on_trip.png';
                        }
                        var content = `
                        <div class="p-2">
                            <h6>{{trans('lang.driver_name')}} : ${(driver.firstName || '') + " " + (driver.lastName || '')} </h6>
                            <h6>{{trans('lang.phone')}} : ${driver.phoneNumber ?? '-'} </h6>
                        </div>`;
                        if (mapType == "OFFLINE" ){
                            var customIcon = L.icon({
                                iconUrl: iconImg,
                                iconSize: [25, 25],
                            });
                            let marker = L.marker([driver.location.latitude, driver.location.longitude], { icon: customIcon }).addTo(map);
                            marker.bindPopup(content);
                            markers[i] = marker;
                            marker.on('click', function () {
                                marker.openPopup();
                            });
                            setInterval(function () {
                                locationUpdate(marker, driver);
                            }, 10000);
                        } else{
                            let marker = new google.maps.Marker({
                                position: new google.maps.LatLng(driver.location.latitude, driver.location.longitude),
                                icon: {
                                    url: iconImg,
                                    scaledSize: new google.maps.Size(25, 25)
                                },
                                map: map
                            });
                            let infowindow = new google.maps.InfoWindow({
                                content: content
                            });
                            marker.addListener('click', function () {
                                infowindow.open(map, marker);
                            });
                            markers[i] = marker;
                            marker.setMap(map);
                            setInterval(function () {
                                locationUpdate(marker, driver);
                            }, 10000);
                        }
                    }
                }
            }
        }

        async function locationUpdate(marker, driver) {
            if (window.globalDrivers && window.globalDrivers[driver.id] && window.globalDrivers[driver.id].location) {
                var loc = window.globalDrivers[driver.id].location;
                if (mapType == "OFFLINE" ){
                    marker.setLatLng([loc.latitude, loc.longitude]);
                } else{
                    marker.setPosition(new google.maps.LatLng(loc.latitude, loc.longitude));
                }
                return;
            }
            database.collection("users").doc(driver.id).get().then((doc) => {
                let data = doc.data();
                if(data && data.location && data.location.latitude && data.location.longitude ){
                    if (mapType == "OFFLINE" ){
                        marker.setLatLng([data.location.latitude, data.location.longitude]);
                    } else{
                        marker.setPosition(new google.maps.LatLng(data.location.latitude, data.location.longitude));
                    }
                }
            });
        }

        async function getUserDetail(userId) {
            if(userId!=''){
                 return database.collection("users").doc(userId).get().then((doc) => {
                 return doc.data();
            });
            }
        }

        async function getDriverDetail(driverId) {
            if (!driverId) {
                return undefined;
            }
            if (window.globalDrivers && window.globalDrivers[driverId]) {
                return window.globalDrivers[driverId];
            }
            return database.collection("users").doc(driverId).get().then((doc) => {
                var data = doc.data();
                if (!data) {
                    return undefined;
                }
                if ((!data.location || data.location.latitude == null) && (data.latitude != null || data.longitude != null)) {
                    data.location = {
                        latitude: parseFloat(data.latitude),
                        longitude: parseFloat(data.longitude)
                    };
                }
                return data;
            });
        }

    </script>

    @endsection
