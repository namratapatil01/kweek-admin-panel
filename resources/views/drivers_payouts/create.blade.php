@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">

            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.drivers_payout_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/driversPayouts') }}">{{ trans('lang.drivers_payout_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ trans('lang.drivers_payout_create') }}</li>
                </ol>
            </div>
        </div>

        <div class="card-body">
            <div class="error_top"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.drivers_payout_create') }}</legend>
                        @if ($id == '')
                            <div class="form-group row width-50">
                                <label class="col-4 control-label">{{ trans('lang.drivers_payout_driver_id') }}</label>
                                <div class="col-7">
                                    <select id="select_vendor" class="form-control">
                                        <option value="">{{ trans('lang.select_driver') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.drivers_payout_driver_id_help') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($id == '')
                            <div class="form-group row width-50">
                            @else
                                <div class="form-group row width-100">
                        @endif
                        <label class="col-4 control-label">{{ trans('lang.drivers_payout_amount') }}</label>
                        <div class="col-7">
                            <input type="number" class="form-control payout_amount">
                            <div class="form-text text-muted">
                                {{ trans('lang.drivers_payout_amount_placeholder') }}
                            </div>
                        </div>
                </div>

                <div class="form-group row width-100">
                    <label class="col-2 control-label">{{ trans('lang.vendors_payout_note') }}</label>
                    <div class="col-12">
                        <textarea type="text" rows="7" class="form-control form-control payout_note"></textarea>
                    </div>
                </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
            {{ trans('lang.save') }}
        </button>
        @if ($id == '')
            <a href="{!! route('driversPayouts') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        @else
            <a href="{!! route('driver.payouts', $id) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        @endif
    </div>

    </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var driverID = "{{ $id }}";
        var driverWalletMap = {};
        var driverApiUrl = '/drivers/api/get-driver';
        var driversListUrl = '/driversPayouts_get-drivers';
        var payoutStoreUrl = '/driversPayouts/store';

        var emailTemplatesData = null;
        var adminEmail = '';
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;

        var userName = '';
        var userContact = '';
        var userEmail = '';

        $(document).ready(function() {
            $("#data-table_processing").show();

            if (driverID == '') {
                $.get(driversListUrl, function(res) {
                    if (res && res.data) {
                        res.data.forEach(function(driver) {
                            driverWalletMap[driver.id] = parseFloat(driver.wallet_amount) || 0;
                            $('#select_vendor').append($("<option></option>")
                                .attr("value", driver.id)
                                .text(driver.firstName + ' ' + driver.lastName));
                        });
                    }
                    $("#data-table_processing").hide();
                }).fail(function() {
                    $("#data-table_processing").hide();
                });
            } else {
                remainingPrice(driverID).finally(function() {
                    $("#data-table_processing").hide();
                });
            }
        });

        var payoutId = "<?php echo uniqid(); ?>";

        $(".save-form-btn").click(async function() {
            if (driverID == '') {
                driverID = $("#select_vendor").val();
            }
            if (!driverID) {
                $(".error_top").show().html("<p>{{ trans('lang.please_enter_details') }}</p>");
                return;
            }

            jQuery("#data-table_processing").show();

            var remaining = await remainingPrice(driverID);
            await getDriver(driverID);

            var amount = parseFloat($(".payout_amount").val());
            var note = $(".payout_note").val();

            if (amount <= 0 || isNaN(amount)) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>Please enter a valid payout amount.</p>");
                return;
            }

            if (remaining >= amount) {
                $.post(payoutStoreUrl, {
                    _token: "{{ csrf_token() }}",
                    id: payoutId,
                    driverID: driverID,
                    amount: amount,
                    note: note
                }, async function(res) {
                    if (res.success) {
                        jQuery("#data-table_processing").hide();
                        <?php if ($id == '') { ?>
                            window.location.href = "{{ route('driversPayouts') }}";
                        <?php } else { ?>
                            window.location.href = "{{ route('driver.payouts', $id) }}";
                        <?php } ?>
                    } else {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show().html("<p>" + (res.error || "An error occurred") + "</p>");
                    }
                }).fail(function(xhr) {
                    jQuery("#data-table_processing").hide();
                    var errMsg = "An error occurred while saving payout.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errMsg = xhr.responseJSON.error;
                    }
                    $(".error_top").show().html("<p>" + errMsg + "</p>");
                });
            } else {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>{{ trans('lang.driver_insufficient_payment_error') }}</p>");
            }
        });

        async function getDriver(driverId) {
            try {
                let res = await $.get(driverApiUrl + '/' + encodeURIComponent(driverId));
                if (res && res.data) {
                    var driverData = res.data;
                    userName = (driverData.firstName || '') + ' ' + (driverData.lastName || '');
                    userContact = driverData.phoneNumber || '';
                    userEmail = driverData.email || '';
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function remainingPrice(driverID) {
            if (driverWalletMap[driverID] !== undefined) {
                return driverWalletMap[driverID];
            }

            try {
                let res = await $.get(driverApiUrl + '/' + encodeURIComponent(driverID));
                if (res && res.data) {
                    var wallet = parseFloat(res.data.wallet_amount);
                    if (!isNaN(wallet)) {
                        driverWalletMap[driverID] = wallet;
                        return wallet;
                    }
                }
            } catch (err) {
                console.error(err);
            }

            return 0;
        }

    </script>
@endsection
