@extends('layouts.app')

@section('content')
<style>
    #autocomplete-list {
        border: 1px solid #d4d4d4;
        z-index: 99;
        position: absolute;
        background-color: white;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        cursor: pointer;
    }
    .autocomplete-item {
        padding: 10px;
        border-bottom: 1px solid #d4d4d4;
    }
    .autocomplete-item:hover {
        background-color: #e9e9e9;
    }
    .provider-field .select2-container {
        width: 100% !important;
        display: block;
    }
    .provider-field .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        display: flex;
        align-items: center;
    }
    .provider-field .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        padding-right: 28px;
        color: #495057;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        box-sizing: border-box;
    }
    .provider-field .select2-container .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }
    .provider-field .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
    }
    .provider-field .select2-dropdown {
        border: 1px solid #ced4da;
        z-index: 1051;
    }
    .uploaded_image_owner img {
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
        margin-top: 10px;
    }
</style>

<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.worker_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>


                <li class="breadcrumb-item"><a href="{!! route('ondemand.workers.index') !!}">{{trans('lang.worker_table')}}</a>
                </li>

                <li class="breadcrumb-item">{{trans('lang.worker_create')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">
        <div class="error_top"></div>
        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend>{{trans('lang.worker_create')}}</legend>

                    <div class="form-group row width-50">
                        <input type="hidden" class="form-control author_profile">
                        <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control first_name">
                            <div class="form-text text-muted" min="0">
                                {{ trans("lang.user_first_name_help") }}
                            </div>
                        </div>
                    </div>


                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{ trans('lang.last_name')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control last_name">
                            <div class="form-text text-muted" min="0">
                                {{ trans("lang.user_last_name_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.email')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control email">
                            <div class="form-text text-muted">
                                {{ trans("lang.user_email_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.password')}}</label>
                        <div class="col-7">
                            <input type="password" class="form-control password">
                            <div class="form-text text-muted">
                                {{ trans("lang.user_password_help") }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control phone"
                                onkeypress="return chkAlphabets2(event,'error1')">
                            <div id="error1" class="err"></div>
                            <div class="form-text text-muted w-50">
                                {{ trans("lang.user_phone_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.salary')}}</label>
                        <div class="col-7">
                            <input type="number" class="form-control salary">
                            <div class="form-text text-muted">
                                {{ trans("lang.user_salary_help") }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.address')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control address" id="address" autocomplete="on">
                            <div id="autocomplete-list"></div>

                        </div>
                    </div>

                    @if(!isset($_GET['id']))
                    <div class="form-group row provider-field" style="width:100%">
                        <label class="col-3 control-label" for="provider_select">{{trans('lang.provider')}}</label>
                        <div class="col-7">
                            <select id="provider_select" class="form-control" style="width:100%">
                                <option value="">{{trans('lang.select_provider')}}</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.user_profile_picture')}}</label>
                        <div class="col-7">
                            <input type="file" onChange="handleFileSelectowner(event)" class="form-control-file" accept="image/*">
                            <div id="uploding_image_owner"></div>
                            <div class="uploaded_image_owner" style="display:none;">
                                <img id="uploaded_image_owner" src="" width="150" height="150" alt="Worker profile">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="item_publish">{{trans('lang.active')}}</label>
                        <div class="col-7">
                            <div class="form-check pl-0">
                                <input type="checkbox" class="item_publish" id="item_publish">
                                <label class="control-label mb-0" for="item_publish">{{trans('lang.active')}}</label>
                            </div>
                        </div>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>
         @if(!isset($_GET['id']))
        <a href="{!! route('ondemand.workers.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        @else
        <a href="{!! route('ondemand.workers.index',$_GET['id']) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        @endif
    </div>
</div>
</div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>

<script>

    var database = kweekDb();

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var createdAt = kweekDb.FieldValue.serverTimestamp();
    var workerImagesCount = 0;
    var ownerphoto = '';
    var ownerFileName = '';
    var photo = "";
    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    var provider_id="{{@$_GET['id']}}";
    var section_id = getCookie('section_id');

    var mapType = 'ONLINE';
    database.collection('settings').doc('DriverNearBy').get().then(async function (snapshots) {
        var data = snapshots.data();
        if (data && data.selectedMapType && data.selectedMapType == "osm") {
            mapType = "OFFLINE"
        }
    });

    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    function showWorkerError(msg) {
        $(".error_top").show().html("<p>" + msg + "</p>");
        window.scrollTo(0, 0);
    }

    function loadProviders() {
        if (!$('#provider_select').length) {
            return;
        }
        $.get("{{ route('ondemand.providers.list') }}", { section_id: section_id || '' })
            .done(function (res) {
                (res.data || []).forEach(function (provider) {
                    $('#provider_select').append(
                        $('<option></option>').attr('value', provider.id).text(provider.name)
                    );
                });
                if ($.fn.select2) {
                    if ($('#provider_select').hasClass('select2-hidden-accessible')) {
                        $('#provider_select').select2('destroy');
                    }
                    $('#provider_select').select2({
                        placeholder: "{{trans('lang.select_provider')}}",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#provider_select').closest('.col-7')
                    });
                    $('#provider_select').next('.select2-container').css('width', '100%');
                }
            })
            .fail(function () {
                showWorkerError('Failed to load providers.');
            });
    }

    $(document).ready(function () {
        loadProviders();

        $(".save-form-btn").click(async function () {
            var userFirstName = $(".first_name").val();
            var userLastName = $(".last_name").val();
            var email = $(".email").val();
            var password = $(".password").val();
            var userPhone = $(".phone").val();
            var salary = $(".salary").val();
            var address = $(".address").val();
            var latitude = parseFloat($('#address').attr('data-latitude'));
            var longitude = parseFloat($('#address').attr('data-longitude'));
            var itemPublish = $(".item_publish").is(":checked");
            var providerId = (provider_id != '') ? provider_id : $("#provider_select").val();

            if (userFirstName == '') {
                return showWorkerError("{{trans('lang.enter_worker_first_name_error')}}");
            }
            if (userLastName == '') {
                return showWorkerError("{{trans('lang.enter_worker_last_name_error')}}");
            }
            if (email == '') {
                return showWorkerError("{{trans('lang.enter_worker_email_error')}}");
            }
            if (password == '') {
                return showWorkerError("{{trans('lang.enter_worker_password_error')}}");
            }
            if (userPhone == '') {
                return showWorkerError("{{trans('lang.enter_worker_userphone_error')}}");
            }
            if (salary == '') {
                return showWorkerError("{{trans('lang.enter_worker_salary_error')}}");
            }
            if ((!latitude && latitude !== 0) || (!longitude && longitude !== 0) || isNaN(latitude) || isNaN(longitude)) {
                latitude = 0.01;
                longitude = 0.01;
            }
            if (providerId == '') {
                return showWorkerError("{{trans('lang.select_service_provider_error')}}");
            }

            jQuery("#data-table_processing").show();
            var user_id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('worker_' + Date.now());

            $.ajax({
                url: "{{ route('ondemand.workers.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: user_id,
                    firstName: userFirstName,
                    lastName: userLastName,
                    email: email,
                    password: password,
                    phoneNumber: userPhone,
                    salary: salary,
                    address: address,
                    latitude: latitude,
                    longitude: longitude,
                    profilePictureURL: ownerphoto || '',
                    active: itemPublish,
                    providerId: providerId
                },
                success: function (response) {
                    if (response.success) {
                        if (provider_id == '') {
                            window.location.href = '{{ route("ondemand.workers.index") }}';
                        } else {
                            window.location.href = '{{ url("/ondemand-workers") }}/' + provider_id;
                        }
                    } else {
                        jQuery("#data-table_processing").hide();
                        showWorkerError(response.error || 'Failed to create worker.');
                    }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Save failed';
                    showWorkerError(errMsg);
                }
            });
        });
    });

    function initialize(id) {
        if (mapType == "OFFLINE"){
            var input = document.getElementById('address');
            var autocompleteList = document.getElementById('autocomplete-list');
            input.addEventListener('input', function() {
                var query = this.value;
                if (query.length < 3) {
                    autocompleteList.innerHTML = '';
                    return;
                }

                fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        autocompleteList.innerHTML = '';
                        data.forEach(place => {
                            var item = document.createElement('div');
                            item.classList.add('autocomplete-item');
                            item.innerText = place.display_name;
                            item.onclick = function() {
                                input.value = place.display_name;
                                input.setAttribute('data-latitude', place.lat);
                                input.setAttribute('data-longitude', place.lon);
                                if (place.address) {
                                    var city = place.address.city || place.address.town || place.address.village || 'N/A';
                                    var state = place.address.state || 'N/A';
                                    var country = place.address.country || 'N/A';
                                    input.setAttribute('data-city', city);
                                    input.setAttribute('data-state', state);
                                    input.setAttribute('data-country', country);
                                }
                                autocompleteList.innerHTML = ''; // Clear the autocomplete list
                            };
                            autocompleteList.appendChild(item);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
            document.addEventListener('click', function(e) {
                if (e.target !== input) {
                    autocompleteList.innerHTML = '';
                }
            });
        }else{
            var input = document.getElementById(id);
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                var placeaddress = autocomplete.getPlace().address_components;
                var city = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['locality', 'political']))[0].long_name;
                var state = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['administrative_area_level_1', 'political']))[0].long_name;
                var country = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['country', 'political']))[0].long_name;
                $("#" + id).val(place.formatted_address).attr('data-latitude', place.geometry.location.lat()).attr('data-longitude', place.geometry.location.lng()).attr('data-city', city).attr('data-state', state).attr('data-country', country)
            });
        }
    }

    $(document).on("click", "#address", function () {
        var id = $(this).attr('id');
        initialize(id);
    });

    function handleFileSelectowner(evt) {
        var f = evt.target.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            ownerphoto = e.target.result;
            $("#uploaded_image_owner").attr('src', ownerphoto);
            $(".uploaded_image_owner").show();
        };
        reader.readAsDataURL(f);
    }
    window.handleFileSelectowner = handleFileSelectowner;


    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)
        ) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets3(event, msg) {
        if (!((event.which >= 48 && event.which <= 57) || (event.which >= 97 && event.which <= 122))) {
            document.getElementById(msg).innerHTML = "Special characters not accepted ";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
</script>

@endsection
