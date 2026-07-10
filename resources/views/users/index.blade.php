@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Top Section -->
        <div class="admin-top-section pt-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/users.png') }}" alt="Users"></span>
                            <div class="top-title-breadcrumb">
                                <div class="d-flex align-items-center mb-1">
                                    <h3 class="mb-0 restaurantTitle">{{ trans('lang.user_plural') }}</h3>
                                    <span class="badge badge-light-danger ml-2" id="total_users" style="background-color: #fee2e2; color: #ef4444; border-radius: 20px; font-weight: bold; padding: 5px 12px;">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-2 position-relative">
                                    <select class="form-control text-muted shadow-sm" id="statusFilter" style="border-radius: 20px; min-width: 120px; appearance: none; padding-right: 30px; background-color: #f8f9fa; border: 1px solid #e5e7eb;">
                                        <option value="">Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <i class="mdi mdi-chevron-down text-muted" style="position: absolute; right: 12px; top: 9px; font-size: 18px; pointer-events: none;"></i>
                                </div>
                                <div class="position-relative">
                                    <input type="text" class="form-control text-muted shadow-sm" id="dateFilter" style="border-radius: 20px; min-width: 160px; background-color: #f8f9fa; border: 1px solid #e5e7eb; padding-left: 35px; padding-right: 30px; cursor: pointer;" placeholder="Select range" readonly>
                                    <i class="mdi mdi-calendar-blank text-muted" style="position: absolute; left: 12px; top: 9px; font-size: 16px; pointer-events: none;"></i>
                                    <i class="mdi mdi-chevron-down text-muted" style="position: absolute; right: 12px; top: 9px; font-size: 18px; pointer-events: none;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card border rounded-lg">
            <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0 pt-4 px-4">
                <div class="card-header-title">
                    <h3 class="text-dark mb-1 h5 font-weight-bold">{{ trans('lang.user_plural') }} List</h3>
                    <p class="mb-0 text-muted small">View and manage all the users</p>
                </div>
                <div>
                    @if(!($module['readonly'] ?? false))
                    <a class="btn btn-dark text-white rounded-pill px-4 py-2" href="{{ route('users.create') }}" style="font-weight: 500;">
                        <i class="mdi mdi-plus mr-1"></i> Create a User
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body px-4">
                <div class="table-responsive">
                    <table id="moduleTable" class="display nowrap table table-hover" width="100%">
                        <thead>
                            <tr style="background-color: #f9f9f9; color: #333;">
                                <th style="border-top-left-radius: 10px; border-bottom-left-radius: 10px; width: 80px;" class="delete-all">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" id="is_active">
                                        <label class="control-label mb-0 mr-2" for="is_active" style="padding-left: 0; min-width: 20px;"></label>
                                        <a href="javascript:void(0)" id="deleteAll" class="text-dark font-weight-bold" style="text-decoration: none;">
                                            <i class="mdi mdi-delete" style="font-size: 16px; vertical-align: middle;"></i> All
                                        </a>
                                    </div>
                                </th>
                                <th>User Info</th>
                                <th>Contact Info</th>
                                <th>Date</th>
                                <th>Active</th>
                                <th style="border-top-right-radius: 10px; border-bottom-right-radius: 10px;">Actions</th>
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
        responsive: true,
        ajax: {
            url: '{{ route("users.datatable") }}',
            type: 'GET',
            data: function (d) {
                d.sectionId = getCookie('section_id') || '';
                d.status = $('#statusFilter').val();
                var dateRange = $('#dateFilter').val();
                if (dateRange) {
                    var dates = dateRange.split(' - ');
                    d.start_date = dates[0];
                    d.end_date = dates[1];
                }
            }
        },
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
            text: '<i class="mdi mdi-cloud-download mr-1"></i> Export as',
            className: 'btn text-white rounded shadow-sm px-4 ml-3',
            attr: {
                style: 'background-color: #ff6838; border-color: #ff6838;'
            },
            buttons: [
                { extend: 'excelHtml5', text: 'Excel' },
                { extend: 'pdfHtml5', text: 'PDF' },
                { extend: 'csvHtml5', text: 'CSV' }
            ]
        }],
        columnDefs: [
            { orderable: false, targets: [0, 1, 2, 4, 5] },
            { className: 'align-middle', targets: '_all' }
        ]
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

    // Checkbox toggling
    $('#is_active').on('click', function () {
        $('.row-select').prop('checked', $(this).is(':checked'));
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
            url: '{{ url("users/bulk-destroy") }}', // Assuming a bulk destroy route, generic uses normal destroy loop or bulk
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

    // Move search and buttons slightly to match UI
    table.on('init.dt', function() {
        var searchInput = $('.dataTables_filter input');
        searchInput.addClass('rounded-pill border-0 px-4 py-2').css({
            'background-color': '#f3f4f6',
            'min-width': '220px',
            'outline': 'none',
            'background-image': 'none' // Remove duplicate native background icon
        }).attr('placeholder', 'Search here..');
        
        $('.dataTables_filter label').contents().filter(function() { return this.nodeType === 3; }).remove();
        
        // Remove existing icon if added multiple times
        $('.dataTables_filter i').remove();
        
        var searchIcon = $('<i class="mdi mdi-magnify" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;"></i>');
        $('.dataTables_filter').css('position', 'relative').append(searchIcon);
        
        $('.dt-buttons .btn-collection').removeClass('btn-secondary').css('color', '#fff');
        
        $('.dataTables_length select').addClass('rounded-pill border-0 px-3 py-1 mx-2').css('background-color', '#f3f4f6');
    });
});
</script>
@endsection
