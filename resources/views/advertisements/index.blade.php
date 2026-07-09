@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.advertisement_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.advertisement_plural') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}" onerror="this.src='{{ asset('images/order.png') }}'"></span>
                            <h3 class="mb-0">{{ trans('lang.advertisement_plural') }}</h3>
                            <span class="counter ml-3 total_ad_count badge badge-warning" style="font-size:14px;padding:6px 12px;border-radius:50px;"></span>
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
                                <h3 class="text-dark-2 mb-1 h4">{{ trans('lang.advertisement_plural') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.advertisement_table_text') }}</p>
                            </div>
                            <div class="card-header-right">
                                <a href="{{ route('advertisements.create') }}" class="btn btn-primary rounded-full">
                                    <i class="mdi mdi-plus mr-1"></i> {{ trans('lang.advertisement_create') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="adTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="select_all_ads">
                                            </th>
                                            <th>{{ trans('lang.ads_title') }}</th>
                                            <th>{{ trans('lang.store_info') }}</th>
                                            <th>{{ trans('lang.ads_type') }}</th>
                                            <th>{{ trans('lang.duration') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.priority') }}</th>
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
<style>
.ad-actions .btn-ad-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #ddd;
    margin: 0 2px;
    color: #555;
}
.ad-actions .btn-ad-action:hover { background: #f5f5f5; text-decoration: none; }
</style>
<script>
$(document).ready(function () {
    var vendorId = '{{ $vendorId ?? "" }}';

    var table = $('#adTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("advertisements.datatable") }}',
            type: 'GET',
            data: function (d) {
                d.vendor_id = vendorId;
                d.section_id = (typeof getCookie === 'function' ? getCookie('section_id') : '') || '';
            },
            dataSrc: function (json) {
                $('.total_ad_count').text(json.recordsTotal || 0);
                return json.data || [];
            },
            error: function (xhr) {
                console.error('Advertisements datatable error', xhr.responseText);
                alert('Failed to load advertisements. Please refresh and try again.');
            }
        },
        columnDefs: [{ orderable: false, targets: [0, 7] }],
        language: {
            zeroRecords: '{{ trans("lang.no_record_found") }}',
            emptyTable: '{{ trans("lang.no_record_found") }}',
            processing: ''
        }
    });

    $('#select_all_ads').on('change', function () {
        $('#adTable .ad-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '.btn-delete-ad', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;
        $.post('{{ route("advertisements.destroy") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }).done(function () {
            table.ajax.reload();
        }).fail(function () {
            alert('Delete failed');
        });
    });
});
</script>
@endsection
