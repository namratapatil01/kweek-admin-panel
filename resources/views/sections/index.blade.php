@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Section / Service</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">Section List</li>
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

        <div class="d-flex align-items-center mb-4">
            <span class="mr-2" style="font-size: 22px; display: inline-flex; align-items: center;"><i class="mdi mdi-view-grid" style="color: #4b5563;"></i></span>
            <span class="section-header-title">Section / Service</span>
            <span class="count-badge ml-2">{{ $sectionsCount }}</span>
        </div>

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-2">
                <div>
                    <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">Section List</h4>
                    <p class="mb-0 text-muted" style="font-size: 13px;">View and manage all the sections</p>
                </div>
                <div>
                    <a class="btn btn-create-section" href="{{ route('sections.create') }}">
                        <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>Create a Section
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="sectionTable" class="table table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Section Info</th>
                                <th>Service Type</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
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
    /* Section / Service title and count badge */
    .section-header-title {
        font-size: 20px;
        font-weight: 700;
        color: #2b354e;
    }
    .count-badge {
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
    #sectionTable {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #sectionTable thead th {
        background-color: #fff !important;
        border-bottom: 2px solid #f4f6f9 !important;
        color: #4b5563 !important;
        font-weight: 600 !important;
        font-size: 14px;
        padding: 12px 16px !important;
        border-top: none !important;
    }
    #sectionTable tbody tr {
        background-color: #fff !important;
        transition: background-color 0.2s ease;
    }
    #sectionTable tbody td {
        padding: 16px !important;
        vertical-align: middle !important;
        border-top: 1px solid #f4f6f9 !important;
        border-bottom: 1px solid #f4f6f9 !important;
        font-size: 14px;
        color: #333;
    }
    #sectionTable tbody tr:hover {
        background-color: #fafbfc !important;
    }

    /* Section Info cell styling */
    .section-info-container {
        display: flex;
        align-items: center;
    }
    .section-img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
        border: 1px solid #eef2f6;
    }
    .section-name-link {
        font-weight: 600;
        color: #000 !important;
        text-decoration: underline !important;
        font-size: 14px;
    }
    .section-name-link:hover {
        color: #ff5e3a !important;
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

    /* Actions styling (Blue circle edit button) */
    .btn-circle-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #5ac8fa !important;
        color: #5ac8fa !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
    }
    .btn-circle-edit:hover {
        background-color: #5ac8fa !important;
        color: #fff !important;
        text-decoration: none;
    }
    .btn-circle-edit i {
        font-size: 16px;
    }

    /* Create Section Button styling */
    .btn-create-section {
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
    .btn-create-section:hover {
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
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        line-height: 30px !important;
        text-align: center !important;
        margin: 0 3px !important;
        border: 1px solid transparent !important;
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
    var table = $('#sectionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("sections.datatable") }}',
            type: 'GET'
        },
        columns: [
            { data: 0, name: 'name', orderable: true },
            { data: 1, name: 'serviceType', orderable: true },
            { data: 2, name: 'isActive', orderable: false },
            { data: 3, name: 'actions', orderable: false, class: 'text-center' }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        },
        drawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // AJAX Status Toggle
    $(document).on('change', '.status-toggle', function () {
        var isChecked = $(this).is(':checked');
        var id = $(this).data('id');
        var switchElement = $(this);

        $.ajax({
            url: '{{ url("admin-data/upsert") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                collection: 'sections',
                id: id,
                data: { isActive: isChecked },
                merge: 1
            },
            success: function (response) {
                // Status updated successfully
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to update status');
                // Revert state if failed
                switchElement.prop('checked', !isChecked);
            }
        });
    });
});
</script>
@endsection
