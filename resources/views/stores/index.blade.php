@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.store_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.vendor_table') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/store_list.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.store_plural') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control cuisine_selector filteredRecords" id="category_filter">
                                        <option value="" selected>{{ trans('lang.select_categoty') }}</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--1">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_stores') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--5">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.active_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--8">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_inactive_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.inactive_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/inactive_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--6">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 new_joined_rest">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.new_joined_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/new_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.store_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('stores.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_vendor') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="storeTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('stores.delete', json_decode(@session('user_permissions'),true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php } ?>
                                                <th>{{ trans('lang.actions') }}</th>
                                                <th>{{ trans('lang.store_info') }}</th>
                                                <th>{{ trans('lang.vendor_phone') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.item') }}</th>
                                                <th>{{ trans('lang.order_plural') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <div class="modal fade" id="create_vendor" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered notification-main" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{trans('lang.copy_vendor')}}
                        <span id="vendor_title_lable"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="data-table_processing2"
                            class="dataTables_processing panel panel-default"
                            style="display: none;">{{trans('lang.processing')}}
                    </div>
                    <div class="error_top"></div>
                    <!-- Form -->
                    <div class="form-row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.first_name')}}</label>
                            <div class="input-group">
                                <input placeholder="Name" type="text" id="user_name"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.last_name')}}</label>
                            <div class="input-group">
                                <input placeholder="Name" type="text" id="user_last_name"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.vendor_title')}}</label>
                            <div class="input-group">
                                <input placeholder="Vendor Title" type="text" id="vendor_title"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group"><label
                                class="form-label">{{trans('lang.email')}}</label><input
                                placeholder="Email" value="" id="user_email" type="text"
                                class="form-control"></div>
                        <div class="col-md-12 form-group"><label
                                class="form-label">{{trans('lang.password')}}</label><input
                                placeholder="Password" id="user_password" type="password"
                                class="form-control">
                        </div>
                    </div>
                    <!-- Form -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                            id="create_vendor_submit">{{trans('lang.create')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = JSON.parse(user_permissions);
        var checkDeletePermission = false;
        var checkCopyPermission  = false;

        if ($.inArray('stores.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        if ($.inArray('stores.copy', user_permissions) >= 0) {
            checkCopyPermission = true;
        }

        $(document).ready(function () {

            var table = $('#storeTable').DataTable({
                pageLength : 10,
                processing : true,
                serverSide : true,
                responsive : true,
                ajax: {
                    url : '{{ route("stores.datatable") }}',
                    type: 'GET',
                    data: function (d) {
                        d._token = '{{ csrf_token() }}';
                        d.category_id = $('.cuisine_selector').val();
                    },
                    dataSrc: function (json) {
                        var rows         = json.data || [];
                        var total        = json.recordsTotal || 0;
                        var active_count = 0, inactive_count = 0;

                        rows.forEach(function (row) {
                            if (row[3] && row[3].indexOf('badge-success') !== -1) {
                                active_count++;
                            } else {
                                inactive_count++;
                            }
                        });

                        $('.total_count').text(total);
                        $('.rest_count').text(total);
                        $('.rest_active_count').text(active_count);
                        $('.rest_inactive_count').text(inactive_count);
                        $('.new_joined_rest').text(0);

                        return rows;
                    }
                },
                order: [[2, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 1] }
                ],
                language: {
                    zeroRecords : '{{ trans("lang.no_record_found") }}',
                    emptyTable  : '{{ trans("lang.no_record_found") }}',
                    processing  : ''
                },
                dom: 'lfrtipB',
                buttons: [
                    {
                        extend   : 'collection',
                        text     : '<i class="mdi mdi-cloud-download"></i> {{ trans("lang.export_as") }}',
                        className: 'btn btn-info',
                        buttons  : [
                            { extend: 'excelHtml5', text: '{{ trans("lang.export_excel") }}' },
                            { extend: 'pdfHtml5',   text: '{{ trans("lang.export_pdf") }}'   },
                            { extend: 'csvHtml5',   text: '{{ trans("lang.export_csv") }}'   }
                        ]
                    }
                ],
                initComplete: function () {
                    $('.dataTables_filter').append($('.dt-buttons').detach());
                    $('.dataTables_filter input')
                        .attr('placeholder', 'Search here...')
                        .attr('autocomplete', 'new-password')
                        .val('');
                    $('.dataTables_filter label').contents().filter(function () {
                        return this.nodeType === 3;
                    }).remove();
                }
            });

            $('.cuisine_selector').on('change', function() {
                table.ajax.reload();
            });

            /* Bulk delete via select-all checkbox */
            $('#is_active').on('click', function () {
                $('#storeTable .is_open').prop('checked', $(this).prop('checked'));
            });

            $('#deleteAll').on('click', function () {
                var ids = [];
                $('#storeTable .is_open:checked').each(function () {
                    ids.push($(this).data('id'));
                });
                if (!ids.length) {
                    alert('{{ trans("lang.select_delete_alert") }}');
                    return;
                }
                if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;

                $.ajax({
                    url   : '{{ route("vendors.bulk-destroy") }}',
                    method: 'POST',
                    data  : { _token: '{{ csrf_token() }}', ids: ids },
                    success: function () { table.ajax.reload(); },
                    error  : function (xhr) { alert('Error: ' + xhr.responseText); }
                });
            });

            /* Single row delete */
            $(document).on('click', 'a[name="delete-btn"]', function () {
                var id = $(this).attr('id');
                if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;
                $.ajax({
                    url   : '{{ url("vendors") }}/' + id,
                    method: 'DELETE',
                    data  : { _token: '{{ csrf_token() }}' },
                    success: function () { table.ajax.reload(); },
                    error  : function (xhr) { alert('Error: ' + xhr.responseText); }
                });
            });

            var cloneVendorId = null;

            /* Show copy modal */
            $(document).on('click', 'a[name="vendor-clone"]', function () {
                var vendorId = $(this).attr('vendor_id');
                var vendorTitle = $(this).closest('tr').find('.redirecttopage').text();
                cloneVendorId = vendorId;

                // Clear previous inputs
                $('#user_name').val('');
                $('#user_last_name').val('');
                $('#vendor_title').val('');
                $('#user_email').val('');
                $('#user_password').val('');
                $('.error_top').html('');

                $('#vendor_title_lable').text(' : ' + vendorTitle);
                $('#create_vendor').modal('show');
            });

            /* Submit copy vendor */
            $('#create_vendor_submit').on('click', function () {
                var userName = $('#user_name').val().trim();
                var userLastName = $('#user_last_name').val().trim();
                var vendorTitle = $('#vendor_title').val().trim();
                var userEmail = $('#user_email').val().trim();
                var userPassword = $('#user_password').val().trim();

                if (!userName || !userLastName || !vendorTitle || !userEmail || !userPassword) {
                    $('.error_top').html('<div class="alert alert-danger">All fields are required.</div>');
                    return;
                }

                $('#data-table_processing2').show();
                $('.error_top').html('');

                $.ajax({
                    url: '{{ route("stores.clone") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        vendor_id: cloneVendorId,
                        user_name: userName,
                        user_last_name: userLastName,
                        vendor_title: vendorTitle,
                        user_email: userEmail,
                        user_password: userPassword
                    },
                    success: function (response) {
                        $('#data-table_processing2').hide();
                        $('#create_vendor').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        $('#data-table_processing2').hide();
                        var errMsg = xhr.responseJSON?.message || 'Failed to clone vendor.';
                        $('.error_top').html('<div class="alert alert-danger">' + errMsg + '</div>');
                    }
                });
            });
        });
    </script>

    <style>
        #data-table_processing.page-overlay {
            z-index: 99999 !important;
        }

        /* Circular action buttons — matches Sections page style */
        .store-action-btns {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .btn-circle-store {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: transparent;
            transition: all 0.2s ease;
            text-decoration: none !important;
            font-size: 16px;
        }
        /* Wallet — orange */
        .btn-circle-wallet {
            border: 1px solid #f97316;
            color: #f97316 !important;
        }
        .btn-circle-wallet:hover {
            background-color: #f97316;
            color: #fff !important;
        }
        /* Copy — green */
        .btn-circle-copy {
            border: 1px solid #10b981;
            color: #10b981 !important;
        }
        .btn-circle-copy:hover {
            background-color: #10b981;
            color: #fff !important;
        }
        /* View — purple */
        .btn-circle-view {
            border: 1px solid #a855f7;
            color: #a855f7 !important;
        }
        .btn-circle-view:hover {
            background-color: #a855f7;
            color: #fff !important;
        }
        /* Edit — cyan */
        .btn-circle-edit {
            border: 1px solid #5ac8fa;
            color: #5ac8fa !important;
        }
        .btn-circle-edit:hover {
            background-color: #5ac8fa;
            color: #fff !important;
        }
        /* Delete — red */
        .btn-circle-delete {
            border: 1px solid #ef4444;
            color: #ef4444 !important;
        }
        .btn-circle-delete:hover {
            background-color: #ef4444;
            color: #fff !important;
        }
    </style>

@endsection
