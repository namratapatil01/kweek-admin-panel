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
                    if (typeStr.toLowerCase() === 'driver') typeStr = '{{ trans("lang.document_driver") }}';
                    else if (typeStr.toLowerCase() === 'vendor') typeStr = '{{ trans("lang.document_vendor") }}';
                    else if (typeStr.toLowerCase() === 'owner') typeStr = '{{ trans("lang.document_owner") }}';

                    var isEnabled = String(row[4] || '').indexOf('badge-success') !== -1;
                    var toggleHtml = '<label class="switch"><input type="checkbox" class="doc-enable-toggle" data-id="' + id + '" ' + (isEnabled ? 'checked' : '') + '><span class="slider round"></span></label>';

                    var routeEdit = '{{ route("documents.edit", ":id") }}'.replace(':id', id);
                    var actionsHtml = '<span class="action-btn">'
                        + '<a href="' + routeEdit + '" data-toggle="tooltip" title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>'
                        + '<a href="javascript:void(0)" class="delete-row" data-id="' + id + '" data-toggle="tooltip" title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a>'
                        + '</span>';

                    return [row[0], row[2], typeStr, toggleHtml, actionsHtml];
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
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #dc3545; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: #28a745; }
input:checked + .slider:before { transform: translateX(20px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }
</style>
@endsection
