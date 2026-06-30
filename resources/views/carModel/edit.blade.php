@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.edit_car_model')}}</h3>

        </div>



        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item"><a href="{!! route('carModel') !!}">{{trans('lang.car_model')}}</a>

                </li>

                <li class="breadcrumb-item active">{{trans('lang.edit_car_model')}}</li>

            </ol>

        </div>



        <div class="card-body">

            <div class="error_top"></div>



            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <legend>{{trans('lang.car_model')}}</legend>



                        <div class="form-group row width-50">

                            <label class="col-3 control-label">{{trans('lang.car_make')}}</label>

                            <div class="col-7 select2-container-full">

                                <select name="car_make" class="form-control car_make">

                                    <option value="">{{trans('lang.select')}}</option>

                                </select>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <label class="col-3 control-label">{{trans('lang.name')}}</label>

                            <div class="col-7">

                                <input type="text" class="form-control title" id="title">

                            </div>

                        </div>



                        <div class="form-group row width-100">

                            <div class="form-check">

                                <input type="checkbox" class="car_model_active" id="car_model_active">

                                <label class="col-3 control-label"

                                       for="car_model_active">{{trans('lang.active')}}</label>



                            </div>





                        </div>



                    </fieldset>

                </div>

            </div>

        </div>



        <div class="form-group col-12 text-center btm-btn">

            <button type="button" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i> {{

                trans('lang.save')}}

            </button>

            <a href="{!! url('carModel') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{

                trans('lang.cancel')}}</a>

        </div>



    </div>



</div>



@endsection



@section('scripts')



<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



<script type="text/javascript">
var id = "<?php echo $id; ?>";
var currentCarMakeId = "<?php echo isset($carModel) ? $carModel->car_make_id : ''; ?>";
var currentName = "<?php echo isset($carModel) ? $carModel->name : ''; ?>";
var currentIsActive = <?php echo isset($carModel) && $carModel->isActive ? 'true' : 'false'; ?>;

$(document).ready(function () {
    $('.title').val(currentName);
    if (currentIsActive) {
        $(".car_model_active").prop('checked', true);
    }
    
    $.get("{{ route('drivers.car-makes') }}", function (res) {
        res.carMakes && res.carMakes.forEach(function(data) {
            let option = $("<option></option>").attr("value", data.id).text(data.name);
            if (data.id == currentCarMakeId) {
                option.prop('selected', true);
            }
            $('.car_make').append(option);
        });
        $('.car_make').select2();
    });
});

$(".edit-setting-btn").click(function () {
    var title = $("#title").val();
    var car_make_id = $('.car_make').val();
    var active = $(".car_model_active").is(":checked");

    if (car_make_id == '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.car_make_error')}}</p>");
        window.scrollTo(0, 0);
    } else if (title == '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.name_error')}}</p>");
        window.scrollTo(0, 0);
    } else {
        jQuery("#data-table_processing").show();
        var payload = {
            _token: "{{ csrf_token() }}",
            id: id,
            name: title,
            car_make_id: car_make_id,
            isActive: active
        };

        $.ajax({
            url: "{{ url('carModel/update') }}/" + id,
            type: "POST",
            data: payload,
            success: function (response) {
                jQuery("#data-table_processing").hide();
                window.location.href = '{{ route("carModel") }}';
            },
            error: function (xhr, status, error) {
                jQuery("#data-table_processing").hide();
                var errMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : error;
                $(".error_top").show().html("<p>" + errMessage + "</p>");
                window.scrollTo(0, 0);
            }
        });
    }
});
</script>

@endsection