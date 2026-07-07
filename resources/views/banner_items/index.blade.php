@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.menu_items')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.menu_items')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <span class="mr-2" style="font-size: 22px; display: inline-flex; align-items: center;">
                <i class="mdi mdi-checkbox-blank-outline" style="color: #4b5563;"></i>
            </span>
            <span class="section-header-title">{{trans('lang.menu_items')}}</span>
            <span class="count-badge ml-2 total_count">{{ $bannersCount }}</span>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-2">
                            <div>
                                <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">{{trans('lang.menu_items')}}</h4>
                                <p class="mb-0 text-muted" style="font-size: 13px;">{{trans('lang.menu_items_table_text')}}</p>
                            </div>
                            <div> 
                                <a class="btn btn-create-banner" href="{!! route('banners.create') !!}">
                                    <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>{{trans('lang.menu_items_create')}}
                                </a>
                            </div>                
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="example24" class="table table-hover table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                        <?php if (in_array('banners.delete', json_decode(@session('user_permissions')))) { ?>
                                            <th class="delete-all" style="width: 90px; vertical-align: middle;">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="is_active" class="mr-2">
                                                    <a id="deleteAll" class="do_not_delete d-inline-flex align-items-center text-danger" href="javascript:void(0)" style="font-weight: 600; font-size: 13px; text-decoration: none;">
                                                        <i class="fa fa-trash mr-1"></i> {{trans('lang.all')}}
                                                    </a>
                                                </div>
                                            </th>
                                        <?php }?>
                                            <th>{{trans('lang.banner_info')}}</th>
                                            <th>{{trans('lang.banner_position')}}</th>
                                            <th>{{trans('lang.item_publish')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_vendors">
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

<style>
    /* Section Title & Badge */
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
    #example24 {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #example24 thead th {
        background-color: #fff !important;
        border-bottom: 2px solid #f4f6f9 !important;
        color: #4b5563 !important;
        font-weight: 600 !important;
        font-size: 14px;
        padding: 12px 16px !important;
        border-top: none !important;
    }
    #example24 tbody tr {
        background-color: #fff !important;
        transition: background-color 0.2s ease;
    }
    #example24 tbody td {
        padding: 16px !important;
        vertical-align: middle !important;
        border-top: 1px solid #f4f6f9 !important;
        border-bottom: 1px solid #f4f6f9 !important;
        font-size: 14px;
        color: #333;
    }
    #example24 tbody tr:hover {
        background-color: #fafbfc !important;
    }

    /* Banner Info cell styling */
    .banner-info-container {
        display: flex;
        align-items: center;
    }
    .banner-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        border: 1px solid #eef2f6;
    }
    .banner-name-link {
        font-weight: 600;
        color: #000 !important;
        text-decoration: underline !important;
        font-size: 14px;
    }
    .banner-name-link:hover {
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

    /* Actions styling (Blue circle edit button, Red circle delete button) */
    .action-btn-container {
        display: flex;
        align-items: center;
    }
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
    .btn-circle-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #ff3b30 !important;
        color: #ff3b30 !important;
        background-color: transparent !important;
        transition: all 0.2s ease;
    }
    .btn-circle-delete:hover {
        background-color: #ff3b30 !important;
        color: #fff !important;
        text-decoration: none;
    }
    .btn-circle-delete i {
        font-size: 16px;
    }

    /* Create Banner Button styling */
    .btn-create-banner {
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
    .btn-create-banner:hover {
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

    /* Checkbox & Delete All Styling */
    .delete-all input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #ff5e3a;
    }
    .delete-all label {
        margin-bottom: 0;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .is_open {
        width: 18px !important;
        height: 18px !important;
        cursor: pointer;
        accent-color: #ff5e3a;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    var table = $('#example24').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("banners.datatable") }}',
            type: 'GET',
            data: function (d) {
                d.sectionId = getCookie('section_id') || '';
            }
        },
        columns: [
            { data: 0, orderable: false },
            { data: 1, orderable: true },
            { data: 2, orderable: true },
            { data: 3, orderable: false },
            { data: 4, orderable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        }
    });

    $('#is_active').on('click', function () {
        $('.is_open').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '[name="vendor-delete"]', function () {
        var id = this.id;
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ url("banners") }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });

    // AJAX Status Toggle
    $(document).on('change', '[name="isSwitch"]', function () {
        var isChecked = $(this).is(':checked');
        var id = this.id;
        var switchElement = $(this);

        $.ajax({
            url: '{{ url("admin-data/upsert") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                collection: 'banner_items',
                id: id,
                data: { is_publish: isChecked },
                merge: true
            },
            success: function (response) {
                // Status updated successfully
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to update status');
                switchElement.prop('checked', !isChecked);
            }
        });
    });

    // Handle Bulk Delete
    $("#deleteAll").click(function () {
        var checkedIds = [];
        $('.is_open:checked').each(function() {
            checkedIds.push($(this).attr('dataId'));
        });

        if (checkedIds.length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                $.ajax({
                    url: '{{ route("banners.bulk-destroy") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: checkedIds
                    },
                    success: function() {
                        table.ajax.reload();
                        $('#is_active').prop('checked', false);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.error || 'Bulk delete failed');
                    }
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
});
</script>
@endsection
