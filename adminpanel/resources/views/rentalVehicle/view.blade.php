@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                    <h3 class="mb-0 page-title">{{trans('lang.vehicle_plural')}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('rentalvehicle') !!}">{{trans('lang.vehicle_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vehicle_details')}}</li>
            </ol>
        </div>
    </div>
       
    <div class="container-fluid">

        <div class="resttab-sec mb-4">  
            <div class="menu-tab">
                <ul>
                    <li>
                        <a href="{{route('drivers.view',$id)}}"><i class="ri-list-indefinite"></i> {{trans('lang.tab_basic')}}</a>
                    </li>
                    <li class="active">
                        <a href="{{route('drivers.vehicle',$id)}}"><i class="ri-car-line"></i> {{trans('lang.vehicle')}}</a>
                    </li>
                    <li class="service_type_orders">

                    </li>
                    <li>
                        <a href="{{route('driver.payouts',$id)}}"><i class="ri-bank-card-line"></i> {{trans('lang.tab_payouts')}}</a>
                    </li>
                    <li>
                        <a href="{{route('payoutRequests.drivers.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i> {{trans('lang.tab_payout_request')}}</a>
                    </li>
                    <li>
                        <a href="{{route('users.walletstransaction',$id)}}" class="wallet_transaction"><i class="ri-wallet-line"></i> {{trans('lang.wallet_transaction')}}</a>
                    </li>
                </ul>
            </div>  
             
        </div>
        <div class="restaurant_info-section">
            <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.vehicle_details')}}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="restaurant_info_left">
                            <div class="d-flex mb-1">
                                <div class="sis-img car_image" id="car_image">
                                </div>
                                <div class="sis-content pl-4">
                                    <ul class="p-0 info-list mb-0">
                                        <div class="car_name_div" style="display:none"> 
                                            <li class="d-flex align-items-center mb-2 ">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.car_name')}}</label>
                                                <span class="car_name" id="car_name"></span>
                                            </li>
                                        </div>
                                        <div class="car_make_div">
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.car_make')}}</label>
                                                <span class="car_make" id="car_make"></span>
                                            </li>
                                        </div>
                                        <div class="parcel_delivery_div">
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.car_model')}}</label>
                                                <span class="car_model" id="car_model"></span>
                                            </li>
                                        </div>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.car_number')}}</label>
                                            <span class="car_number" id="car_number"> </span>
                                        </li>
                                        <div class="vehicle_type_div">
                                            <li class="d-flex align-items-center mb-2 mr-1">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.vehicle_type')}}</label>
                                                <span class="vehicle_type" id="vehicle_type"> </span>
                                            </li>
                                        </div>
                                        <div class="vehicle_type_div">
                                            <li class="d-flex align-items-center mb-2 mr-1 ">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.section')}}</label>
                                                <span class="cab_section_id" id="cab_section_id"> </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.car_color')}}</label>
                                                <span class="vehicle_color" id="vehicle_color"> </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type1" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.air_conditioning')}}</label>
                                                <span class="air_conditioning" id="air_conditioning"> </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_doors" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.doors')}}</label>
                                                <span class="doors" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_fuel_filling" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.fuel_filling')}}</label>
                                                <span class="fuel_filling" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_fuel_type" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.fuel_type')}}</label>
                                                <span class="fuel_type" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_gear" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.gear')}}</label>
                                                <span class="gear" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_max_power" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.max_power')}}</label>
                                                <span class="max_power" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_mileage" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" i>
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.mileage')}}</label>
                                                <span class="mileage" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_mph" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.mph')}}</label>
                                                <span class="mph" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_top_speed" style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.top_speed')}}</label>
                                                <span class="top_speed" > </span>
                                            </li>
                                        </div>
                                        <div id="div_service_type_passengers"  style="display:none">
                                            <li class="d-flex align-items-center mb-2 mr-1" >
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.passengers')}}</label>
                                                <span class="passengers" > </span>
                                            </li>
                                        </div>
                                      
                                    </ul>
                                </div>
                            </div>                            
                        </div>
                    </div>                    
                </div>
            </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">

            <a href="{!! route('users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

        </div>
    </div>    
</div>

@endsection

@section('scripts')

