@extends('layouts.app')
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
            <div class="overview-sec">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title d-flex">
                                    <div>
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.overview') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.see_overview_of_package_earning') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row subscription-list">
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        <a href="{!! route('subscription-plans.save') !!}" class="btn-primary btn rounded-full"><i
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
        var database = firebase.firestore();
        var ref = database.collection('subscription_plans');
       
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
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            decimal_degits = currencyData.decimal_degits;
        });
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        
        $(document).ready(async function() {
            
            getOverviewSection(section_id);
           
            $(document.body).on('click', '.redirecttopage', function() {
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
                    data: function(d) {
                        d.section_id = section_id;
                    }
                },
                columns: [
                    <?php if (in_array('subscription-plans.delete', json_decode(@session('user_permissions'), true))) { ?>
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (checkDeletePermission && row.isCommissionPlan != true) {
                                return '<input type="checkbox" id="is_open_' + row.id + '" class="is_open" dataId="' + row.id + '"><label class="col-3 control-label" for="is_open_' + row.id + '"></label>';
                            }
                            return '';
                        }
                    },
                    <?php } ?>
                    {
                        data: null,
                        render: function(data, type, row) {
                            var rowId = row.id || '';
                            var rowName = row.name || '';
                            var rowImage = row.image || placeholderImage;
                            var url = "{{ url('current-subscriber') }}/" + rowId;
                            return '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" style="width:70px;height:70px;" src="' + rowImage + '"> <a href="' + url + '" id="' + rowId + '">' + rowName + '</a>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
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
                        render: function(data, type, row) {
                            var expiry = row.expiryDay || '-1';
                            return expiry == '-1' ? "{{ trans('lang.unlimited') }}" : expiry + ' Days';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var rowId = row.id || '';
                            var url = "{{ url('current-subscriber') }}/" + rowId;
                            return '<a href="' + url + '">0</a>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.isCommissionPlan == true || row.isCommissionPlan === 'true') return '';
                            var isEnable = (row.isEnable == 1 || row.isEnable === true || row.isEnable === 'true');
                            var checked = isEnable ? 'checked' : '';
                            return '<label class="switch"><input type="checkbox" ' + checked + ' id="' + row.id + '" data-section="' + row.sectionId + '" name="isActive"><span class="slider round"></span></label>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var rowId = row.id || '';
                            var editRoute = "{{ url('subscription-plans/save') }}/" + rowId;
                            var html = '<span class="action-btn"><a href="' + editRoute + '" class="link-td" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
                            if (checkDeletePermission && row.isCommissionPlan != true && row.isCommissionPlan !== 'true') {
                                html += '<a id="' + rowId + '" class="link-td delete-btn direct-click-btn" name="plan-delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
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
                "language": {
                    "zeroRecords": "{{ trans('lang.no_record_found') }}",
                    "emptyTable": "{{ trans('lang.no_record_found') }}",
                    "processing": "" // Remove default loader
                },
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
            $('#search-input').on('input', debounce(function() {
                const searchValue = $(this).val();
                if (searchValue.length >= 3) {
                    $('#data-table_processing').show();
                    table.search(searchValue).draw();
                } else if (searchValue.length === 0) {
                    $('#data-table_processing').show();
                    table.search('').draw();
                }
            }, 300));
        });
        $(document).on("click", "input[name='isActive']", async function(e) {
            var ischeck = $(this).is(':checked');
            var sectionId = $(this).attr('data-section');
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

        $(document).on("click", "a[name='plan-delete']", async function(e) {
            var id = this.id;
            $.ajax({
                url: "{{ route('subscription-plans.delete') }}",
                type: 'POST',
                data: {
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    window.location.reload();
                }
            });
        });

        $("#is_active").click(function() {
            $("#subscriptionPlansTable .is_open").prop('checked', $(this).prop('checked'));
        });

        $("#deleteAll").click(function() {
            if ($('#subscriptionPlansTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    var ids = [];
                    $('#subscriptionPlansTable .is_open:checked').each(function() {
                        ids.push($(this).attr('dataId'));
                    });
                    $.ajax({
                        url: "{{ route('subscription-plans.bulk-delete') }}",
                        type: 'POST',
                        data: {
                            ids: ids,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            window.location.reload();
                        }
                    });
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });
        async function getTotalSubscriber(id) {
            var total = 0;
            await database.collection('users').where('subscriptionPlanId', '==', id).get()
                .then(async function(snapshots) {
                    total = snapshots.docs.length;
                });
            return total;
        }
        async function getSectionName(id) {
            var sectionName = '';
            await database.collection('sections').where('id', '==', id).get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    sectionName = data.name;
                }
            });
            return sectionName;
        }

        async function getOverviewSection(selectedSectionId){
            $.ajax({
                url: "{{ route('subscription-plans.overview') }}",
                type: "GET",
                data: { section_id: selectedSectionId },
                success: function(response) {
                    var html = '';
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(data) {
                            getEarnings(data.id);
                            var dName = data.name || (data.payload && data.payload.name) || 'Plan';
                            var img = data.image || (data.payload && data.payload.image) || placeholderImage;
                            html += ` <div class="col-md-4">
                                <div class="card card-box-with-icon">
                                    <div class="card-body">
                                        <span class="box-icon"><img src="${img}" onerror="this.src='${placeholderImage}'"></span>
                                        <div class="card-box-with-content mt-3">
                                        <h4 class="text-dark-2 mb-1 h4 earnings_${data.id}"></h4>
                                        <p class="mb-0 text-dark-2">${dName}</p>
                                        </div>
                                        <span class="background-img"><img src="${img}" onerror="this.src='${placeholderImage}'"></span>
                                    </div>
                                </div>
                            </div>`;
                        });
                        $('.subscription-list').html(html);
                    } else {
                        $('.subscription-list').html('');
                    }
                }
            });
        }
         
        function getEarnings(planId) {
            var total = 0;
            database.collection('subscription_history').where('subscription_plan.id', '==', planId).get().then(
                async function(snapshots) {
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
