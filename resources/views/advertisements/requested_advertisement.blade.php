@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.advertisement_requests') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.advertisement_requests') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.advertisement_requests') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.advertisement_request_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.advertisement_table_text') }}</p>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="card-header">

                                    <ul class="nav nav-pills mb-3" role="tablist">

                                        <li class="nav-item">

                                            <a class="nav-link new_request_list active" data-toggle="pill" href="#new_request_list" role="tab">{{ trans('lang.new_requests') }}</a>

                                        </li>
                                        <li class="nav-item">

                                            <a class="nav-link updated_request_list " data-toggle="pill" href="#updated_request_list" role="tab">{{ trans('lang.update_requests') }}</a>

                                        </li>

                                        <li class="nav-item">

                                            <a class="nav-link canceled_request_list" data-toggle="pill" href="#canceled_request_list" role="tab">{{ trans('lang.canceled_requests') }}</a>

                                        </li>

                                    </ul>

                                </div>
                                <div class="table-responsive m-t-10">
                                    <div class="tab-content">

                                        <div class="tab-pane active" id="new_request_list" role="tabpanel">

                                            <div class="table-responsive">

                                                <table id="newRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">

                                                    <thead>

                                                        <tr>

                                                            <th class="delete-all"><input type="checkbox" id="del_new"><label class="col-3 control-label" for="del_new"><a id="deleteAllNew" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>

                                                            <th>{{ trans('lang.actions') }}</th>

                                                        </tr>

                                                    </thead>

                                                    <tbody id="new_request_row"></tbody>

                                                </table>

                                            </div>

                                        </div>

                                        <div class="tab-pane" id="updated_request_list" role="tabpanel">

                                            <div class="table-responsive">

                                                <table id="updateRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">

                                                    <thead>

                                                        <tr>

                                                            <th class="delete-all"><input type="checkbox" id="del_updated"><label class="col-3 control-label" for="del_updated"><a id="deleteAllUpdated" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>

                                                            <th>{{ trans('lang.actions') }}</th>

                                                        </tr>

                                                    </thead>

                                                    <tbody id="update_request_row"></tbody>

                                                </table>

                                            </div>

                                        </div>

                                        <div class="tab-pane" id="canceled_request_list" role="tabpanel">

                                            <div class="table-responsive">

                                                <!--<div class="dropdown text-right">

                                                                                                                                                                    <button class="btn dropdown-toggle custom-export-btn" type="button" id="exportDropdown" data-toggle="dropdown" aria-expanded="false">

                                                                                                                                                                        <i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}

                                                                                                                                                                    </button>

                                                                                                                                                                    <ul class="dropdown-menu " aria-labelledby="exportDropdown">

                                                                                                                                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportBookingData('today_bookings','excel')">{{ trans('lang.export_excel') }}</a></li>

                                                                                                                                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportBookingData('today_bookings','pdf')">{{ trans('lang.export_pdf') }}</a></li>

                                                                                                                                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportBookingData('today_bookings','csv')">{{ trans('lang.export_csv') }}</a></li>

                                                                                                                                                                    </ul>

                                                                                                                                                                </div>-->

                                                <table id="canceledRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">

                                                    <thead>

                                                        <tr>

                                                            <th class="delete-all"><input type="checkbox" id="del_canceled"><label class="col-3 control-label" for="del_canceled"><a id="deleteAllCancelled" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>

                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>

                                                    </thead>

                                                    <tbody id="canceled_request_row"></tbody>

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
$(document).ready(function () {
    var checkDeletePermission = false;
    var user_permissions = '<?php echo @session("user_permissions"); ?>';
    if (user_permissions) {
        user_permissions = Object.values(JSON.parse(user_permissions));
        if ($.inArray('advertisements.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
    }

    var tableNew = mainDataTable('#newRequestTable', 'pending');
    var tableUpdated = mainDataTable('#updateRequestTable', 'updated');
    var tableCanceled = mainDataTable('#canceledRequestTable', 'canceled');

    $(document).on('click', '.new_request_list', function() { tableNew.ajax.reload(); });
    $(document).on('click', '.updated_request_list', function() { tableUpdated.ajax.reload(); });
    $(document).on('click', '.canceled_request_list', function() { tableCanceled.ajax.reload(); });

    function mainDataTable(tableName, statusQuery) {
        return $(tableName).DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("advertisements.datatable") }}',
                type: 'GET',
                data: function (d) {
                    d._token = '{{ csrf_token() }}';
                    d.status = statusQuery;
                },
                dataSrc: function (json) {
                    if (json.recordsTotal !== undefined) {
                        $('.total_count').text(json.recordsTotal);
                    }
                    var formattedData = [];
                    var rows = json.data || [];
                    for(var i=0; i<rows.length; i++){
                        var row = rows[i];
                        formattedData.push([ row[0], row[1], row[2], row[3], row[4], row[7] ]);
                    }
                    return formattedData;
                }
            },
            order: [[1, 'desc']],
            columnDefs: [{ orderable: false, targets: [0, 5] }],
            language: {
                zeroRecords: '{{ trans("lang.no_record_found") }}',
                emptyTable: '{{ trans("lang.no_record_found") }}',
                processing: ''
            }
        });
    }

    $("#del_new").click(function() { $('#newRequestTable .ad-checkbox').prop('checked', $(this).prop('checked')); });
    $("#del_updated").click(function() { $('#updateRequestTable .ad-checkbox').prop('checked', $(this).prop('checked')); });
    $("#del_canceled").click(function() { $('#canceledRequestTable .ad-checkbox').prop('checked', $(this).prop('checked')); });

    // Handle delete all actions
    function deleteBulk(tableSelector, tableObj) {
        var ids = [];
        $(tableSelector + ' .ad-checkbox:checked').each(function() {
            ids.push($(this).data('id'));
        });
        if (ids.length) {
            if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                $.post('{{ route("advertisements.destroy") }}', { _token: '{{ csrf_token() }}', id: ids }).done(function() {
                    tableObj.ajax.reload();
                });
            }
        } else {
            alert("{{ trans('lang.select_delete_alert') }}");
        }
    }
    
    $("#deleteAllNew").click(function() { deleteBulk('#newRequestTable', tableNew); });
    $("#deleteAllUpdated").click(function() { deleteBulk('#updateRequestTable', tableUpdated); });
    $("#deleteAllCancelled").click(function() { deleteBulk('#canceledRequestTable', tableCanceled); });

    $(document).on('click', '.btn-delete-ad', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;
        $.post('{{ route("advertisements.destroy") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }).done(function () {
            tableNew.ajax.reload();
            tableUpdated.ajax.reload();
            tableCanceled.ajax.reload();
        }).fail(function () {
            alert('Delete failed');
        });
    });
});
        </script>
    @endsection
