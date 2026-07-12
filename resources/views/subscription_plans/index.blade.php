@extends('layouts.app')

@section('style')
<style>
    /* Checkbox custom styling */
    #subscriptionPlansTable input[type="checkbox"] {
        position: relative !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        width: 18px !important;
        height: 18px !important;
        border: 1.5px solid #cbd5e0 !important;
        border-radius: 4px !important;
        outline: none !important;
        cursor: pointer !important;
        background-color: #fff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s ease-in-out !important;
        margin: 0 !important;
        opacity: 1 !important;
    }
    #subscriptionPlansTable input[type="checkbox"]:checked {
        background-color: #ff5c28 !important;
        border-color: #ff5c28 !important;
    }
    #subscriptionPlansTable input[type="checkbox"]:checked::after {
        content: '' !important;
        width: 5px !important;
        height: 9px !important;
        border: solid white !important;
        border-width: 0 2.5px 2.5px 0 !important;
        transform: rotate(45deg) !important;
        margin-bottom: 2px !important;
        display: block !important;
    }

    /* Action buttons circle styling */
    .action-btn-circle-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: transparent;
        transition: all 0.2s ease;
        text-decoration: none !important;
        font-size: 14px;
    }
    .btn-circle-edit {
        border: 1px solid #06b6d4;
        color: #06b6d4 !important;
    }
    .btn-circle-edit:hover {
        background-color: #06b6d4;
        color: #fff !important;
    }
    .btn-circle-delete {
        border: 1px solid #ef4444;
        color: #ef4444 !important;
    }
    .btn-circle-delete:hover {
        background-color: #ef4444;
        color: #fff !important;
    }

    /* Overview cards styling */
    .overview-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background-color: #fff;
        border: 1px solid #eef2f5 !important;
    }
    .overview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
    }
    .overview-icon-container {
        background: #f8fafc;
        border-radius: 50%;
        padding: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Black pill button styling */
    .btn-black-pill {
        background-color: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
        border-radius: 50px !important;
        padding: 8px 20px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s ease !important;
    }
    .btn-black-pill:hover {
        background-color: #333 !important;
        border-color: #333 !important;
        color: #fff !important;
    }

    .delete-all {
        min-width: 80px !important;
        white-space: nowrap !important;
    }
    .delete-all label {
        display: inline-flex !important;
        align-items: center !important;
        margin: 0 !important;
        padding: 0 !important;
        width: auto !important;
        max-width: none !important;
        flex: none !important;
    }
</style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{ trans('lang.subscription_plans') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.subscription_plans') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-items-center">
                                <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.subscription_plans') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overview-sec mb-4">
                <h3 class="text-dark-2 mb-1 h4">{{ trans('lang.overview') }}</h3>
                <p class="mb-3 text-muted">{{ trans('lang.see_overview_of_package_earning') }}</p>
                <div class="row subscription-list">
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.subscription_package_list') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.manage_all_package_in_single_click') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a href="{!! route('subscription-plans.save') !!}"
                                            class="btn-black-pill"><i
                                                class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_subscription_plan') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="subscriptionPlansTable"
                                        class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed"
                                        cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('subscription-plans.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                        class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                                class="mdi mdi-delete"></i>
                                                            {{ trans('lang.all') }}</a></label>
                                                </th>
                                                <?php } ?>
                                                <th>{{ trans('lang.plan_name') }}</th>
                                                <th>{{ trans('lang.plan_price') }}</th>
                                                <th>{{ trans('lang.duration') }}</th>
                                                <th>{{ trans('lang.current_subscriber') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var section_id = getCookie('section_id') || '';
        var database = kweekDb();
        var placeholderImage = '';

        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('subscription-plans.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var refCurrency = database.collection('currencies').where('isActive', '==', true);
        refCurrency.get().then(async function (snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            decimal_degits = currencyData.decimal_degits;
        });
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })

        function resolveImageUrl(url) {
            if (!url) {
                return placeholderImage;
            }
            if (url.indexOf('http://') === 0 || url.indexOf('https://') === 0) {
                return url;
            }
            if (url.indexOf('/') === 0) {
                return window.location.origin + url;
            }
            return url;
        }

        $(document).ready(async function () {
            getOverviewSection(section_id);

            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            jQuery("#data-table_processing").show();

            const table = $('#subscriptionPlansTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: {
                    url: "{{ route('subscription-plans.datatable') }}",
                    type: "GET",
                    data: function (d) {
                        d.section_id = section_id;
                    },
                    complete: function() {
                        jQuery("#data-table_processing").hide();
                    },
                    error: function() {
                        jQuery("#data-table_processing").hide();
                    }
                },
                columns: [
                    <?php if (in_array('subscription-plans.delete', json_decode(@session('user_permissions'), true))) { ?>
                        {
                        data: null,
                        render: function (data, type, row) {
                            if (checkDeletePermission && row.isCommissionPlan != true) {
                                return '<input type="checkbox" id="is_open_' + row.id + '" class="is_open" dataId="' + row.id + '"><label class="col-3 control-label" for="is_open_' + row.id + '"></label>';
                            }
                            return '';
                        }
                    },
                    <?php } ?>
                        {
                        data: null,
                        render: function (data, type, row) {
                            var rowId = row.id || '';
                            var rowName = row.name || '';
                            var rowImage = resolveImageUrl(row.image);
                            var url = "{{ url('current-subscriber') }}/" + rowId;
                            return '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" style="width:70px;height:70px;" src="' + rowImage + '"> <a href="' + url + '" id="' + rowId + '">' + rowName + '</a>';
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var rowType = row.type || '';
                            var rowPrice = row.price || 0;
                            if (rowType === 'free') {
                                return '<span style="color:red;">Free</span>';
                            }
                            return currencyAtRight ? parseFloat(rowPrice).toFixed(decimal_degits) + currentCurrency : currentCurrency + parseFloat(rowPrice).toFixed(decimal_degits);
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var expiry = row.expiryDay || '-1';
                            return expiry == '-1' ? "{{ trans('lang.unlimited') }}" : expiry + ' Days';
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var rowId = row.id || '';
                            var url = "{{ url('current-subscriber') }}/" + rowId;
                            return '<a href="' + url + '">0</a>';
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            if (row.isCommissionPlan == true || row.isCommissionPlan === 'true') return '';
                            var isEnable = (row.isEnable == 1 || row.isEnable === true || row.isEnable === 'true');
                            var checked = isEnable ? 'checked' : '';
                            return '<label class="switch"><input type="checkbox" ' + checked + ' id="' + row.id + '" data-section="' + row.sectionId + '" name="isActive"><span class="slider round"></span></label>';
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var rowId = row.id || '';
                            var editRoute = "{{ url('subscription-plans/save') }}/" + rowId;
                            var html = '<span class="action-btn-circle-container"><a href="' + editRoute + '" class="btn-circle btn-circle-edit" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
                            if (checkDeletePermission && row.isCommissionPlan != true && row.isCommissionPlan !== 'true') {
                                html += '<a id="' + rowId + '" class="btn-circle btn-circle-delete delete-btn" name="plan-delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
                            }
                            html += '</span>';
                            return html;
                        }
                    }
                ],
                order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
                columnDefs: [{
                    orderable: false,
                    targets: (checkDeletePermission) ? [0, 5, 6] : [4, 5]
                }],
                initComplete: function(settings, json) {
                    jQuery("#data-table_processing").hide();
                    if (json && typeof json.recordsFiltered !== 'undefined') {
                        $('.total_count').text(json.recordsFiltered);
                    }
                },
                "language": {
                    "zeroRecords": "{{ trans('lang.no_record_found') }}",
                    "emptyTable": "{{ trans('lang.no_record_found') }}",
                    "processing": ""
                },
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
            $('#search-input').on('input', debounce(function () {
                const searchValue = $(this).val();
                if (searchValue.length >= 3) {
                    $('#data-table_processing').show();
                    table.search(searchValue).draw();
                } else if (searchValue.length === 0) {
                    $('#data-table_processing').show();
                    table.search('').draw();
                }
            }, 300));

            $(document).on("click", "input[name='isActive']", async function (e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;

                $.ajax({
                    url: "{{ route('subscription-plans.store') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        isEnable: ischeck,
                        _token: '{{ csrf_token() }}'
                    }
                });
            });

            $(document).on("click", "a[name='plan-delete']", async function (e) {
                var id = this.id;
                $.ajax({
                    url: "{{ route('subscription-plans.delete') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function () {
                        window.location.reload();
                    }
                });
            });

            $("#is_active").click(function () {
                $("#subscriptionPlansTable .is_open").prop('checked', $(this).prop('checked'));
            });

            $("#deleteAll").click(function () {
                if ($('#subscriptionPlansTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        var ids = [];
                        $('#subscriptionPlansTable .is_open:checked').each(function () {
                            ids.push($(this).attr('dataId'));
                        });
                        $.ajax({
                            url: "{{ url('/subscription-plans/bulk-delete') }}",
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function () {
                                window.location.reload();
                            }
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });
        });

        async function getTotalSubscriber(id) {
            var total = 0;
            await database.collection('users').where('subscriptionPlanId', '==', id).get()
                .then(async function (snapshots) {
                    total = snapshots.docs.length;
                });
            return total;
        }
        async function getSectionName(id) {
            var sectionName = '';
            await database.collection('sections').where('id', '==', id).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    sectionName = data.name;
                }
            });
            return sectionName;
        }

        async function getOverviewSection(selectedSectionId) {
            $.ajax({
                url: "{{ route('subscription-plans.overview') }}",
                type: "GET",
                data: { section_id: selectedSectionId },
                success: function (response) {
                    var html = '';
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function (data) {
                            getEarnings(data.id);
                            var dName = data.name || (data.payload && data.payload.name) || 'Plan';
                            var img = resolveImageUrl(data.image || (data.payload && data.payload.image));
                            
                            html += ` <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card overview-card border-0 shadow-sm position-relative overflow-hidden" style="border-radius: 12px; height: 120px; background-color: #fff;">
                                        <div class="card-body d-flex align-items-center h-100 p-4">
                                            <div class="overview-icon-container mr-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                                <img src="${img}" onerror="this.onerror=null;this.src='${placeholderImage}'" style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                            <div class="overview-text">
                                                <h3 class="font-weight-bold mb-1 earnings_${data.id}" style="font-size: 1.5rem; color: #2b2b2b;"></h3>
                                                <span class="text-muted font-weight-medium" style="font-size: 0.9rem;">${dName}</span>
                                            </div>
                                        </div>
                                        <div class="position-absolute" style="bottom: -15px; right: -15px; width: 80px; height: 80px; opacity: 0.15; pointer-events: none;">
                                            <img src="${img}" onerror="this.onerror=null;this.src='${placeholderImage}'" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>`;
                        });
                        $('.subscription-list').html(html);
                    } else {
                        $('.subscription-list').html('<div class="col-12"><p class="text-muted">{{ trans("lang.no_active_plans") }}</p></div>');
                    }
                }
            });
        }

        function getEarnings(planId) {
            var total = 0;
            database.collection('subscription_history').where('subscription_plan.id', '==', planId).get().then(
                async function (snapshots) {
                    await snapshots.docs.map(async (listval) => {
                        var data = listval.data();
                        total += parseFloat(data.subscription_plan.price);
                    });
                    if (currencyAtRight) {
                        total = parseFloat(total).toFixed(decimal_degits) + currentCurrency;
                    } else {
                        total = currentCurrency + parseFloat(total).toFixed(decimal_degits);
                    }
                    $('.earnings_' + planId).html(total);
                });
        }
    </script>
@endsection