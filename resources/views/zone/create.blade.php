@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.zone_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('zone') !!}">{{ trans('lang.zone_plural') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.zone_create') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="error_top" style="display:none"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.zone_create') }}</legend>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{ trans('lang.zone_name') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" id="name">
                                        <div class="form-text text-muted">{{ trans('lang.zone_name_help') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <div class="form-check">
                                        <input type="checkbox" class="publish" id="publish">
                                        <label class="col-3 control-label" for="publish">{{ trans('lang.status') }}</label>
                                    </div>
                                </div>
                                <div class="form-hidden">
                                    <input type="hidden" id="coordinates" name="coordinates" value="">
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-sm-5">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4>{{ trans('lang.instructions') }}</h4>
                                    <p>{{ trans('lang.instructions_help') }}</p>
                                    <p><i class="fa fa-hand-pointer-o map_icons"></i>{{ trans('lang.instructions_hand_tool') }}</p>
                                    <p><i class="fa fa-plus-circle map_icons"></i>{{ trans('lang.instructions_shape_tool') }}</p>
                                    <p><i class="mdi mdi-delete map_icons"></i>{{ trans('lang.instructions_trash_tool') }}</p>
                                </div>
                                <div class="col-sm-12">
                                    <img src="{{ asset('images/zone_info.gif') }}" alt="GIF" width="100%">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 zone-map-column">
                            <input type="text" placeholder="{{ trans('lang.search_location') }}" id="search-box" class="form-control controls" />
                            <div id="autocomplete-list"></div>
                            <div id="map"></div>
                        </div>
                        <div class="col-sm-2 mapType">
                            <ul style="list-style: none;padding:0">
                                <li>
                                    <a id="select-button" href="javascript:void(0)" class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped" title="Use this tool to drag the map and select your desired location">
                                        <i class="fa fa-hand-pointer-o map_icons"></i>
                                    </a>
                                </li>
                                <li>
                                    <a id="add-button" href="javascript:void(0)" class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped" title="Use this tool to highlight areas and connect the dots">
                                        <i class="fa fa-plus-circle map_icons"></i>
                                    </a>
                                </li>
                                <li>
                                    <a id="delete-all-button" href="javascript:void(0)" class="btn-floating zone-delete-all-btn btn-large waves-effect waves-light tooltipped" title="Use this tool to delete all selected areas">
                                        <i class="mdi mdi-delete map_icons"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-setting-btn">
                            <i class="fa fa-save"></i> {{ trans('lang.save') }}
                        </button>
                        <a href="{!! route('zone') !!}" class="btn btn-default">
                            <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    #map {
        height: 500px;
        width: 100%;
    }

    #panel {
        width: 200px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        float: right;
        margin: 10px;
        margin-top: 100px;
    }

    #delete-button,
    #add-button,
    #delete-all-button,
    #save-button {
        margin-top: 5px;
    }

    #search-box {
        background-color: #f7f7f7;
        font-size: 15px;
        font-weight: 300;
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        height: 25px;
        border: 1px solid #c7c7c7;
    }

    .map_icons {
        font-size: 24px;
        color: white;
        padding: 10px;
        margin: 5px;
        background-color: {{ isset($_COOKIE['admin_panel_color']) ? $_COOKIE['admin_panel_color'] : '#072750' }};
    }

    #autocomplete-list {
        border: 1px solid #d4d4d4;
        z-index: 9999;
        position: absolute;
        top: 42px;
        left: 0;
        right: 0;
        display: none;
        background-color: white;
        cursor: pointer;
        max-height: 240px;
        overflow-y: auto;
    }

    .zone-map-column {
        position: relative;
    }

    .autocomplete-item {
        padding: 10px;
        border-bottom: 1px solid #d4d4d4;
    }

    .autocomplete-item:hover {
        background-color: #e9e9e9;
    }

    .leaflet-control-custom {
        background-color: #f44336;
        border: none;
        color: white;
        padding: 10px;
        cursor: pointer;
        font-size: 16px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    /* Hover effect for the button */
    .leaflet-control-custom:hover {
        background-color: #d32f2f;
    }

    .leaflet-control-custom i {
        font-size: 18px;
    }
</style>
@section('scripts')
    <script>
        var database = kweekDb();
        var id = database.collection("tmp").doc().id;
        var ref = database.collection('zone');
        $(document).ready(function() {
            $(".save-setting-btn").click(function() {
                var name = $("#name").val();
                var publish = $("#publish").is(":checked");
                var coordinates_object = $('#coordinates').val();
                $(".error_top").empty();
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.zone_name_error') }}</p>");
                    window.scrollTo(0, 0);
                }else if (coordinates_object == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.zone_coordinates_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    if (mapType == "ONLINE") {
                        var coordinates_parse = $.parseJSON(coordinates_object);
                        var latitude = coordinates_parse[0].lat;
                        var longitude = coordinates_parse[0].lng;
                        var area = [];
                        for (let i = 0; i < coordinates_parse.length; i++) {
                            var item = coordinates_parse[i];
                            area.push({ latitude: item.lat, longitude: item.lng });
                        }
                        submitZoneData(name, latitude, longitude, area, publish);
                    } else {
                        var coordinates_parse = $.parseJSON(coordinates_object);
                        // ... the rest of the parsing logic ...
                        if (Array.isArray(coordinates_parse) && coordinates_parse.length > 0) {
                            // Check if the first item in coordinates_parse is an array (polygon)
                            if (Array.isArray(coordinates_parse[0])) {
                                // Handle case where the first element is an array of points (polygon)
                                if (coordinates_parse[0].length > 0) {
                                    var firstPoint = coordinates_parse[0][0]; // First point in the first polygon 
                                    // Ensure the first point has valid lat and lng properties
                                    if (firstPoint && typeof firstPoint.lat === 'number' && typeof firstPoint.lng === 'number') {
                                        latitude = firstPoint.lat; // First point's latitude
                                        longitude = firstPoint.lng; // First point's longitude
                                    } else {
                                        console.error("Invalid first point in coordinates_parse:", firstPoint);
                                        return; // Exit if the first point is invalid
                                    }
                                } else {
                                    console.error("First polygon (coordinates_parse[0]) is empty.");
                                    return; // Exit if the first polygon is empty
                                }
                            } else {
                                // Handle case where the first element is a single point object (no array of points)
                                var firstPoint = coordinates_parse[0]; // This is an object with lat/lon (single point)
                                // Ensure this object has valid lat and lon properties
                                if (firstPoint && typeof firstPoint.lat === 'number' && typeof firstPoint.lon === 'number') {
                                    latitude = firstPoint.lat; // Set latitude from the first point
                                    longitude = firstPoint.lon; // Set longitude from the first point
                                } else {
                                    console.error("Invalid first point object in coordinates_parse:", firstPoint);
                                    return; // Exit if the point is invalid
                                }
                            }
                        } else {
                            console.error("coordinates_parse is not a valid array or is empty:", coordinates_parse);
                            return; // Exit if coordinates_parse is empty or invalid
                        }
                        var area = [];
                        for (let i = 0; i < coordinates_parse.length; i++) {
                            var polygon = coordinates_parse[i]; // Each polygon is an array of points or a single point object
                            // Check if the polygon is an array (an array of points)
                            if (Array.isArray(polygon)) {
                                // Iterate over each point in the polygon
                                polygon.forEach(function(point, index) {
                                    // Check if the point is valid (has lat and lng properties)
                                    if (point && typeof point.lat === 'number' && typeof point.lng === 'number') {
                                        // Correctly create GeoPoint for each valid point and add to the area array
                                        area.push(new kweekDb.GeoPoint(point.lat, point.lng));
                                    } else {
                                        // Log the error if a point is invalid or undefined
                                        console.error("Invalid lat/lng at polygon index " + i + ", point index " + index, point);
                                        $(".error_top").show();
                                        $(".error_top").html("<p>{{ trans('lang.invalid_coordinates_error') }}</p>");
                                        window.scrollTo(0, 0);
                                        return; // Stop processing invalid point
                                    }
                                });
                            } else {
                                // If the polygon is not an array, handle it as a single point object
                                if (polygon && typeof polygon.lat === 'number' && typeof polygon.lon === 'number') {
                                    // Correctly create GeoPoint for a single valid point and add to the area array
                                    area.push(new kweekDb.GeoPoint(polygon.lat, polygon.lon));
                                } else {
                                    console.error("Invalid single point object at polygon index " + i, polygon);
                                    $(".error_top").show();
                                    $(".error_top").html("<p>{{ trans('lang.invalid_coordinates_error') }}</p>");
                                    window.scrollTo(0, 0);
                                    return; // Stop processing invalid point
                                }
                            }
                        }
                        jQuery("#overlay").show();
                        if (latitude && longitude && area.length > 0) {
                            submitZoneData(name, latitude, longitude, area, publish);
                        } else {
                            console.error("Invalid latitude, longitude, or area:", latitude, longitude, area);
                            $(".error_top").show();
                            $(".error_top").html("<p>{{ trans('lang.invalid_coordinates_error') }}</p>");
                            window.scrollTo(0, 0);
                        }
                    }
                }
            });

        function submitZoneData(name, latitude, longitude, area, publish) {
            var formData = new FormData();
            formData.append('name', name);
            formData.append('latitude', latitude);
            formData.append('longitude', longitude);
            formData.append('area', JSON.stringify(area));
            formData.append('publish', publish ? '1' : '0');
            
            fetch('{{ route("zone.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            }).then(response => response.json())
            .then(data => {
                jQuery("#overlay").hide();
                if (data.success) {
                    window.location.href = '{{ route("zone") }}';
                } else {
                    $(".error_top").show().html("<p>" + (data.message || 'Error occurred') + "</p>");
                    window.scrollTo(0, 0);
                }
            }).catch(error => {
                jQuery("#overlay").hide();
                $(".error_top").show().html("<p>" + error + "</p>");
                window.scrollTo(0, 0);
            });
        }

            var dbMapType = '{{ \DB::table("settings")->where("id", "DriverNearBy")->value("value") ? (json_decode(\DB::table("settings")->where("id", "DriverNearBy")->value("value"))->selected_map_type ?? "google") : "google" }}';
            if (dbMapType === 'osm') {
                mapType = 'OFFLINE';
            }
            bindZoneMapButtons();
            waitForMaps(initMap);
        });

        function bindZoneMapButtons() {
            var onclick, polygon, deletearea;
            if (mapType === 'OFFLINE') {
                onclick = function () {
                    DragMap();
                };
                polygon = function () {
                    enablePolygonDrawing(map);
                };
                deletearea = function () {
                    deleteSelectedPolygon();
                };
            } else {
                onclick = function () {
                    drawingManager.setDrawingMode(null);
                };
                polygon = function () {
                    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
                };
                deletearea = function () {
                    clearMap();
                };
            }
            document.getElementById('select-button').onclick = onclick;
            document.getElementById('add-button').onclick = polygon;
            document.getElementById('delete-all-button').onclick = deletearea;
        }
        var map;
        let polygon;
        let polygonPath;
        var drawingManager;
        var selectedShape;
        var selectedKernel;
        var gmarkers = [];
        var coordinates = [];
        var allShapes = [];
        var sendable_coordinates = [];
        var shapeColor = "#007cff";
        var kernelColor = "#000";
        var default_lat = getCookie('default_latitude');
        var default_lng = getCookie('default_longitude');
        let drawnItems;
        let deleteButton, dragMap;
        let selectedPolygon = null;
        var mapType = 'ONLINE';

        function setMapOnAll(map) {
            for (var i = 0; i < gmarkers.length; i++) {
                gmarkers[i].setMap(map);
            }
        }

        function clearMarkers() {
            setMapOnAll(null);
        }

        function deleteMarkers() {
            clearMarkers();
            gmarkers = [];
        }

        function deleteSelectedShape() {
            if (selectedShape) {
                selectedShape.setMap(null);
                var index = allShapes.indexOf(selectedShape);
                if (index > -1) {
                    allShapes.splice(index, 1);
                }
            }
            if (selectedKernel) {
                selectedKernel.setMap(null);
            }
            let lat_lng = [];
            allShapes.forEach(function(data, index) {
                lat_lng[index] = getCoordinates(data);
            });
            if (lat_lng.length == 0) {
                document.getElementById('coordinates').value = '';
            } else {
                document.getElementById('coordinates').value = JSON.stringify(lat_lng);
            }
        }

        function clearMap() {
            if (allShapes.length > 0) {
                for (var i = 0; i < allShapes.length; i++) {
                    allShapes[i].setMap(null);
                }
                allShapes = [];
                deleteMarkers();
                document.getElementById('coordinates').value = null;
            }
        }

        function clearSelection() {
            if (selectedShape) {
                if (selectedShape.type !== 'marker') {
                    selectedShape.setEditable(false);
                }
                selectedShape = null;
            }
            if (selectedKernel) {
                if (selectedKernel.type !== 'marker') {
                    selectedKernel.setEditable(false);
                }
                selectedKernel = null;
            }
        }

        function setSelection(shape, check) {
            clearSelection();
            shape.setEditable(true);
            shape.setDraggable(true);
            if (check) {
                selectedKernel = shape;
            } else {
                selectedShape = shape;
            }
        }

        function getCoordinates(polygon) {
            var path = polygon.getPath();
            coordinates = [];
            for (var i = 0; i < path.length; i++) {
                coordinates.push({
                    lat: path.getAt(i).lat(),
                    lng: path.getAt(i).lng()
                });
            }
            document.getElementById('coordinates').value = JSON.stringify(coordinates);
            return coordinates;
        }

        function createMarker(coord, nr, map) {
            var mesaj = "<h6>Vârf " + nr + "</h6><br>" + "Lat: " + coord.lat + "<br>" + "Lng: " + coord.lng;
            var marker = new google.maps.Marker({
                position: coord,
                map: map,
            });
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent(mesaj);
                infowindow.open(map, marker);
            });
            google.maps.event.addListener(marker, 'dblclick', function() {
                marker.setMap(null);
            });
            return marker;
        }

        function enablePolygonEditingAndDragging(layer) {
            // Ensure the layer is editable and draggable
            if (layer && (layer instanceof L.Polygon || layer instanceof L.MultiPolygon)) {
                if (!layer.editing) {
                    layer.enableEdit(); // Enable editing on the polygon
                }
                if (!layer.dragging) {
                    if (typeof L.Handler.PolygonDrag !== 'undefined') {
                        layer.dragging = new L.Handler.PolygonDrag(layer);
                        layer.dragging.enable(); // Enable dragging handler
                    } else {
                        console.error("L.Handler.PolygonDrag is not available.");
                    }
                }
            } else {
                console.error("The layer is not a valid L.Polygon or L.MultiPolygon:", layer);
            }
        }

        function makePolygonDraggable(layer) {
            var latLngs = layer.getLatLngs()[0]; // Get the LatLngs of the polygon
            const coordinates = layer.getLatLngs(); // Get the polygon's coordinates
            document.getElementById('coordinates').value = JSON.stringify(coordinates);
            // To track mouse position and delta
            var isDragging = false;
            var startLatLng = null;
            var startLatLngs = [];
            // Mouse down event to start dragging
            layer.on('mousedown', function(e) {
                isDragging = true;
                startLatLng = e.latlng; // Store the initial mouse position in LatLng
                startLatLngs = latLngs.map(function(latlng) {
                    return latlng; // Clone the LatLngs of the polygon for reference
                });
                map.on('mousemove', onMouseMove); // Track mouse movement
                map.on('mouseup', onMouseUp); // End dragging when mouse is released
            });
            // Mouse move event to drag the polygon
            function onMouseMove(e) {
                const coordinates = layer.getLatLngs(); // Get the polygon's coordinates
                layer.setLatLngs(coordinates);
                document.getElementById('coordinates').value = JSON.stringify(coordinates);
                if (isDragging) {
                    var dx = e.latlng.lng - startLatLng.lng; // Calculate change in longitude
                    var dy = e.latlng.lat - startLatLng.lat; // Calculate change in latitude
                    // Create new LatLngs by applying the change to each point
                    var newLatLngs = startLatLngs.map(function(latlng) {
                        return L.latLng(latlng.lat + dy, latlng.lng + dx); // Shift each point by dx, dy
                    });
                    // Update the polygon's LatLngs
                    layer.setLatLngs([newLatLngs]);
                    document.getElementById('coordinates').value = JSON.stringify(newLatLngs);
                }
            }
            // Mouse up event to stop dragging
            function onMouseUp() {
                const coordinates = layer.getLatLngs(); // Get the polygon's coordinates
                layer.setLatLngs(coordinates);
                document.getElementById('coordinates').value = JSON.stringify(coordinates);
                isDragging = false;
                map.off('mousemove', onMouseMove); // Stop mousemove tracking
                map.off('mouseup', onMouseUp); // Stop mouseup tracking
            }
        }
        // Function to update coordinates (when polygon is resized or dragged)
        function updateCoordinates(layer) {
            let latLngs = layer.getLatLngs();
            let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
            let convertedArray = flatLatLngs.map(function(latLng) {
                if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                    return {
                        lat: latLng.lat,
                        lon: latLng.lng
                    };
                }
            }).filter(item => item !== undefined); // Filter out undefined items
            // Update coordinates in the input field
            document.getElementById('coordinates').value = JSON.stringify(convertedArray);
        }

        function createDragMapButton() {
            if (!dragMap) {
                var dragMap = L.control({
                    position: 'topright'
                });
                dragMap.onAdd = function(map) {
                    var button = L.DomUtil.create('button', 'leaflet-control-custom');
                    button.innerHTML = '<i class="fa fa-hand-pointer-o"></i>'; // Using Font Awesome icon
                    // Disable map dragging when clicking the button
                    L.DomEvent.disableClickPropagation(button);
                    // Button click functionality
                    button.addEventListener('click', function() {
                        DragMap();
                    });
                    return button; // Return the button to the control
                };
                // Add the custom button to the map
                dragMap.addTo(map);
            }
        }
        // Create the delete button once and hide it initially
        function createDeleteButton() {
            if (!deleteButton) {
                var deleteButton = L.control({
                    position: 'topright'
                });
                deleteButton.onAdd = function(map) {
                    var button = L.DomUtil.create('button', 'leaflet-control-custom');
                    button.innerHTML = '<i class="mdi mdi-delete"></i>'; // Using Font Awesome icon
                    // Disable map dragging when clicking the button
                    L.DomEvent.disableClickPropagation(button);
                    // Button click functionality
                    button.addEventListener('click', function() {
                        deleteSelectedPolygon();
                    });
                    return button; // Return the button to the control
                };
                // Add the custom button to the map
                deleteButton.addTo(map);
            }
        }

        function updateCoordinatesDisplay(lat, lon) {
            fetch('{{ route('zone.location-reverse') }}?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon))
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data && data.display_name) {
                        document.getElementById('search-box').value = data.display_name;
                    }
                })
                .catch(function (error) {
                    console.error('Error:', error);
                });
        }

        function enablePolygonDrawing(map) {
            map.dragging.disable();
            if (!drawnItems) {
                drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);
            }
            // Create the delete button before enabling drawing
            createDeleteButton();
            createDragMapButton();
            map.on('draw:created', function(event) {
                var layer = event.layer; // The drawn polygon or shape
                if (layer instanceof L.Polygon) {
                    // Add the drawn layer to the map
                    drawnItems.addLayer(layer);
                    makePolygonDraggable(layer);
                    // Bind a popup and open it
                    layer.bindPopup("Drag me!").openPopup();
                    // Update selected polygon variable (optional, depending on use case)
                    selectedPolygon = layer;
                } else {
                    console.log("This is not a polygon.");
                }
            });
            // Optional: Restrict dragging to only one polygon at a time (click event)
            map.on('click', function(event) {
                map.dragging.disable();
                var latlng = event.latlng;
                if (selectedPolygon) {
                    // If there's already a selected polygon, deselect it
                    selectedPolygon.setStyle({
                        color: '#3388ff'
                    });
                }
                drawnItems.eachLayer(function(layer) {
                    makePolygonDraggable(layer);
                    if (layer instanceof L.Polygon && layer.getBounds().contains(latlng)) {
                        selectedPolygon = layer;
                        layer.setStyle({
                            color: 'red'
                        });
                    }
                    // Optionally, log the coordinates of the drawn polygon to the console
                    const coordinates = layer.getLatLngs(); // Get the polygon's coordinates
                    document.getElementById('coordinates').value = JSON.stringify(coordinates);
                });
            });
        }

        function DragMap() {
            map.dragging.enable();
        }
        // Allow deletion of selected polygon
        function deleteSelectedPolygon() {
            map.dragging.disable();
            if (!drawnItems) {
                return;
            }
            if (selectedPolygon) {
                drawnItems.removeLayer(selectedPolygon);
                selectedPolygon = null;
                if (selectedPolygon == null) {
                    document.getElementById('coordinates').value = '';
                }
            } else {
                alert("Please select polygon to delete");
            }
        }

        function searchBox() {
            var input = document.getElementById('search-box');
            var autocompleteList = document.getElementById('autocomplete-list');
            var locationMarker = null;
            var searchTimeout = null;

            function selectPlace(place) {
                var lat = parseFloat(place.lat);
                var lon = parseFloat(place.lon || place.lng);
                input.value = place.display_name || place.formatted_address || input.value;
                input.setAttribute('data-latitude', lat);
                input.setAttribute('data-longitude', lon);

                if (mapType === 'OFFLINE') {
                    if (locationMarker && map.hasLayer(locationMarker)) {
                        map.removeLayer(locationMarker);
                    }
                    locationMarker = L.marker([lat, lon], { draggable: true }).addTo(map);
                    map.setView([lat, lon], 13);
                    locationMarker.on('dragend', function (e) {
                        var pos = e.target.getLatLng();
                        updateCoordinatesDisplay(pos.lat, pos.lng);
                    });
                } else if (place.geometry) {
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.panTo(place.geometry.location);
                        map.setZoom(13);
                    }
                } else if (!isNaN(lat) && !isNaN(lon)) {
                    map.panTo({ lat: lat, lng: lon });
                    map.setZoom(13);
                }

                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
            }

            function renderResults(results) {
                autocompleteList.innerHTML = '';
                if (!results || !results.length) {
                    autocompleteList.style.display = 'none';
                    return;
                }
                autocompleteList.style.display = 'block';
                results.forEach(function (place) {
                    var item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.textContent = place.display_name || place.formatted_address || '';
                    item.onclick = function () {
                        selectPlace(place);
                    };
                    autocompleteList.appendChild(item);
                });
            }

            function runOfflineSearch(query) {
                if (!query || query.length < 3) {
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                    return;
                }
                fetch('{{ route('zone.location-search') }}?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(function (response) { return response.json(); })
                    .then(renderResults)
                    .catch(function (error) {
                        console.error('Location search failed:', error);
                    });
            }

            input.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    if (mapType === 'OFFLINE') {
                        runOfflineSearch(input.value.trim());
                    }
                }, 350);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(searchTimeout);
                    if (mapType === 'OFFLINE') {
                        runOfflineSearch(input.value.trim());
                    }
                }
            });

            document.addEventListener('click', function (e) {
                if (e.target !== input && !autocompleteList.contains(e.target)) {
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                }
            });

            if (mapType !== 'OFFLINE' && typeof google !== 'undefined' && google.maps && google.maps.places) {
                var searchBoxControl = new google.maps.places.SearchBox(input);
                map.addListener('bounds_changed', function () {
                    searchBoxControl.setBounds(map.getBounds());
                });
                searchBoxControl.addListener('places_changed', function () {
                    var places = searchBoxControl.getPlaces();
                    if (!places || !places.length) {
                        return;
                    }
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function (place) {
                        if (!place.geometry) {
                            return;
                        }
                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
                var autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', map);
                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    if (!place || !place.geometry) {
                        return;
                    }
                    selectPlace({
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng(),
                        formatted_address: place.formatted_address,
                        geometry: place.geometry,
                    });
                });
            }
        }

        function waitForMaps(callback, attempts) {
            attempts = attempts || 0;
            var ready = false;
            if (mapType === 'OFFLINE') {
                ready = typeof L !== 'undefined' && typeof L.map === 'function';
            } else {
                ready = typeof google !== 'undefined' && google.maps && google.maps.Map && google.maps.places;
            }
            if (ready || attempts > 80) {
                callback();
                return;
            }
            setTimeout(function () {
                waitForMaps(callback, attempts + 1);
            }, 100);
        }

        function mapCenterCoords() {
            var lat = parseFloat(getCookie('default_latitude'));
            var lng = parseFloat(getCookie('default_longitude'));
            if (isNaN(lat) || isNaN(lng)) {
                lat = 23.022505;
                lng = 72.571365;
            }
            return { lat: lat, lng: lng };
        }

        function initMap() {
            var center = mapCenterCoords();
            var legend = document.getElementById('legend');
            if (mapType == "ONLINE") {
                $(".mapType").show();
                var infowindow = new google.maps.InfoWindow({
                    size: new google.maps.Size(150, 50)
                });
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: new google.maps.LatLng(center.lat, center.lng),
                    mapTypeControl: false,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.LEFT_CENTER
                    },
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    },
                    scaleControl: false,
                    scaleControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    },
                    streetViewControl: false,
                    fullscreenControl: false
                });
                searchBox();
                var shapeOptions = {
                    strokeWeight: 1,
                    fillOpacity: 0.4,
                    editable: true,
                    draggable: true
                };
                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: null,
                    drawingControl: false,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER,
                        drawingModes: ['polygon']
                    },
                    polygonOptions: shapeOptions,
                    map: map
                });
                google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
                    var newShape = e.overlay;
                    allShapes.push(newShape);
                    let lat_lng = [];
                    allShapes.forEach(function(data, index) {
                        lat_lng[index] = getCoordinates(data);
                    });
                    document.getElementById('coordinates').value = JSON.stringify(lat_lng);
                    newShape.setOptions({
                        fillColor: shapeColor
                    });
                    getCoordinates(newShape);
                    drawingManager.setDrawingMode(null);
                    setSelection(newShape, 0);
                    google.maps.event.addListener(newShape, 'click', function(e) {
                        if (e.vertex !== undefined) {
                            var path = newShape.getPaths().getAt(e.path);
                            path.removeAt(e.vertex);
                            getCoordinates(newShape);
                            if (path.length < 3) {
                                newShape.setMap(null);
                            }
                        }
                        setSelection(newShape, 0);
                    });
                    //update coordinates
                    google.maps.event.addListener(newShape, 'click', function(e) {
                        getCoordinates(newShape);
                    });
                    google.maps.event.addListener(newShape, "dragend", function(e) {
                        getCoordinates(newShape);
                    });
                    google.maps.event.addListener(newShape.getPath(), "insert_at", function(e) {
                        getCoordinates(newShape);
                    });
                    google.maps.event.addListener(newShape.getPath(), "remove_at", function(e) {
                        getCoordinates(newShape);
                    });
                    google.maps.event.addListener(newShape.getPath(), "set_at", function(e) {
                        getCoordinates(newShape);
                    });
                });
                google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                google.maps.event.addListener(map, 'click', clearSelection);
            } else {
                $(".mapType").hide();
                searchBox();
                map = L.map('map').setView([center.lat, center.lng], 10);
                map.dragging.disable();
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                // Create a feature group to store drawn items (polygons, lines, etc.)
                drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);
                // Set up the Leaflet Draw control
                var drawControl = new L.Control.Draw({
                    edit: {
                        featureGroup: drawnItems,
                        remove: false
                    },
                    draw: {
                        polygon: {
                            allowIntersection: false, // Disable intersecting polygons
                            showArea: true // Show area of the polygon
                        },
                        rectangle: false, // Disable rectangle drawing
                        circle: false, // Disable circle drawing
                        marker: false, // Disable marker drawing
                        polyline: false, // Disable polyline drawing
                        circlemarker: false,
                    },
                });
                map.addControl(drawControl);
                map.on('draw:dragend', function(event) {
                    makePolygonDraggable(event.layer);
                });
                map.on('draw:edited', function(event) {
                    event.layers.eachLayer(function(layer) {
                        makePolygonDraggable(layer);
                        if (layer instanceof L.Polygon || layer instanceof L.MultiPolygon) {
                            // Get the coordinates of the polygon (all vertices)
                            let latLngs = layer.getLatLngs();
                            // Flatten the array of coordinates in case of multi-polygon
                            let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
                            // Convert to desired format (lat, lon)
                            let convertedArray = flatLatLngs.map(function(latLng) {
                                if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                                    if (latLng.lat >= -90 && latLng.lat <= 90 && latLng.lng >= -180 && latLng.lng <= 180) {
                                        return {
                                            lat: latLng.lat,
                                            lon: latLng.lng
                                        };
                                    } else {
                                        console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                        return null; // Avoid undefined latLngs
                                    }
                                } else {
                                    console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                    return null; // Avoid undefined latLngs
                                }
                            }).filter(item => item !== null);
                            // Final array to be saved as JSON
                            let finalArray = convertedArray;
                            layer.setLatLngs(finalArray);
                            document.getElementById('coordinates').value = JSON.stringify(finalArray);
                        }
                    });
                });
                map.on('draw:resize', function(event) {
                    var layer = event.layer;
                    if (layer instanceof L.Polygon || layer instanceof L.MultiPolygon) {
                        let latLngs = layer.getLatLngs();
                        let flatLatLngs = L.LineUtil.isFlat(latLngs) ? latLngs : latLngs.flat(Infinity);
                        let convertedArray = flatLatLngs.map(function(latLng) {
                            if (latLng && typeof latLng.lat === 'number' && typeof latLng.lng === 'number') {
                                if (latLng.lat >= -90 && latLng.lat <= 90 && latLng.lng >= -180 && latLng.lng <= 180) {
                                    return {
                                        lat: latLng.lat,
                                        lon: latLng.lng
                                    };
                                } else {
                                    console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                    return null; // Avoid undefined latLngs
                                }
                            } else {
                                console.error("Invalid latLng:", latLng); // Log invalid latLng for debugging
                                return null; // Avoid undefined latLngs
                            }
                        }).filter(item => item !== null);
                        // Final array to be saved as JSON
                        let finalArray = convertedArray;
                        document.getElementById('coordinates').value = JSON.stringify(finalArray);
                    }
                });
                enablePolygonDrawing(map);
            }
        }
    </script>
@endsection
