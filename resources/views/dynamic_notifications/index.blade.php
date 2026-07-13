@extends('layouts.app')

@section('content')
<style>
.table-responsive {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.table { margin-bottom: 0; }
.table thead th {
    background-color: #f9fafb !important;
    color: #111827 !important;
    font-weight: 700;
    border-top: none !important;
    border-bottom: 1px solid #e5e7eb !important;
    border-right: 1px solid #e5e7eb !important;
    padding: 15px;
}
.table thead th:last-child {
    border-right: none !important;
}
.table tbody td {
    vertical-align: middle;
    border-bottom: 1px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    color: #374151;
    padding: 15px;
}
.table tbody td:last-child {
    border-right: none;
}
div.dataTables_wrapper div.dataTables_filter input {
    border-radius: 20px;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    padding: 6px 36px 6px 15px;
    outline: none;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%239ca3af" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 12px center;
}
div.dataTables_wrapper div.dataTables_length select {
    border-radius: 20px;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    padding: 6px 30px 6px 15px;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="%236b7280" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.5 5.5l6 6 6-6"/></svg>');
    background-repeat: no-repeat;
    background-position: right 12px center;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover, .page-item.active .page-link {
    background: #000 !important; border-color: #000 !important; color: #fff !important; border-radius: 8px !important; box-shadow: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button, .page-link { border-radius: 8px; color: #6b7280 !important; border: none !important; }

/* Circular action buttons */
.action-btn-circle-container {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 16px;
    border: 1.5px solid;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}
.btn-circle i {
    border: none !important;
    padding: 0 !important;
    width: auto !important;
    height: auto !important;
    background: none !important;
    line-height: 1 !important;
}
.btn-circle:hover { transform: scale(1.1); }
.btn-circle-view {
    color: #fff !important;
    border-color: #000 !important;
    background: #000 !important;
}
.btn-circle-view:hover { background: #333 !important; border-color: #333 !important; color: #fff !important; }
.btn-circle-edit {
    color: #5ac8fa !important;
    border-color: #5ac8fa !important;
    background: #fff !important;
}
.btn-circle-edit:hover { background: #5ac8fa !important; color: #fff !important; }
</style>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor" style="font-weight: bold; color:#000;">App Notification</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active" style="color: #9ca3af;">Notifications List</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="admin-top-section mb-4"> 
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-2 justify-content-between">
                        <div class="d-flex top-title-left align-items-center">
                            <span class="icon mr-3"><img src="{{ asset('images/notification.png') }}" alt="icon" style="width:28px; height:28px;"></span>
                            <h3 class="mb-0" style="font-weight: 600;">App Notification</h3>
                            <span class="counter ml-3 notification_count" style="background:#ffe4e1; color:#ff5722; border-radius:50px; padding:2px 10px; font-weight:bold; font-size:13px; margin-left:10px;">0</span>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div>
                    <h3 class="text-dark-2 mb-2 h4" style="font-weight:700;">Notifications List</h3>
                    <p class="mb-0 text-dark-2" style="font-size:14px; color:#6b7280!important;">View and manage all the notifications</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="moduleTable" class="display nowrap table table-hover table-striped" width="100%">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th>Notification Type</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date Created</th>
                                <th class="no-export">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    var table = $('#moduleTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route($routePrefix . ".datatable") }}',
            type: 'GET'
        },
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ],
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        }
    });

    table.on('draw', function () {
        $('.notification_count').text(table.page.info().recordsTotal);
    });
});
</script>
@endsection
