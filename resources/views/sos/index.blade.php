@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.sos') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.sos') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/SOS.png') }}" alt="SOS"></span>
                            <h3 class="mb-0">{{ trans('lang.sos') }}</h3>
                            <span class="counter ml-3 total_count"></span>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.sos') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.sos_table_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="sosTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.order_id') }}</th>
                                            <th>{{ trans('lang.sos_id') }}</th>
                                            <th>{{ trans('lang.order_user_id') }}</th>
                                            <th class="driverClass">{{ trans('lang.driver_plural') }}</th>
                                            <th>{{ trans('lang.address') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
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
    var section_id = getCookie('section_id') || '';

    $(document).ready(function () {
        $('body').tooltip({ selector: '[data-toggle="tooltip"]' });

        var table = $('#sosTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('sos.datatable') }}',
                data: function (d) {
                    d.sectionId = section_id;
                },
                dataSrc: function (json) {
                    $('.total_count').text(json.recordsFiltered || 0);
                    return json.data;
                }
            },
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: "Processing..."
            }
        });

        $(document).on('click', '.delete-sos', function () {
            if (!confirm("{{ trans('lang.delete_alert') }}")) {
                return;
            }

            var id = $(this).data('id');
            jQuery("#data-table_processing").show();

            $.ajax({
                url: '{{ url('sos') }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function () {
                    table.ajax.reload();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Delete failed');
                },
                complete: function () {
                    jQuery("#data-table_processing").hide();
                }
            });
        });
    });
</script>
@endsection
