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
            <span class="count-badge ml-2 total_count">0</span>
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
                                <table id="example24" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all" style="width: 90px; vertical-align: middle;">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="is_active" class="mr-2">
                                                    <label for="is_active" class="mr-2 doc-select-label"></label>
                                                    <a id="deleteAll" class="do_not_delete d-inline-flex align-items-center delete-all-link" href="javascript:void(0)">
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
                setTimeout(function () { $('[data-toggle="tooltip"]').tooltip(); }, 100);

                return (json.data || []).map(function (row) {
                    var idMatch = row[0].match(/data-id="([^"]+)"/);
                    var id = idMatch ? idMatch[1] : '';
                    var routeEdit = '{{ route("documents.edit", ":id") }}'.replace(':id', id);

                    var typeStr = String(row[3] || '');
                    if (typeStr.toLowerCase() === 'driver') typeStr = '{{ trans("lang.document_driver") }}';
                    else if (typeStr.toLowerCase() === 'vendor') typeStr = '{{ trans("lang.document_vendor") }}';
                    else if (typeStr.toLowerCase() === 'owner') typeStr = '{{ trans("lang.document_owner") }}';

                    var isEnabled = String(row[4] || '').indexOf('badge-success') !== -1;
                    var toggleHtml = '<label class="switch"><input type="checkbox" class="doc-enable-toggle" data-id="' + id + '" ' + (isEnabled ? 'checked' : '') + '><span class="slider round"></span></label>';

                    var titleHtml = id
                        ? '<a href="' + routeEdit + '" class="document-title-link">' + row[2] + '</a>'
                        : row[2];

                    var actionsHtml = '<span class="action-btn">'
                        + '<a href="' + routeEdit + '" data-toggle="tooltip" title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>'
                        + '<a href="javascript:void(0)" class="delete-row" data-id="' + id + '" data-toggle="tooltip" title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a>'
                        + '</span>';

                    var checkboxHtml = id
                        ? '<input type="checkbox" id="doc_select_' + id + '" class="row-select" data-id="' + id + '"><label class="mb-0" for="doc_select_' + id + '"></label>'
                        : '';

                    return [checkboxHtml, titleHtml, typeStr, toggleHtml, actionsHtml];
                });
            }
        },
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 3, 4] }
        ],
        createdRow: function (row) {
            $('td:eq(0)', row).addClass('delete-all');
        },
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: ''
        }
    });

    $('#is_active').on('click', function () {
        $('#example24 .row-select').prop('checked', $(this).prop('checked'));
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

    $('#deleteAll').on('click', function () {
        var checkedIds = [];
        $('#example24 .row-select:checked').each(function () {
            var id = $(this).data('id');
            if (id) {
                checkedIds.push(id);
            }
        });

        if (!checkedIds.length) {
            alert("{{ trans('lang.select_delete_alert') }}");
            return;
        }

        if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
            return;
        }

        $.ajax({
            url: '{{ route("documents.bulk-destroy") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ids: checkedIds
            },
            success: function () {
                table.ajax.reload();
                $('#is_active').prop('checked', false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.error || 'Bulk delete failed');
            }
        });
    });
});
</script>

<style>
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
.card {
    border-radius: 12px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
    border: 1px solid #eef2f6 !important;
    background-color: #fff;
}
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
.btn-create-document {
    background-color: #000 !important;
    color: #fff !important;
    border-radius: 30px !important;
    padding: 8px 20px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    border: none !important;
}
.btn-create-document:hover {
    color: #fff !important;
    opacity: 0.9;
}
.action-btn a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    margin-right: 6px;
    text-decoration: none !important;
}
.action-btn a:first-child {
    border: 1px solid #5ac8fa;
    color: #5ac8fa;
}
.action-btn a.delete-row {
    border: 1px solid #ff3b30;
    color: #ff3b30;
}
.action-btn a:hover {
    color: #fff !important;
}
.action-btn a:first-child:hover {
    background-color: #5ac8fa;
}
.action-btn a.delete-row:hover {
    background-color: #ff3b30;
}
.document-title-link {
    font-weight: 600;
    color: #000 !important;
    text-decoration: underline !important;
}
.document-title-link:hover {
    color: #ff5e3a !important;
}
.delete-all-link {
    color: #1a202c !important;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none !important;
}
.delete-all-link:hover {
    color: #000 !important;
}
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
    padding-left: 24px;
    height: 18px;
    line-height: 18px;
}
#example24 th.delete-all .doc-select-label {
    padding-left: 24px;
    height: 18px;
    line-height: 18px;
    margin-bottom: 0;
    min-width: 18px;
}
#example24 td.delete-all label {
    min-width: 18px;
}
#example24 th.delete-all input[type="checkbox"] + label:before,
#example24 td.delete-all input[type="checkbox"] + label:before {
    margin-top: 0;
    top: 0;
}
#example24 .row-select {
    cursor: pointer;
}
.switch { position: relative; display: inline-block; width: 46px; height: 24px; margin-bottom: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e53e3e; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; }
input:checked + .slider { background-color: #38a169; }
input:checked + .slider:before { transform: translateX(22px); }
.slider.round { border-radius: 24px; }
.slider.round:before { border-radius: 50%; }
</style>
@endsection