<script type="text/javascript">

    var id = "<?php echo $id;?>";
    var database = firebase.firestore();
    var ref = database.collection('users').where("id", "==", id);
    var photo = "";
    var vendorOwnerId = "";
    var vendorOwnerOnline = false;
    var cab_sections = database.collection('sections');

    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');

    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })

    $(document).ready(async function () {

        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {

            if(snapshots.docs.length>0){

                var dirver = snapshots.docs[0].data();

                $(".driver_name").text(dirver.firstName);
                $(".car_number").text(dirver.carNumber);

                $(".email").text(dirver.email);
                $(".phone").text(dirver.phoneNumber);
                var wallet_route = "{{route('users.walletstransaction','id')}}";
                $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID='+dirver.id));

                if (dirver.serviceType == "cab-service") {
                
                    $(".vehicle_color").text(dirver.carColor);
                    $(".car_model").text(dirver.carName);
                    $(".car_make").text(dirver.carMakes);
                    $(".vehicle_type").text(dirver.vehicleType);

                    cab_sections.get().then(async function (snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();

                            if (dirver.sectionId == data.id) {
                                $(".cab_section_id").text(data.name);
                            }
                        });
                    });


                }  else if (dirver.serviceType == "rental-service") {
                
                    $(".car_name_div").show();
                    $(".car_name").text(dirver.carName);
                    $(".vehicle_type").text(dirver.vehicleType);
                    $('.parcel_delivery_div').html('');
                    $(".vehicle_type_div").addClass("d-none");

                } else {

                    $(".car_name_div").show();
                    $(".car_name").text(dirver.carName);
                    $(".car_model").text(dirver.carMakes);
                    $(".vehicle_type_div").addClass("d-none");
                    $('#div_service_type').hide();
                    $('.parcel_delivery_div').html('');

                }

                if (dirver.companyName != "") {
                    $(".type").text('Company');
                    $(".company_details").show();
                    $(".company_address").text(dirver.companyAddress);
                    $(".company_name").text(dirver.companyName);
                } else {
                    $(".type").text('Individual');
                }
                var images = "";
                if (dirver.carInfo && dirver.carInfo.car_image && dirver.carInfo.car_image.length > 0) {
                    for (var i = 0; i < dirver.carInfo.car_image.length; i++) {
                        images += '<img width="200px" id="" height="auto" style="margin-right: 10px; margin-top: 10px" src="' + dirver.carInfo.car_image[i] + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    }
                } else {
                    images = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                }
                $(".car_image").html(images);

                var driver_image = "";
                if (dirver.profilePictureURL) {
                    driver_image = '<img width="200px" id="" height="auto" src="' + dirver.profilePictureURL + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else {
                    driver_image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                }

                $(".profile_image").html(driver_image);
                
                if (dirver.serviceType == "rental-service") {

                    if(dirver.carInfo != null){
                        $(".air_conditioning").text(dirver.carInfo.air_conditioning);
                        $('#div_service_type1').show();

                        $(".doors").text(dirver.carInfo.doors);
                        $('#div_service_type_doors').show();

                        $(".fuel_filling").text(dirver.carInfo.fuel_filling);
                        $('#div_service_type_fuel_filling').show();

                        $(".fuel_type").text(dirver.carInfo.fuel_type);
                        $('#div_service_type_fuel_type').show();

                        $(".gear").text(dirver.carInfo.gear);
                        $('#div_service_type_gear').show();

                        $(".max_power").text(dirver.carInfo.maxPower);
                        $('#div_service_type_max_power').show();

                        $(".mileage").text(dirver.carInfo.mileage);
                        $('#div_service_type_mileage').show();

                        $(".mph").text(dirver.carInfo.mph);
                        $('#div_service_type_mph').show();

                        $(".top_speed").text(dirver.carInfo.topSpeed);
                        $('#div_service_type_top_speed').show();

                        $(".passengers").text(dirver.carInfo.passenger);
                        $('#div_service_type_passengers').show();
                    }
                }
            
                if (dirver.serviceType == "cab-service") {

                    var url = "{{route('drivers.rides','driverId')}}";
                    url = url.replace('driverId', dirver.id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');

                } else if (dirver.serviceType == "rental-service") {
                    var url = "{{route('rental_orders.driver','id')}}";
                    url = url.replace("id", dirver.id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');

                } else if (dirver.serviceType == "delivery-service" || dirver.serviceType == "ecommerce-service") {
                    var url = "{{route('orders','id')}}";
                    url = url.replace("id", 'driverId=' + dirver.id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');

                } else if (dirver.serviceType == "parcel_delivery") {
                    var url = "{{route('parcel_orders.driver','id')}}";
                    url = url.replace("id", dirver.id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');
                }

            }else{
                $('.vehicle_detail_div').html('<h5 class="text-danger text-center font-weight-bold">{{trans("lang.vehicle_info_not_available")}}</h5>')
            }
            jQuery("#data-table_processing").hide();

        })

    })

</script>

@endsection
