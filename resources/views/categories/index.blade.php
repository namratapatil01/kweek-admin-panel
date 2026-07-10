@extends('layouts.app')
@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.category_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.category_table') }}</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/category.png') }}" alt=""></span>
                            <h3 class="mb-0">{{ trans('lang.category_plural') }}</h3>
                            <span class="counter ml-3 total_count">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.category_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.category_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{{ route('categories.create') }}">
                                        <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.category_create') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="categoryTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            @if($canDelete)
                                                <th class="delete-all">
                                                    <input type="checkbox" id="is_active">
                                                    <label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                            <i class="mdi mdi-delete"></i> {{ trans('lang.all') }}
                                                        </a>
                                                    </label>
                                                </th>
                                            @endif
                                            <th>{{ trans('lang.category_info') }}</th>
                                            <th>{{ trans('lang.item') }}</th>
                                            <th>{{ trans('lang.item_publish') }}</th>
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
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script type="text/javascript">
    var checkDeletePermission = @json($canDelete);
    var defaultOrderCol = checkDeletePermission ? 1 : 0;

    $(document).ready(function () {
        var table = $('#categoryTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('categories.datatable') }}',
                type: 'GET',
                data: function (d) {
                    d.sectionId = getCookie('section_id') || '';
                },
                dataSrc: function (json) {
                    $('.total_count').text(json.recordsFiltered || 0);
                    return json.data;
                }
            },
            order: [[defaultOrderCol, 'asc']],
            columnDefs: [{
                orderable: false,
                targets: checkDeletePermission ? [0, 3, 4] : [2, 3]
            }],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: ''
            },
            drawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#is_active').on('click', function () {
            $('.row-select').prop('checked', $(this).is(':checked'));
        });

        $(document).on('change', '.publish-toggle', function () {
            var id = $(this).data('id');
            var publish = $(this).is(':checked');

            $.ajax({
                url: '{{ url('categories') }}/' + id + '/toggle-publish',
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    publish: publish ? 1 : 0
                },
                error: function () {
                    alert('{{ trans('lang.error') }}');
                    table.ajax.reload(null, false);
                }
            });
        });

        $(document).on('click', '.category-delete', function () {
            var id = $(this).data('id');
            if (!confirm('{{ trans('lang.delete_alert') }}')) {
                return;
            }

            jQuery('#data-table_processing').show();
            $.ajax({
                url: '{{ url('categories') }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function () {
                    table.ajax.reload();
                    jQuery('#data-table_processing').hide();
                },
                error: function (xhr) {
                    jQuery('#data-table_processing').hide();
                    alert(xhr.responseJSON?.error || '{{ trans('lang.error') }}');
                }
            });
        });

        $('#deleteAll').on('click', function () {
            var ids = $('.row-select:checked').map(function () {
                return $(this).data('id');
            }).get();

            if (!ids.length) {
                alert("{{ trans('lang.select_delete_alert') }}");
                return;
            }

            if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
                return;
            }

            jQuery('#data-table_processing').show();
            $.ajax({
                url: '{{ route('categories.bulk-destroy') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                },
                success: function () {
                    $('#is_active').prop('checked', false);
                    table.ajax.reload();
                    jQuery('#data-table_processing').hide();
                },
                error: function (xhr) {
                    jQuery('#data-table_processing').hide();
                    alert(xhr.responseJSON?.error || '{{ trans('lang.error') }}');
                }
            });
        });
    });
</script>
@endsection
