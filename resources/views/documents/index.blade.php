@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Documents</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">Documents</li>
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

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/folder.png') }}" onerror="this.src='{{ asset('images/default_user.png') }}'"></span>
                            <h3 class="mb-0">Documents</h3>
                            <span class="counter ml-3 total_count badge badge-warning" style="font-size:14px;padding:6px 12px;border-radius:50px;">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div>
                    <h3 class="text-dark-2 mb-2 h4">Document List</h3>
                    <p class="mb-0 text-dark-2">View and manage all the documents</p>
                </div>
                <a class="btn btn-dark rounded-pill text-white" href="{{ route('documents.create') }}">
                    <i class="mdi mdi-plus mr-2"></i>Create a Document
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="documentTable" class="display nowrap table table-hover table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"> All</th>
                                <th>{{ trans('lang.title') }}</th>
                                <th>Document For</th>
                                <th>Enabled</th>
                                <th>{{ trans('lang.actions') }}</th>
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
<style>
/* Adjust switch size if needed */
.switch { position: relative; display: inline-block; width: 40px; height: 20px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #dc3545; -webkit-transition: .4s; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; -webkit-transition: .4s; transition: .4s; }
input:checked + .slider { background-color: #28a745; }
input:focus + .slider { box-shadow: 0 0 1px #28a745; }
input:checked + .slider:before { -webkit-transform: translateX(20px); -ms-transform: translateX(20px); transform: translateX(20px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }
</style>
<script type="text/javascript">
$(function () {
    var table = $('#documentTable').DataTable({
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
                if(json.recordsTotal !== undefined) {
                    $('.total_count').text(json.recordsTotal);
                }
                setTimeout(function(){ $('[data-toggle="tooltip"]').tooltip(); }, 100);
                
                return (json.data || []).map(function(row) {
                    // row[0] is checkbox
                    // row[1] is actions
                    // row[2] is title
                    // row[3] is type
                    // row[4] is enable (boolean badge)

                    var idMatch = row[0].match(/data-id="([^"]+)"/);
                    var id = idMatch ? idMatch[1] : '';

                    var typeStr = String(row[3]);
                    if (typeStr.toLowerCase() === 'driver') typeStr = 'Individual Driver';
                    else if (typeStr.toLowerCase() === 'vendor') typeStr = 'Vendor';
                    else if (typeStr.toLowerCase() === 'owner') typeStr = 'Owner';

                    var isEnabled = row[4].indexOf('badge-success') !== -1;
                    var toggleHtml = '<label class="switch"><input type="checkbox" class="doc-enable-toggle" data-id="'+id+'" '+(isEnabled ? 'checked' : '')+'><span class="slider round"></span></label>';

                    var routeEdit = '{{ route("documents.edit", ":id") }}'.replace(':id', id);
                    var actionsHtml = '<span class="action-btn d-flex align-items-center">' +
                        '<a href="'+routeEdit+'" class="btn btn-sm btn-outline-info rounded-circle ml-2" style="width: 32px; height: 32px; display: inline-flex; justify-content: center; align-items: center;"><i class="mdi mdi-lead-pencil"></i></a>' +
                        '<a href="javascript:void(0)" class="delete-row btn btn-sm btn-outline-danger rounded-circle ml-2" data-id="'+id+'" style="width: 32px; height: 32px; display: inline-flex; justify-content: center; align-items: center;"><i class="mdi mdi-delete"></i></a>' +
                    '</span>';

                    return [ row[0], row[2], typeStr, toggleHtml, actionsHtml ];
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

    $('#select-all').on('click', function () {
        $('.row-select').prop('checked', $(this).is(':checked'));
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
                // success msg if needed
            },
            error: function() {
                alert('Failed to update status');
                table.ajax.reload();
            }
        });
    });
});
</script>
@endsection
