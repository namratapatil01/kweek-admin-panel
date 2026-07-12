@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.coupon_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    @if (empty($providerId))
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.coupons') !!}">{{ trans('lang.coupon_plural') }}</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.coupons', $providerId) !!}">{{ trans('lang.coupon_plural') }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ trans('lang.coupon_create') }}</li>
                </ol>
            </div>
        </div>

        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.coupon_create') }}</legend>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.coupon_code') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control coupon_code">
                                <div class="form-text text-muted">{{ trans('lang.coupon_code_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.coupon_discount_type') }}</label>
                            <div class="col-7">
                                <select id="coupon_discount_type" class="form-control">
                                    <option value="Percentage">{{ trans('lang.coupon_percent') }}</option>
                                    <option value="Fix Price">{{ trans('lang.coupon_fixed') }}</option>
                                </select>
                                <div class="form-text text-muted">{{ trans('lang.coupon_discount_type_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.coupon_discount') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control coupon_discount">
                                <div class="form-text text-muted">{{ trans('lang.coupon_discount_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.coupon_expires_at') }}</label>
                            <div class="col-7">
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type='text' class="form-control date_picker input-group-addon" />
                                </div>
                                <div class="form-text text-muted">{{ trans('lang.coupon_expires_at_help') }}</div>
                            </div>
                        </div>

                        @if (empty($providerId))
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.provider') }}</label>
                                <div class="col-7">
                                    <select id="provider_select" class="form-control">
                                        <option value="">{{ trans('lang.select_provider') }}</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider['id'] }}">{{ $provider['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.select_provider') }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.coupon_description') }}</label>
                            <div class="col-7">
                                <textarea rows="12" class="form-control coupon_description" id="coupon_description"></textarea>
                                <div class="form-text text-muted">{{ trans('lang.coupon_description_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.category_image') }}</label>
                            <div class="col-7">
                                <input type="file" onChange="handleFileSelect(event)">
                                <div class="placeholder_img_thumb coupon_image"></div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="coupon_enabled" id="coupon_enabled" checked>
                                <label class="col-3 control-label" for="coupon_enabled">{{ trans('lang.coupon_enabled') }}</label>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="coupon_public" id="coupon_public" checked>
                                <label class="col-3 control-label" for="coupon_public">{{ trans('lang.coupon_public') }}</label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            @if (empty($providerId))
                <a href="{!! route('ondemand.coupons') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            @else
                <a href="{!! route('ondemand.coupons', $providerId) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <script type="text/javascript">
        var photo = "";
        var provider_id = "{{ $providerId ?? '' }}";
        var section_id = (typeof getCookie === 'function' ? getCookie('section_id') : '') || '';

        $(document).ready(function () {
            $('#datetimepicker1').datepicker({ format: 'mm/dd/yyyy' });

            $(".save-form-btn").click(function () {
                var code = $(".coupon_code").val();
                var discount = $(".coupon_discount").val();
                var description = $(".coupon_description").val();
                var dateVal = $(".date_picker").val();
                var discountType = $("#coupon_discount_type").val();
                var isEnabled = $(".coupon_enabled").is(":checked");
                var isPublic = $(".coupon_public").is(":checked");
                var providerId = provider_id !== '' ? provider_id : $("#provider_select").val();

                if (code == '') {
                    $(".error_top").show().html("<p>{{ trans('lang.enter_coupon_code_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (discount == '') {
                    $(".error_top").show().html("<p>{{ trans('lang.enter_coupon_discount_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (!dateVal) {
                    $(".error_top").show().html("<p>{{ trans('lang.select_coupon_expdate_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (!providerId) {
                    $(".error_top").show().html("<p>{{ trans('lang.select_provider_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }

                var expiresAt = new Date(dateVal);
                expiresAt.setHours(23, 59, 59, 999);

                $.ajax({
                    url: '{{ route("ondemand.coupons.store") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        code: code,
                        discount: discount,
                        discountType: discountType,
                        description: description,
                        expiresAt: expiresAt.toISOString(),
                        isEnabled: isEnabled ? 1 : 0,
                        isPublic: isPublic ? 1 : 0,
                        providerId: providerId,
                        sectionId: section_id,
                        image: photo || ''
                    },
                    success: function () {
                        if (provider_id === '') {
                            window.location.href = '{{ route("ondemand.coupons") }}';
                        } else {
                            window.location.href = '{{ route("ondemand.coupons", $providerId ?? "") }}';
                        }
                    },
                    error: function (xhr) {
                        $(".error_top").show().html("<p>" + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to save coupon') + "</p>");
                        window.scrollTo(0, 0);
                    }
                });
            });
        });

        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            if (!f) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                photo = e.target.result;
                $(".coupon_image").html('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            };
            reader.readAsDataURL(f);
        }
    </script>
@endsection
