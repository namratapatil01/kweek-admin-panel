@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.document_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.document_table') }}</li>
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

        <div class="d-flex align-items-center mb-4">
            <span class="mr-2" style="font-size: 22px; display: inline-flex; align-items: center;">
                <i class="fa fa-folder-open-o" style="color: #4b5563;"></i>
            </span>
            <span class="section-header-title" style="font-size: 20px; font-weight: 600; color: #1a202c;">{{ trans('lang.document_plural') }}</span>
            <span class="count-badge ml-2 total_count" style="background-color: #ff3b30; color: #fff; border-radius: 12px; padding: 2px 10px; font-size: 12px; font-weight: 700;">0</span>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-2">
                            <div>
                                <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">{{ trans('lang.document_table') }}</h4>
                                <p class="mb-0 text-muted" style="font-size: 13px;">{{ trans('lang.documents_table_text') }}</p>
                            </div>
                            <div>
                                <a href="{{ route('documents.create') }}" class="btn btn-create-document">
                                    <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>{{ trans('lang.document_create') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table id="example24" class="table table-hover table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all" style="width: 90px; vertical-align: middle;">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="is_active" class="mr-2">
                                                    <label for="is_active" class="mr-2" style="margin-bottom: 0;"></label>
                                                    <a id="deleteAll" class="do_not_delete d-inline-flex align-items-center text-danger" href="javascript:void(0)" style="font-weight: 600; font-size: 13px; text-decoration: none;">
                                                        <i class="fa fa-trash mr-1"></i> {{ trans('lang.all') }}
                                                    </a>
                                                </div>
                                            </th>
                                            <th class="font-weight-bold" style="color: #2b354e;">{{ trans('lang.title') }}</th>
                                            <th class="font-weight-bold" style="color: #2b354e;">{{ trans('lang.document_for') }}</th>
                                            <th class="font-weight-bold" style="color: #2b354e;">{{ trans('lang.coupon_enabled') }}</th>
                                            <th class="font-weight-bold" style="color: #2b354e;">{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
$(function () {
    var table = $('#example24').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("documents.datatable") }}',
            type: 'GET',
            data: function (d) {
                d.sectionId = getCookie('section_id') || '';
            },
            dataSrc: function (json) {
                if (json.recordsTotal !== undefined) {
                    $('.total_count').text(json.recordsTotal);
                }
                setTimeout(function(){ $('[data-toggle="tooltip"]').tooltip(); }, 100);
                
                return (json.data || []).map(function(row) {
                    var idMatch = row[0].match(/data-id="([^"]+)"/);
                    var id = idMatch ? idMatch[1] : '';

                    var typeStr = String(row[3]);
                    if (typeStr.toLowerCase() === 'driver') typeStr = 'Individual Driver';
                    else if (typeStr.toLowerCase() === 'vendor') typeStr = 'Vendor';
                    else if (typeStr.toLowerCase() === 'owner') typeStr = 'Owner';

                    var isEnabled = row[4].indexOf('badge-success') !== -1;
                    
                    var checkboxHtml = '<input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label for="is_open_' + id + '"></label>';

                    var routeEdit = '{{ route("documents.edit", ":id") }}'.replace(':id', id);
                    var titleLinkHtml = '<a href="' + routeEdit + '" class="document-title-link">' + row[2] + '</a>';

                    var toggleHtml = '<label class="switch"><input type="checkbox" class="doc-enable-toggle" data-id="' + id + '" ' + (isEnabled ? 'checked' : '') + '><span class="slider round"></span></label>';

                    var actionsHtml = '<span class="action-btn-container">' +
                        '<a href="' + routeEdit + '" class="btn-circle-edit mr-2" data-toggle="tooltip" title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>' +
                        '<a href="javascript:void(0)" class="delete-row btn-circle-delete" data-id="' + id + '" data-toggle="tooltip" title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a>' +
                    '</span>';

                    return [ checkboxHtml, titleLinkHtml, typeStr, toggleHtml, actionsHtml ];
                });
            }
        },
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 3, 4] }
        ],
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: ''
        }
    });

    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#example24 .is_open:checked').length) {
            if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                var ids = [];
                $('#example24 .is_open:checked').each(function () {
                    ids.push($(this).attr('dataId'));
                });
                
                jQuery("#data-table_processing").show();
                $.ajax({
                    url: '{{ route("documents.bulk-destroy") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids
                    },
                    success: function () {
                        jQuery("#data-table_processing").hide();
                        $("#is_active").prop('checked', false);
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        jQuery("#data-table_processing").hide();
                        alert(xhr.responseJSON?.error || 'Bulk delete failed');
                    }
                });
            }
        } else {
            alert("{{ trans('lang.select_to_delete_alert') }}");
        }
    });

    $(document).on('click', '.delete-row', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ url("documents") }}/' + id,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });

    $(document).on('change', '.doc-enable-toggle', function () {
        var id = $(this).data('id');
        var isChecked = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{{ url("documents/status") }}/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                enable: isChecked
            },
            success: function(res) {
                // Done
            },
            error: function() {
                alert('Failed to update status');
                table.ajax.reload();
            }
        });
    });
});
</script>

<style>
    .btn-create-document {
        background-color: #000;
        color: #fff;
        border-radius: 20px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-create-document:hover {
        background-color: #2b354e;
        color: #fff;
        text-decoration: none;
    }
    .document-title-link {
        font-weight: 600;
        color: #000 !important;
        text-decoration: underline !important;
        font-size: 14px;
    }
    .document-title-link:hover {
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
        background-color: #e53e3e;
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
        background-color: #38a169;
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

    /* Actions styling */
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

    /* Pagination controls */
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        width: auto !important;
        padding: 5px 12px !important;
        border-radius: 4px !important;
    }

    /* Table styling to align checkboxes and center elements */
    #example24 td {
        vertical-align: middle;
    }
</style>
@endsection
