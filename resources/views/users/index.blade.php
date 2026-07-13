@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.user_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.user_plural') }} List</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <span class="mr-2" style="font-size: 22px; display: inline-flex; align-items: center;"><i class="mdi mdi-account-outline" style="color: #4b5563;"></i></span>
                <span class="section-header-title">Users</span>
                <span class="count-badge ml-2" id="total_users">0</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="mr-2 position-relative">
                    <select class="form-control text-muted" id="statusFilter" style="border-radius: 20px; min-width: 120px; appearance: none; padding-right: 30px; background-color: #f8f9fa; border: 1px solid #e5e7eb; font-family: 'Urbanist', sans-serif; font-size: 14px;">
                        <option value="">Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <i class="mdi mdi-chevron-down text-muted" style="position: absolute; right: 12px; top: 9px; font-size: 18px; pointer-events: none;"></i>
                </div>
                <div class="position-relative">
                    <input type="text" class="form-control text-muted" id="dateFilter" style="border-radius: 20px; min-width: 160px; background-color: #f8f9fa; border: 1px solid #e5e7eb; padding-left: 35px; padding-right: 30px; cursor: pointer; font-family: 'Urbanist', sans-serif; font-size: 14px;" placeholder="Select range" readonly>
                    <i class="mdi mdi-calendar-blank text-muted" style="position: absolute; left: 12px; top: 9px; font-size: 16px; pointer-events: none;"></i>
                    <i class="mdi mdi-chevron-down text-muted" style="position: absolute; right: 12px; top: 9px; font-size: 18px; pointer-events: none;"></i>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-2">
                <div>
                    <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px; font-family: 'Urbanist', sans-serif;">Users List</h4>
                    <p class="mb-0 text-muted" style="font-size: 13px; font-family: 'Urbanist', sans-serif;">View and manage all the users</p>
                </div>
                <div>
                    <a class="btn btn-create-user" href="{{ route('users.create') }}">
                        <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>Create a User
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="moduleTable" class="table table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="delete-all">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" id="is_active" class="mr-2">
                                        <label class="control-label mb-0" for="is_active" style="padding-left: 0; min-width: 20px;"></label>
                                        <a href="javascript:void(0)" id="deleteAll" class="text-dark font-weight-bold" style="text-decoration: none;">
                                            <i class="mdi mdi-delete" style="font-size: 16px; vertical-align: middle;"></i> All
                                        </a>
                                    </div>
                                </th>
                                <th>User Info</th>
                                <th>Contact Info</th>
                                <th>Date</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700&display=swap');

    /* Section title and count badge */
    .section-header-title {
        font-family: 'Urbanist', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #2b354e;
    }
    .count-badge {
        font-family: 'Urbanist', sans-serif;
        background-color: #ffe9e3;
        color: #ff5e3a;
        font-weight: 700;
        font-size: 13px;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Card styling */
    .card {
        border-radius: 12px !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border: 1px solid #eef2f6 !important;
        background-color: #fff;
    }

    /* Table styling */
    #moduleTable {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #moduleTable thead th {
        font-family: 'Urbanist', sans-serif;
        background-color: #fff !important;
        border-bottom: 2px solid #f4f6f9 !important;
        color: #4b5563 !important;
        font-weight: 600 !important;
        font-size: 14px;
        padding: 12px 16px !important;
        border-top: none !important;
    }
    #moduleTable tbody tr {
        background-color: #fff !important;
        transition: background-color 0.2s ease;
    }
    #moduleTable tbody td {
        padding: 16px !important;
        vertical-align: middle !important;
        border-top: 1px solid #f4f6f9 !important;
        border-bottom: 1px solid #f4f6f9 !important;
        font-size: 14px;
        color: #333;
    }
    #moduleTable tbody tr:hover {
        background-color: #fafbfc !important;
    }

    /* Custom Switch Toggle styling (Green/Red) */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
        margin-bottom: 0;
        vertical-align: middle;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e53e3e; /* Red when unchecked */
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
    }
    input:checked + .slider {
        background-color: #38a169; /* Green when checked */
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    .slider.round {
        border-radius: 24px;
    }
    .slider.round:before {
        border-radius: 50%;
    }

    /* Actions Hover state */
    #moduleTable tbody td a[title="Wallet History"]:hover {
        background-color: #d97706 !important;
        color: #fff !important;
    }
    #moduleTable tbody td a[title="View"]:hover {
        background-color: #9333ea !important;
        color: #fff !important;
    }
    #moduleTable tbody td a[title="Edit"]:hover {
        background-color: #0284c7 !important;
        color: #fff !important;
    }
    #moduleTable tbody td a.delete-row:hover {
        background-color: #dc2626 !important;
        color: #fff !important;
    }

    /* Create User Button styling */
    .btn-create-user {
        background-color: #000000 !important;
        color: #ffffff !important;
        border-radius: 30px !important;
        padding: 8px 20px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    .btn-create-user:hover {
        background-color: #222 !important;
        color: #fff !important;
        text-decoration: none;
    }

    /* Customizing DataTables elements */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 6px 15px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #cbd5e1;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #000 !important;
        color: #fff !important;
        border: 1px solid #000 !important;
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        line-height: 30px !important;
        text-align: center !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:not(.previous):not(.next) {
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        line-height: 30px !important;
        text-align: center !important;
        margin: 0 3px !important;
        border: 1px solid transparent !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        margin: 0 3px !important;
        border: 1px solid transparent !important;
        padding: 6px 12px !important;
        cursor: pointer !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #000 !important;
        border: 1px solid #cbd5e1 !important;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    var table = $('#moduleTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("users.datatable") }}',
            type: 'GET',
            data: function (d) {
                d.status = $('#statusFilter').val();
                var dateRange = $('#dateFilter').val();
                if (dateRange) {
                    var dates = dateRange.split(' - ');
                    d.start_date = dates[0];
                    d.end_date = dates[1];
                }
            }
        },
        columns: [
            { data: 0, name: 'id', orderable: false },
            { data: 1, name: 'firstName', orderable: true },
            { data: 2, name: 'email', orderable: true },
            { data: 3, name: 'created_at', orderable: true },
            { data: 4, name: 'active', orderable: false },
            { data: 5, name: 'actions', orderable: false }
        ],
        order: [[3, 'desc']], // order by Date column
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        },
        dom: '<"row align-items-center mb-3"<"col-md-3"l><"col-md-9 d-flex justify-content-end align-items-center"fB>>rt<"row align-items-center mt-3"<"col-md-6"i><"col-md-6"p>>',
        buttons: [{
            extend: 'collection',
            text: '<i class="mdi mdi-cloud-download mr-1" style="font-size: 16px; vertical-align: middle;"></i> Export as <i class="mdi mdi-chevron-down ml-1" style="font-size: 16px; vertical-align: middle;"></i>',
            className: 'btn rounded-pill border-0 px-4 py-2 ml-3 shadow-none',
            attr: {
                style: 'background-color: #f1f5f9; color: #334155; font-weight: 500; font-family: \'Urbanist\', sans-serif; font-size: 14px;'
            },
            buttons: [
                { extend: 'excelHtml5', text: 'Excel' },
                { extend: 'pdfHtml5', text: 'PDF' },
                { extend: 'csvHtml5', text: 'CSV' }
            ]
        }]
    });

    // Update total count
    table.on('draw.dt', function () {
        var info = table.page.info();
        $('#total_users').text(info.recordsTotal);
    });

    // Handle filters
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });

    $('#dateFilter').daterangepicker({
        autoUpdateInput: false,
        opens: 'left',
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#dateFilter').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        table.ajax.reload();
    });

    $('#dateFilter').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        table.ajax.reload();
    });

    // Checkbox toggling (Select All)
    $('#is_active').on('click', function () {
        $('.row-select').prop('checked', $(this).is(':checked'));
    });

    // AJAX Status Toggle
    $(document).on('change', '.status-toggle', function () {
        var isChecked = $(this).is(':checked');
        var id = $(this).data('id');
        var switchElement = $(this);

        $.ajax({
            url: '{{ route("users.toggle-status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                active: isChecked ? 1 : 0
            },
            success: function (response) {
                // Status updated successfully
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.error || 'Failed to update status');
                // Revert state if failed
                switchElement.prop('checked', !isChecked);
            }
        });
    });

    // Delete all action
    $('#deleteAll').on('click', function () {
        var ids = [];
        $('.row-select:checked').each(function() {
            ids.push($(this).data('id'));
        });
        if (ids.length === 0) {
            alert('Please select at least one record');
            return;
        }
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;

        $.ajax({
            url: '{{ route("users.bulk-destroy") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: ids },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { 
                // Fallback to loop if bulk-destroy doesn't exist
                ids.forEach(function(id) {
                    $.ajax({
                        url: '{{ url("users") }}/' + id,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }
                    });
                });
                setTimeout(function(){ table.ajax.reload(); }, 1000);
            }
        });
    });

    // Delete single action
    $(document).on('click', '.delete-row', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ url("users") }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });

    // Align Search Box and Export Button to premium layout
    table.on('init.dt', function() {
        var searchInput = $('.dataTables_filter input');
        searchInput.addClass('rounded-pill border-0 px-4 py-2').css({
            'background-color': '#f1f5f9',
            'min-width': '220px',
            'outline': 'none',
            'font-family': "'Urbanist', sans-serif",
            'font-size': '14px',
            'color': '#334155',
            'background-image': 'none' // Remove duplicate native background icon
        }).attr('placeholder', 'Search here...');
        
        $('.dataTables_filter label').contents().filter(function() { return this.nodeType === 3; }).remove();
        
        // Remove existing icon if added multiple times
        $('.dataTables_filter i').remove();
        
        var searchIcon = $('<i class="mdi mdi-magnify" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;"></i>');
        $('.dataTables_filter').css({
            'position': 'relative',
            'display': 'inline-flex',
            'align-items': 'center'
        }).append(searchIcon);
        
        $('.dataTables_length select').addClass('rounded-pill border-0 px-3 py-1 mx-2').css({
            'background-color': '#f1f5f9',
            'font-family': "'Urbanist', sans-serif",
            'font-size': '14px',
            'outline': 'none'
        });

        // Detach and append the Export As button next to the search input
        $('.dataTables_filter').append($('.dt-buttons').detach());
        $('.dt-buttons').css({
            'display': 'inline-flex',
            'margin': '0'
        });
    });
});
</script>
@endsection
