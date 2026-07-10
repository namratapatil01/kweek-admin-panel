@extends('layouts.app')

@section('content')

    <div class="page-wrapper">

        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{ trans('lang.business_model_settings') }}</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item active">{{ trans('lang.business_model_settings') }}</li>

                </ol>

            </div>

        </div>

        <div class="card-body">

            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.subscription_based_model_settings') }}

                        </legend>

                        <div class="form-group row mt-1 ">

                            <div class="form-group row mt-1 ">

                                <div class="col-12 switch-box">

                                    <div class="switch-box-inner">

                                        <label class=" control-label">{{ trans('lang.subscription_based_model') }}</label>

                                        <label class="switch"> <input type="checkbox" name="subscription_model"

                                                id="subscription_model"><span class="slider round"></span></label>

                                        <i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"

                                            title="{{ trans('lang.subscription_tooltip') }}" aria-describedby="tippy-3"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </fieldset>



                    <fieldset>

                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.bulk_update') }}</legend>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{ trans('lang.select_section') }} <i

                                    class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"

                                    title="{{ trans('lang.bulk_update_commission_tooltip') }}"

                                    aria-describedby="tippy-3"></i>

                            </label>

                            <div class="col-7">

                                <select class="form-control section" id="section">

                                    <option value="">{{ trans('lang.select_section') }}</option>

                                </select>

                            </div>



                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{ trans('lang.select_user') }}</label>

                            <div class="col-7 selected-user">

                                <select id="food_restaurant_type" class="form-control" required>

                                    <option value="all">{{ trans('lang.all_user') }}</option>

                                    <option value="custom">{{ trans('lang.custom') }}</option>

                                </select>

                                <select id="food_restaurant" style="display:none" multiple class="form-control mt-3"

                                    required>

                                </select>

                            </div>

                        </div>



                        <div class="form-group row width-50">

                            <label class="col-4 control-label">{{ trans('lang.commission_type') }}</label>

                            <div class="col-7">

                                <select class="form-control bulk_commission_type" id="bulk_commission_type">

                                    <option value="percentage">{{ trans('lang.coupon_percent') }}</option>

                                    <option value="fixed">{{ trans('lang.coupon_fixed') }}</option>

                                </select>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <label class="col-4 control-label">{{ trans('lang.admin_commission') }}</label>

                            <div class="col-7">

                                <input type="number" value="0" class="form-control bulk_commission_fix">

                            </div>

                        </div>

                        <div class="form-group col-12 text-center">

                            <div class="col-12">

                                <button type="button" id="bulk_update_btn" class="btn btn-primary edit-setting-btn"><i

                                        class="fa fa-save"></i> {{ trans('lang.bulk_update') }}</button>

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

        </div>

        <style>

            .select2.select2-container {

                width: 100% !important;

                position: static;

                margin-top: 1rem;

            }

        </style>

    @endsection

    @section('scripts')

        <script>

            var currentServiceType = '';

            $(document).ready(function () {

                // ── 1. Load Sections from MySQL ──────────────────────────
                fetch('{{ route("settings.sections.list") }}')
                    .then(r => r.json())
                    .then(sections => {
                        sections.forEach(function (sec) {
                            var $optgroup = $('#section').find("optgroup[label='" + sec.serviceType + "']");
                            if ($optgroup.length === 0) {
                                $optgroup = $("<optgroup></optgroup>").attr("label", sec.serviceType);
                                $('#section').append($optgroup);
                            }
                            $optgroup.append(
                                $("<option></option>")
                                    .attr("value", sec.id)
                                    .attr("servicetype", sec.serviceType)
                                    .text(sec.name)
                            );
                        });
                    });

                // ── 2. When Section changes — load users/vendors from MySQL ──
                $('#section').on('change', function () {
                    var sectionId   = $(this).val();
                    currentServiceType = $(this).find('option:selected').attr('servicetype') || '';

                    $('#food_restaurant').empty();

                    if (!sectionId) return;

                    fetch('{{ route("settings.vendors.by-section") }}?section_id=' + sectionId + '&service_type=' + encodeURIComponent(currentServiceType))
                        .then(r => r.json())
                        .then(function (items) {
                            items.forEach(function (item) {
                                $('#food_restaurant').append(
                                    $("<option></option>").attr("value", item.id).text(item.label)
                                );
                            });
                            // Refresh select2 if open
                            if ($('#food_restaurant').hasClass('select2-hidden-accessible')) {
                                $('#food_restaurant').trigger('change');
                            }
                        });
                });

                // ── 3. Custom / All toggle ──────────────────────────────
                $('#food_restaurant_type').on('change', function () {
                    if ($(this).val() === 'custom') {
                        $('#food_restaurant').show();
                        $('#food_restaurant').empty();

                        // Load ALL vendors/providers regardless of section
                        var sectionId = $('#section').val();
                        var url = '{{ route("settings.vendors.by-section") }}?mode=all&service_type=' + encodeURIComponent(currentServiceType);

                        fetch(url)
                            .then(r => r.json())
                            .then(function (items) {
                                items.forEach(function (item) {
                                    $('#food_restaurant').append(
                                        $('<option></option>').attr('value', item.id).text(item.label)
                                    );
                                });
                                $('#food_restaurant').select2({
                                    placeholder: "{{ trans('lang.select_user') }}",
                                    allowClear: true,
                                    width: '100%',
                                    dropdownAutoWidth: true
                                });
                            });
                    } else {
                        $('#food_restaurant').hide();
                        if ($('#food_restaurant').hasClass('select2-hidden-accessible')) {
                            $('#food_restaurant').select2('destroy');
                        }
                    }
                });

                // ── 4. Load current subscription model state from MySQL ──
                jQuery("#data-table_processing").show();
                fetch('{{ url("admin-data/document/settings/vendor") }}')
                    .then(r => r.json())
                    .then(function (res) {
                        var data = res.data || {};
                        if (data.subscription_model) {
                            $("#subscription_model").prop('checked', true);
                        }
                        jQuery("#data-table_processing").hide();
                    }).catch(() => jQuery("#data-table_processing").hide());

                // ── 5. Subscription Model toggle ─────────────────────────
                $(document).on("click", "input[name='subscription_model']", function (e) {
                    var subscription_model = $("#subscription_model").is(":checked");
                    var userConfirmed = confirm(subscription_model ?
                        "{{ trans('lang.enable_subscription_plan_confirm_alert') }}" :
                        "{{ trans('lang.disable_subscription_plan_confirm_alert') }}"
                    );
                    if (!userConfirmed) {
                        $(this).prop("checked", !subscription_model);
                        return;
                    }
                    fetch('{{ url("admin-data/upsert") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ collection: 'settings', id: 'vendor', data: { subscription_model: subscription_model }, merge: true })
                    }).then(() => {
                        Swal.fire('Update Complete!', subscription_model ? 'Subscription model enabled.' : 'Subscription model disabled.', 'success');
                    });
                });

                // ── 6. Bulk Update ───────────────────────────────────────
                $('#bulk_update_btn').on('click', async function () {
                    const commissionType = $("#bulk_commission_type").val();
                    const fixCommission  = parseFloat($(".bulk_commission_fix").val());
                    const adminCommission = { commission: fixCommission, enable: true, type: commissionType };

                    const foodRestaurantType = $('#food_restaurant_type').val();
                    const selectedIds        = $('#food_restaurant').val() || [];
                    const sectionId          = $('#section').val();

                    if (!sectionId) {
                        Swal.fire('Please select section!', '', 'warning');
                        return;
                    }

                    // Determine IDs to update
                    var idsToUpdate = [];

                    if (foodRestaurantType === 'all') {
                        // Fetch vendors filtered by section (mode=section)
                        const res = await fetch('{{ route("settings.vendors.by-section") }}?mode=section&section_id=' + sectionId + '&service_type=' + encodeURIComponent(currentServiceType));
                        const items = await res.json();
                        idsToUpdate = items.map(i => i.id);
                    } else {
                        idsToUpdate = selectedIds;
                    }

                    if (idsToUpdate.length === 0) {
                        Swal.fire('No vendors selected or found!', '', 'warning');
                        return;
                    }

                    Swal.fire({ title: 'Processing...', text: '0% Complete', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    const collection = currentServiceType.includes('On Demand') ? 'app_users' : 'vendors';
                    let processed = 0;

                    for (const id of idsToUpdate) {
                        await fetch('{{ url("admin-data/upsert") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ collection: collection, id: id, data: { adminCommission: adminCommission }, merge: true })
                        });
                        processed++;
                        Swal.update({ text: `${Math.round((processed / idsToUpdate.length) * 100)}% Complete` });
                    }

                    Swal.fire('Update Complete!', `${idsToUpdate.length} users updated.`, 'success');
                });

            });

            function ShowHideDiv() {
                var checkboxValue = $("#enable_commission").is(":checked");
                if (checkboxValue) {
                    $(".admin_commision_detail").show();
                } else {
                    $(".admin_commision_detail").hide();
                }
            }

        </script>

    @endsection