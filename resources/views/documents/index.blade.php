@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Documents</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Document List</li>
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
            <span style="font-size: 20px; font-weight: 600; color: #1a202c;">Documents</span>
            <span class="total_count" style="background-color: #ffe4e1; color: #ff5722; border-radius: 50px; padding: 2px 10px; font-size: 13px; font-weight: bold; margin-left: 10px;">0</span>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-0" style="border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 20px 24px;">
                        <div>
                            <h4 class="mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">Document List</h4>
                            <p class="mb-0 text-muted" style="font-size: 13px;">View and manage all the documents</p>
                        </div>
                        <div>
                            <a href="{{ route('documents.create') }}" class="btn" style="background-color: #000; color: #fff; border-radius: 50px; padding: 10px 24px; font-weight: 700; font-size: 14px; border: none; display: inline-flex; align-items: center;">
                                <i class="mdi mdi-plus mr-1" style="font-size: 16px; font-weight: 700;"></i>Create a Document
                            </a>
                        </div>
                    </div>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <div class="table-responsive">
                            <table id="documentsTable" class="display nowrap table table-hover table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="delete-all" style="width: 90px; vertical-align: middle;">
                                            <input type="checkbox" id="is_active">
                                            <label for="is_active" style="margin-bottom:0; font-weight:bold; margin-left: 5px;">
                                                <a id="deleteAll" class="do_not_delete text-dark" href="javascript:void(0)" style="text-decoration: none; color:#000!important;">All</a>
                                            </label>
                                        </th>
                                        <th style="font-weight: 600; color: #4a5568;">Title</th>
                                        <th style="font-weight: 600; color: #4a5568;">Document For</th>
                                        <th style="font-weight: 600; color: #4a5568;">Enabled</th>
                                        <th style="font-weight: 600; color: #4a5568;">Actions</th>
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
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    var table = $('#documentsTable').DataTable({
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
                setTimeout(function () { $('[data-toggle="tooltip"]').tooltip(); }, 100);

                return (json.data || []).map(function (row) {
                    var idMatch = row[0].match(/data-id="([^"]+)"/);
                    var id = idMatch ? idMatch[1] : '';

                    var typeStr = String(row[3] || '');
                    if (typeStr.toLowerCase() === 'driver') typeStr = 'Individual Driver';
                    else if (typeStr.toLowerCase() === 'vendor') typeStr = 'Vendor';
                    else if (typeStr.toLowerCase() === 'owner') typeStr = 'Owner';

                    var isEnabled = String(row[4] || '').indexOf('badge-success') !== -1;
                    var toggleHtml = '<label class="switch"><input type="checkbox" class="doc-enable-toggle" data-id="' + id + '" ' + (isEnabled ? 'checked' : '') + '><span class="slider round"></span></label>';

                    var routeEdit = '{{ route("documents.edit", ":id") }}'.replace(':id', id);
                    var titleLink = '<a href="' + routeEdit + '" style="font-weight: 500; color: #3b82f6;">' + row[2] + '</a>';

                    var actionsHtml = '<span class="action-btn-circle-container">'
                        + '<a href="' + routeEdit + '" class="btn-circle btn-circle-edit" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></a>'
                        + '<a href="javascript:void(0)" class="btn-circle btn-circle-delete delete-row" data-id="' + id + '" data-toggle="tooltip" title="Delete"><i class="mdi mdi-delete"></i></a>'
                        + '</span>';

                    return [row[0], titleLink, typeStr, toggleHtml, actionsHtml];
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
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        }
    });

    $('#is_active').on('click', function () {
        $('#documentsTable .row-select').prop('checked', $(this).prop('checked'));
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
            error: function () {
                alert('Failed to update status');
                table.ajax.reload();
            }
        });
    });
});
</script>

<style>
/* Circle action buttons - exact match to reference */
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
.btn-circle-edit {
    color: #00b0ff;
    border-color: #00b0ff;
    background: #fff;
}
.btn-circle-edit:hover { background: #00b0ff; color: #fff !important; }
.btn-circle-delete {
    color: #ef5350;
    border-color: #ef5350;
    background: #fff;
}
.btn-circle-delete:hover { background: #ef5350; color: #fff !important; }

/* Switch toggle styling - exact match to reference */
.switch { position: relative; display: inline-block; width: 44px; height: 22px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #fff; border: 2px solid #ef4444; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 2px; bottom: 2px; background-color: #ef4444; transition: .4s; }
input:checked + .slider { background-color: #22c55e; border-color: #22c55e; }
input:checked + .slider:before { transform: translateX(22px); background-color: #fff; }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }
</style>
@endsection
