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

                        <div class="multivendor-map-wrap">
                            <div id="map"></div>
                            <div id="legend" class="multivendor-map-legend"></div>
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

        .multivendor-map-wrap {
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

        .multivendor-map-legend {
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

        .multivendor-map-legend h3 {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .multivendor-map-legend img {
            vertical-align: middle;
            width: 14px;
            margin-right: 5px;
        }

        .live-tracking-box.track-from {
            cursor: pointer;
        }

        .live-tracking-box.track-from:hover {
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

        var map;
        var markers = [];
        var base_url = '{!! asset('/images/') !!}';
        var mapType = 'ONLINE';
        var globalDrivers = {};
        var mapDataUrl = "{{ route('map.food.data') }}";
        var mapSettingsUrl = "{{ route('map.settings') }}";
        var driverLocationsUrl = "{{ route('map.drivers.locations') }}";
        var serviceType = 'delivery-service';
        var section_id = getCookie('section_id') || '3';
        var godsEyeMapReady = false;
        var mapDataLoaded = false;
        var lastMapData = [];
        var pendingMapData = null;
        var locationRefreshTimer = null;

        window.gm_authFailure = function () {
            if (mapType === "OFFLINE") {
                return;
            }
            console.warn('Google Maps failed to load, using OpenStreetMap for food tracking');
            mapType = "OFFLINE";
            godsEyeMapReady = false;
            InitializeFoodMap(true);
            if (lastMapData.length) {
                $(".live-tracking-list").empty();
                renderFoodMapData(lastMapData);
                fitMapToMarkers();
            }
        };

        function hasValidCoords(location) {
            if (!location || location.latitude == null || location.longitude == null) {
                return false;
            }
            var lat = parseFloat(location.latitude);
            var lng = parseFloat(location.longitude);
            if (isNaN(lat) || isNaN(lng)) {
                return false;
            }
            if (lat < -60 || lat > 80 || lng < -180 || lng > 180) {
                return false;
            }
            if (Math.abs(lat) < 0.1 && Math.abs(lng) < 0.1) {
                return false;
            }
            if (Math.abs(lat - 0.01) < 0.001 && Math.abs(lng - 0.01) < 0.001) {
                return false;
            }
            return true;
        }

        function distanceKm(lat1, lng1, lat2, lng2) {
            var dLat = (lat2 - lat1) * Math.PI / 180;
            var dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function getMarkerLatLng(point) {
            if (!point) {
                return null;
            }
            if (typeof point.lat === 'function') {
                return { lat: point.lat(), lng: point.lng() };
            }
            return { lat: parseFloat(point.lat), lng: parseFloat(point.lng) };
        }

        function shouldShowDriverOnMap(lat, lng) {
            return hasValidCoords({ latitude: lat, longitude: lng });
        }

        function getDriverDetail(driverId) {
            if (!driverId) {
                return null;
            }
            return window.globalDrivers ? (window.globalDrivers[driverId] || null) : null;
        }

        function fetchMapSettings() {
            return $.ajax({
                url: mapSettingsUrl,
                method: 'GET',
                dataType: 'json',
                cache: false
            });
        }

        function getDefaultMapCenter() {
            var default_lat = parseFloat(getCookie('default_latitude'));
            var default_lng = parseFloat(getCookie('default_longitude'));
            if (isNaN(default_lat) || isNaN(default_lng) || (Math.abs(default_lat) < 0.1 && Math.abs(default_lng) < 0.1)) {
                return { lat: 23.0225, lng: 72.5714 };
            }
            return { lat: default_lat, lng: default_lng };
        }

        function fetchFoodMapData() {
            if (mapDataLoaded) {
                return;
            }
            mapDataLoaded = true;

            $.ajax({
                url: mapDataUrl,
                method: 'GET',
                data: { section_id: section_id },
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
                    lastMapData = $.merge(orders, drivers);
                    pendingMapData = lastMapData;
                    if (res.section && res.section.name) {
                        $('.section_name').text(res.section.name);
                    }
                    $(".live-tracking-list").empty();
                    renderFoodMapData(pendingMapData);
                    fitMapToMarkers();
                },
                error: function (xhr) {
                    mapDataLoaded = false;
                    console.error('Failed to load food map data', xhr && xhr.status, xhr && xhr.responseText);
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

            var center = getDefaultMapCenter();
            var nearby = points.filter(function (p) {
                var pos = getMarkerLatLng(p);
                return pos && distanceKm(center.lat, center.lng, pos.lat, pos.lng) <= 400;
            });
            if (nearby.length) {
                points = nearby;
            }

            if (mapType === 'OFFLINE') {
                var bounds = L.latLngBounds(points);
                map.fitBounds(bounds.pad(0.15));
                if (map.getZoom() > 14) {
                    map.setZoom(14);
                }
            } else if (window.google && google.maps) {
                var bounds = new google.maps.LatLngBounds();
                points.forEach(function (p) { bounds.extend(p); });
                map.fitBounds(bounds, 48);
                google.maps.event.addListenerOnce(map, 'idle', function () {
                    if (map.getZoom() < 10) {
                        map.setCenter(new google.maps.LatLng(center.lat, center.lng));
                        map.setZoom(11);
                    } else if (map.getZoom() > 15) {
                        map.setZoom(15);
                    }
                });
            }
        }

        $(document).ready(async function () {
            @if(!empty($sectionName))
            $('.section_name').text(@json($sectionName));
            @endif

            try {
                var settingsRes = await fetchMapSettings();
                if (settingsRes && settingsRes.selectedMapType === 'osm') {
                    mapType = 'OFFLINE';
                }
            } catch (e) {
                console.warn('Map settings load failed, using default map type', e);
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
                InitializeFoodMap();
            } catch (e) {
                console.error('Map init failed, falling back to OSM', e);
                mapType = "OFFLINE";
                godsEyeMapReady = false;
                InitializeFoodMap();
            }

            fetchFoodMapData();

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

        function InitializeFoodMap(forceReinit) {
            var canUseGoogle = typeof google !== 'undefined' && google.maps;
            if (godsEyeMapReady && map && !forceReinit) {
                if (mapType === 'OFFLINE' && canUseGoogle) {
                    forceReinit = true;
                } else {
                    return;
                }
            }

            if (forceReinit) {
                godsEyeMapReady = false;
                markers = [];
                if (map && map.remove) {
                    map.remove();
                    map = null;
                }
                if (typeof L !== 'undefined' && L.DomUtil.get('map') != null) {
                    L.DomUtil.get('map')._leaflet_id = null;
                }
                var legendHost = document.querySelector('.multivendor-map-wrap');
                var legendEl = document.getElementById('legend');
                if (legendHost && legendEl && !legendHost.contains(legendEl)) {
                    legendHost.appendChild(legendEl);
                }
            }

            var center = getDefaultMapCenter();
            var legend = document.getElementById('legend');

            if (mapType == "OFFLINE" || !canUseGoogle) {
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
                mapType = 'ONLINE';
                var myLatlng = new google.maps.LatLng(center.lat, center.lng);
                map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 10,
                    center: myLatlng,
                    streetViewControl: false,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
            }

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

            if (pendingMapData && pendingMapData.length) {
                $(".live-tracking-list").empty();
                renderFoodMapData(pendingMapData);
                fitMapToMarkers();
            }
        }

        window.godsEyeMapInit = function () {
            if (mapType === 'OFFLINE' && typeof google !== 'undefined' && google.maps) {
                InitializeFoodMap(true);
            } else if (!godsEyeMapReady) {
                InitializeFoodMap(false);
            }
        };

        function renderFoodMapData(data) {
            markers = [];
            var mapEntries = [];

            for (let i = 0; i < data.length; i++) {
                val = data[i];

                var html = '';
                var driverId = '';
                var hasCoords = false;
                if (val.flag == "in_transit") {
                    if (val.hasOwnProperty('driver') && val.driver) {
                        driverId = val.driver.id;
                    }
                } else {
                    driverId = val.id;
                }

                let driver = getDriverDetail(driverId);
                if (driver) {
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
                    }

                    if (val.flag == "in_transit") {
                        var user = val.author || null;
                        if (driver.firstName || driver.lastName) {
                            html += '<div class="live-tracking-box' + (hasCoords ? ' track-from' : '') + '"' +
                                (hasCoords ? ' data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '"' : '') + '>';
                            html += '<div class="live-tracking-inner">';
                            html += hasCoords
                                ? '<span class="listicon"><img src="' + base_url + '/car_on_trip.png" alt=""></span>'
                                : '<span class="listicon listicon-off"></span>';
                            html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                            if (user && (user.firstName || user.lastName)) {
                                html += '<h4 class="user-name">{{trans("lang.user_name")}} : ' + user.firstName + ' ' + user.lastName + '</h4>';
                            }
                            html += '<span class="badge badge-danger">In Transit</span>';
                            html += '&nbsp;&nbsp;<a href="/orders/edit/' + val.id + '" class="badge badge-info" target="_blank">{{trans("lang.order_id")}} : ' + String(val.id).substring(0, 7) + '</a>';
                            html += '</div>';
                            html += '</div>';
                        }
                    } else if (driver.firstName || driver.lastName) {
                        html += '<div class="live-tracking-box' + (hasCoords ? ' track-from' : '') + '"' +
                            (hasCoords ? ' data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '"' : '') + '>';
                        html += '<div class="live-tracking-inner">';
                        html += hasCoords
                            ? '<span class="listicon"><img src="' + base_url + '/car_available.png" alt=""></span>'
                            : '<span class="listicon listicon-off"></span>';
                        html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                        html += '<span class="badge badge-success">Available</span>';
                        html += '</div>';
                        html += '</div>';
                    }
                }

                if (html) {
                    $(".live-tracking-list").append(html);
                }

                if (hasCoords && driver) {
                    mapEntries.push({ index: i, val: val, driver: driver });
                }
            }

            mapEntries.forEach(function (entry) {
                var pos = entry.driver.location;
                if (!shouldShowDriverOnMap(pos.latitude, pos.longitude)) {
                    return;
                }
                try {
                    addDriverMarker(entry.index, entry.val, entry.driver);
                } catch (e) {
                    console.warn('Marker skipped for driver', entry.driver && entry.driver.id, e);
                }
            });

            startLocationRefresh();
        }

        function addDriverMarker(i, val, driver) {
            if (!map) {
                return;
            }

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
                    iconSize: [36, 36],
                });
                let marker = L.marker([driver.location.latitude, driver.location.longitude], { icon: customIcon, driverId: driver.id }).addTo(map);
                marker.bindPopup(content);
                markers[i] = marker;
                marker.on('click', function () {
                    marker.openPopup();
                });
            } else{
                let marker = new google.maps.Marker({
                    position: new google.maps.LatLng(driver.location.latitude, driver.location.longitude),
                    icon: {
                        url: iconImg,
                        scaledSize: new google.maps.Size(36, 36)
                    },
                    map: map
                });
                marker.driverId = driver.id;
                let infowindow = new google.maps.InfoWindow({
                    content: content
                });
                marker.addListener('click', function () {
                    infowindow.open(map, marker);
                });
                markers[i] = marker;
            }
        }

        function startLocationRefresh() {
            if (locationRefreshTimer) {
                clearInterval(locationRefreshTimer);
            }

            locationRefreshTimer = setInterval(function () {
                var driverIds = Object.keys(window.globalDrivers || {});
                if (!driverIds.length) {
                    return;
                }

                $.get(driverLocationsUrl, {
                    ids: driverIds.join(','),
                    service_type: serviceType
                }, function (res) {
                    (res.drivers || []).forEach(function (driver) {
                        if (!driver.id) {
                            return;
                        }
                        if (!driver.location) {
                            driver.location = {
                                latitude: driver.latitude,
                                longitude: driver.longitude
                            };
                        }
                        window.globalDrivers[driver.id] = driver;

                        if (!hasValidCoords(driver.location)) {
                            return;
                        }

                        markers.forEach(function (marker) {
                            if (!marker || marker.driverId !== driver.id) {
                                return;
                            }
                            if (mapType === 'OFFLINE' && marker.setLatLng) {
                                marker.setLatLng([driver.location.latitude, driver.location.longitude]);
                            } else if (marker.setPosition) {
                                marker.setPosition(new google.maps.LatLng(driver.location.latitude, driver.location.longitude));
                            }
                        });
                    });
                });
            }, 10000);
        }

    </script>

    @endsection
