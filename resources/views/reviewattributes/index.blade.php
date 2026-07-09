@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.reviewattribute_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.reviewattribute_table') }}</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/attribute.png') }}" alt=""></span>
                            <h3 class="mb-0">{{ trans('lang.reviewattribute_plural') }}</h3>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.reviewattribute_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.attribute_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{{ route('reviewattributes.create') }}">
                                        <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.reviewattribute_create') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="reviewAttributesTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.reviewattribute_name') }}</th>
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

    $(document).ready(function () {
        var table = $('#reviewAttributesTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('reviewattributes.datatable') }}',
                type: 'GET',
                dataSrc: function (json) {
                    $('.total_count').text(json.recordsFiltered || 0);
                    return json.data;
                },
                error: function () {
                    jQuery('#data-table_processing').hide();
                }
            },
            order: [[0, 'asc']],
            columnDefs: [{
                orderable: false,
                targets: [1]
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

        $(document).on('click', '.reviewattribute-delete', function () {
            if (!checkDeletePermission) {
                return;
            }

            var id = $(this).data('id');
            if (!confirm('{{ trans('lang.delete_alert') }}')) {
                return;
            }

            jQuery('#data-table_processing').show();
            $.ajax({
                url: '{{ url('reviewattributes') }}/' + id,
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
    });
</script>
@endsection
