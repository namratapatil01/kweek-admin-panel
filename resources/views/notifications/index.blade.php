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
.table th input[type="checkbox"],
.table td input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    cursor: pointer !important;
    display: inline-block !important;
    margin: 0 !important;
}
.table td:first-child, .table th:first-child {
    text-align: center !important;
    vertical-align: middle !important;
}
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; }
.action-btn .delete-row {
    color: #ef4444 !important; border: 1px solid #ef4444 !important; border-radius: 50% !important;
    width: 32px !important; height: 32px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;
    font-size: 16px !important; background-color: #fff !important; box-shadow: none !important; outline: none !important; padding: 0 !important; margin: 0 auto !important;
}
.action-btn .delete-row i {
    border: none !important; background: transparent !important; margin: 0 !important; padding: 0 !important;
}
.action-btn .delete-row:hover { background-color: #fef2f2 !important; }
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
.action-btn .delete-row {
    color: #ef4444; border: 1px solid #ef4444; border-radius: 50%;
    width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
    font-size: 16px; transition: all 0.2s; text-decoration: none; background-color: #fff;
}
.action-btn .delete-row:hover { background-color: #fef2f2; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover, .page-item.active .page-link {
    background: #000 !important; border-color: #000 !important; color: #fff !important; border-radius: 8px !important; box-shadow: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button, .page-link { border-radius: 8px; color: #6b7280 !important; border: none !important; }
</style>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor" style="font-weight: bold; color:#000;">Notifications</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active" style="color: #9ca3af;">Send Notification</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/notification.png') }}" alt="icon" style="width:24px; height:24px;"></span>
                            <h3 class="mb-0" style="font-weight: 600;">Notifications</h3>
                            <span class="counter ml-3 notification_count" style="background:#ffe4e1; color:#ff5722; border-radius:50px; padding:2px 10px; font-weight:bold; font-size:13px; margin-left:10px;">0</span>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div>
                    <h3 class="text-dark-2 mb-2 h4" style="font-weight:700;">Send Notification</h3>
                    <p class="mb-0 text-dark-2" style="font-size:14px; color:#6b7280!important;">View and manage all the notifications</p>
                </div>
                @if(!$readonly)
             <a class="btn btn-dark rounded-pill" style="border-radius: 50px; font-weight:500; background-color:#000; color:#fff; padding: 8px 20px; box-shadow: 0 8px 15px rgba(0,0,0,0.1); border:none; display:inline-flex; align-items:center;" href="{{ route($routePrefix . '.create') }}">
    <span style="font-size: 18px; margin-right: 6px; line-height: 1;">+</span> Create Notification
</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="moduleTable" class="display nowrap table table-hover table-striped" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <input type="checkbox" id="select-all" style="margin-top: 2px;">
                                        <label class="control-label" for="select-all" style="margin:0; font-weight:600; cursor:pointer; color:#111827; display:flex; align-items:center;">
                                            <i class="mdi mdi-delete" style="margin-right:4px; font-size: 16px; color:#000;"></i> All
                                        </label>
                                    </div>
                                </th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date Created</th>
                                <th style="width: 100px;">Actions</th>
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
            type: 'GET',
            data: function (d) {}
        },
        order: [[3, 'desc']], // Order by Date Created
        columnDefs: [
            { orderable: false, targets: [0, 4] }
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

    $('#select-all').on('click', function () {
        $('.row-select').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '.delete-row', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ url($routePrefix) }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });

    $('#select-all').closest('th').on('click', 'label', function() {
        if ($('.row-select:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                var ids = [];
                $('.row-select:checked').each(function () {
                    ids.push($(this).data('id'));
                });
                $.ajax({
                    url: '{{ route($routePrefix . ".destroy", "0") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE', ids: ids },
                    success: function () { table.ajax.reload(); },
                    error: function () { alert('Bulk delete failed'); }
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
});
</script>
@endsection
