@extends('layouts.app')



@section('content')



<div class="page-wrapper">



    <div class="row page-titles">



        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.live_tracking_of')}} <span class="section_name">{{ $sectionName ?? '' }}</span></h3>

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
        var map;
        var markers = [];
        var markersByDriverId = {};
        var activeInfoWindow = null;
        var base_url = '{!! asset('/images/') !!}';
        var mapType = 'ONLINE';
        var section_id = '8';
        var pendingMapData = null;
        var godsEyeMapReady = false;
        var mapDataLoaded = false;
        var lastTodaMapData = [];
        var mapSettingsUrl = "{{ route('map.settings') }}";
        var driverLocationsUrl = "{{ route('map.drivers.locations') }}";
        var serviceType = 'cab-service';
        var locationRefreshTimer = null;

        window.gm_authFailure = function () {
            if (mapType === "OFFLINE") {
                return;
            }
            console.warn('Google Maps failed to load, using OpenStreetMap for TODA tracking');
            mapType = "OFFLINE";
            godsEyeMapReady = false;
            InitializeTodaMap(true);
            if (lastTodaMapData.length) {
                $(".live-tracking-list").empty();
                renderTodaMapData(lastTodaMapData);
                fitTodaMapToTaxiMarkers();
            }
        };

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

        function startTodaLocationRefresh() {
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
                        resolveTodaDriverCoords(driver);

                        if (!hasValidCoords(driver.location)) {
                            return;
                        }

                        var marker = markersByDriverId[driver.id];
                        if (!marker) {
                            var driverFlag = (window.globalDrivers[driver.id] && window.globalDrivers[driver.id].flag) || 'available';
                            createTodaTaxiMapMarker(driver.id, { flag: driverFlag }, driver, driver.id);
                            updateTodaDriverListEntry(driver.id, driver, driverFlag);
                            fitTodaMapToTaxiMarkers();
                            return;
                        }

                        if (mapType === 'OFFLINE' && marker.setLatLng) {
                            marker.setLatLng([driver.location.latitude, driver.location.longitude]);
                        } else if (marker.setPosition) {
                            marker.setPosition(new google.maps.LatLng(driver.location.latitude, driver.location.longitude));
                        }
                    });
                });
            }, 5000);
        }

        function getDefaultMapCenter() {
            var default_lat = parseFloat(getCookie('default_latitude'));
            var default_lng = parseFloat(getCookie('default_longitude'));
            if (isNaN(default_lat) || isNaN(default_lng) || (Math.abs(default_lat) < 0.1 && Math.abs(default_lng) < 0.1)) {
                return { lat: 23.0225, lng: 72.5714 };
            }
            return { lat: default_lat, lng: default_lng };
        }

        function fetchTodaMapData() {
            if (mapDataLoaded) {
                return;
            }
            mapDataLoaded = true;

            $.ajax({
                url: "{{ route('map.toda.data') }}",
                method: 'GET',
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var orders = [];
                    var drivers = [];
                    var ordersDriverIds = [];
                    var globalDrivers = {};

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
                    lastTodaMapData = $.merge(orders, drivers);
                    pendingMapData = lastTodaMapData;
                    if (res.section && res.section.name) {
                        $('.section_name').text(res.section.name);
                    }
                    $(".live-tracking-list").empty();
                    renderTodaMapData(pendingMapData);
                    fitTodaMapToTaxiMarkers();
                },
                error: function (xhr) {
                    mapDataLoaded = false;
                    console.error('Failed to load TODA map data', xhr && xhr.status, xhr && xhr.responseText);
                    $(".live-tracking-list").html('<div class="p-3 text-danger">Failed to load drivers. Please refresh.</div>');
                }
            });
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
                InitializeTodaMap();
            } catch (e) {
                console.error('Map init failed, falling back to OSM', e);
                mapType = "OFFLINE";
                godsEyeMapReady = false;
                InitializeTodaMap(true);
            }

            fetchTodaMapData();

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

        function InitializeTodaMap(forceReinit) {
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
                markersByDriverId = {};
                if (map && map.remove) {
                    map.remove();
                    map = null;
                }
                if (typeof L !== 'undefined' && L.DomUtil.get('map') != null) {
                    L.DomUtil.get('map')._leaflet_id = null;
                }
                var legendHost = document.querySelector('.cab-map-wrap');
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
                    mapTypeControl: true,
                    fullscreenControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
            }

            godsEyeMapReady = true;

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

            if (pendingMapData && pendingMapData.length) {
                $(".live-tracking-list").empty();
                renderTodaMapData(pendingMapData);
                fitTodaMapToTaxiMarkers();
            }
        }

        window.godsEyeMapInit = function () {
            if (mapType === 'OFFLINE' && typeof google !== 'undefined' && google.maps) {
                InitializeTodaMap(true);
            } else if (!godsEyeMapReady) {
                InitializeTodaMap(false);
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

        function resolveTodaDriverCoords(driver) {
            if (!driver) {
                return null;
            }

            var lat = driver.latitude;
            var lng = driver.longitude;

            if (driver.location) {
                lat = lat != null ? lat : driver.location.latitude;
                lng = lng != null ? lng : driver.location.longitude;
            }

            if ((lat == null || lng == null) && driver.coordinates) {
                lat = lat != null ? lat : driver.coordinates.latitude;
                lng = lng != null ? lng : driver.coordinates.longitude;
            }

            if ((lat == null || lng == null) && driver.g && driver.g.geopoint) {
                lat = lat != null ? lat : driver.g.geopoint.latitude;
                lng = lng != null ? lng : driver.g.geopoint.longitude;
            }

            if (lat != null && lng != null) {
                driver.latitude = parseFloat(lat);
                driver.longitude = parseFloat(lng);
                driver.location = {
                    latitude: driver.latitude,
                    longitude: driver.longitude
                };
            }

            return driver.location;
        }

        function getTodaTaxiMarkerIcon(flag) {
            return flag === 'available' ? base_url + '/car_available.png' : base_url + '/car_on_trip.png';
        }

        function canPlaceTodaTaxiMarker(lat, lng) {
            return hasValidCoords({ latitude: lat, longitude: lng });
        }

        function createTodaTaxiMapMarker(i, val, driver, driverId) {
            if (!map || !driver || !driver.location) {
                return null;
            }

            resolveTodaDriverCoords(driver);
            if (!canPlaceTodaTaxiMarker(driver.location.latitude, driver.location.longitude)) {
                return null;
            }

            var iconImg = getTodaTaxiMarkerIcon(val.flag);
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
                return marker;
            }

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
            return marker;
        }

        function updateTodaDriverListEntry(driverId, driver, flag) {
            if (!driverId || !driver || !hasValidCoords(driver.location)) {
                return;
            }

            var $box = $('.live-tracking-box[data-driver-id="' + driverId + '"]');
            if (!$box.length) {
                return;
            }

            var iconImg = getTodaTaxiMarkerIcon(flag || 'available');
            $box.addClass('track-from')
                .attr('data-lat', driver.location.latitude)
                .attr('data-lng', driver.location.longitude)
                .attr('title', 'Click to view on map');
            $box.find('.listicon').html('<img src="' + iconImg + '" alt="">');
            $box.find('.toda-location-hint').remove();
        }

        function renderTodaTaxiMarkersOnMap(mapEntries) {
            mapEntries.forEach(function (entry) {
                resolveTodaDriverCoords(entry.driver);
                var pos = entry.driver.location;
                if (!canPlaceTodaTaxiMarker(pos.latitude, pos.longitude)) {
                    return;
                }
                try {
                    createTodaTaxiMapMarker(entry.index, entry.val, entry.driver, entry.driverId);
                } catch (e) {
                    console.warn('TODA taxi marker skipped for driver', entry.driverId, e);
                }
            });
        }

        function fitTodaMapToTaxiMarkers() {
            var validMarkers = Object.keys(markersByDriverId).map(function (id) {
                return markersByDriverId[id];
            }).filter(function (m) { return !!m; });
            if (!validMarkers.length || !map) {
                return;
            }

            var center = getDefaultMapCenter();

            if (mapType == "OFFLINE") {
                var group = L.featureGroup(validMarkers);
                map.fitBounds(group.getBounds(), { padding: [40, 40] });
                if (map.getZoom() > 14) {
                    map.setZoom(14);
                }
                return;
            }

            var bounds = new google.maps.LatLngBounds();
            validMarkers.forEach(function (marker) {
                bounds.extend(marker.getPosition());
            });
            map.fitBounds(bounds, 48);
            google.maps.event.addListenerOnce(map, 'idle', function () {
                if (map.getZoom() < 5) {
                    map.setCenter(new google.maps.LatLng(center.lat, center.lng));
                    map.setZoom(6);
                } else if (map.getZoom() > 15) {
                    map.setZoom(15);
                }
            });
        }

        function shouldShowDriverOnMap(lat, lng) {
            if (!hasValidCoords({ latitude: lat, longitude: lng })) {
                return false;
            }
            var center = getDefaultMapCenter();
            return distanceKm(center.lat, center.lng, parseFloat(lat), parseFloat(lng)) <= 400;
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

            var center = getDefaultMapCenter();
            var nearby = validMarkers.filter(function (marker) {
                var pos = mapType === 'OFFLINE' ? getMarkerLatLng(marker.getLatLng()) : getMarkerLatLng(marker.getPosition());
                return pos && distanceKm(center.lat, center.lng, pos.lat, pos.lng) <= 400;
            });
            if (nearby.length) {
                validMarkers = nearby;
            }

            if (mapType == "OFFLINE") {
                var group = L.featureGroup(validMarkers);
                map.fitBounds(group.getBounds(), { padding: [40, 40] });
                if (map.getZoom() > 14) {
                    map.setZoom(14);
                }
                return;
            }

            var bounds = new google.maps.LatLngBounds();
            validMarkers.forEach(function (marker) {
                bounds.extend(marker.getPosition());
            });
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

        function renderTodaMapData(data) {
            $(".live-tracking-list").empty();
            markers = [];
            markersByDriverId = {};
            var mapEntries = [];

            for (let i = 0; i < data.length; i++) {
                var val = data[i];
                var html = '';
                var driverId = '';
                var hasCoords = false;

                if (val.flag == "in_transit") {
                    if (val.driver && val.driver.id) {
                        driverId = val.driver.id;
                    }
                } else {
                    driverId = val.id;
                }

                var driver = getDriverDetail(driverId);
                if (!driver) {
                    continue;
                }

                resolveTodaDriverCoords(driver);

                hasCoords = hasValidCoords(driver.location);
                if (hasCoords) {
                    driver.location.latitude = parseFloat(driver.location.latitude);
                    driver.location.longitude = parseFloat(driver.location.longitude);
                }

                if (val.flag == "in_transit") {
                    var user = val.author || null;
                    if (driver.firstName || driver.lastName) {
                        html += '<div class="live-tracking-box' + (hasCoords ? ' track-from' : '') + '" data-driver-id="' + driverId + '"' +
                            (hasCoords ? ' data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '" title="Click to view on map"' : '') + '>';
                        html += '<div class="live-tracking-inner">';
                        html += '<span class="listicon"><img src="' + base_url + '/car_on_trip.png" alt=""></span>';
                        html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                        if (user && (user.firstName || user.lastName)) {
                            html += '<h4 class="user-name">{{trans("lang.user_name")}} : ' + user.firstName + ' ' + user.lastName + '</h4>';
                        }
                        html += '<span class="badge badge-danger">In Transit</span>';
                        html += '&nbsp;&nbsp;<a href="/rides/edit/' + val.id + '" class="badge badge-info" target="_blank">{{trans("lang.order_id")}} : ' + String(val.id).substring(0, 7) + '</a>';
                        html += '</div></div>';
                    }
                } else if (driver.firstName || driver.lastName) {
                    html += '<div class="live-tracking-box' + (hasCoords ? ' track-from' : '') + '" data-driver-id="' + driverId + '"' +
                        (hasCoords ? ' data-index="' + i + '" data-lat="' + driver.location.latitude + '" data-lng="' + driver.location.longitude + '" title="Click to view on map"' : '') + '>';
                    html += '<div class="live-tracking-inner">';
                    html += hasCoords
                        ? '<span class="listicon"><img src="' + base_url + '/car_available.png" alt=""></span>'
                        : '<span class="listicon listicon-off"></span>';
                    html += '<h3 class="drier-name">{{trans("lang.driver_name")}} : ' + driver.firstName + ' ' + driver.lastName + '</h3>';
                    html += '<span class="badge badge-success">Available</span>';
                    if (!hasCoords) {
                        html += ' <span class="badge badge-secondary toda-location-hint">No GPS</span>';
                    }
                    html += '</div></div>';
                }

                if (html) {
                    $(".live-tracking-list").append(html);
                }

                if (hasCoords) {
                    mapEntries.push({ index: i, val: val, driver: driver, driverId: driverId });
                }
            }

            renderTodaTaxiMarkersOnMap(mapEntries);

            startTodaLocationRefresh();
        }

        function addTodaDriverMarker(i, val, driver, driverId) {
            if (!map) {
                return;
            }

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

    </script>

    @endsection

