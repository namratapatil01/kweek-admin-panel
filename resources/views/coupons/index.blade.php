@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <!-- <h3 class="text-themecolor">{{ trans('lang.coupon_plural') }}</h3> -->
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.coupon_plural') }}</li>
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

        {{-- Top title bar --}}
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}" onerror="this.src='{{ asset('images/order.png') }}'"></span>
                            <h3 class="mb-0">{{ trans('lang.coupon_plural') }}</h3>
                            <span class="counter ml-3 total_coupon_count badge badge-secondary" style="font-size:14px;padding:6px 12px;border-radius:50px;"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <a href="{{ route('coupons.create') }}" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-plus mr-1"></i> Create Coupon
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-1 h4">{{ trans('lang.coupon_plural') }}</h3>
                                <p class="mb-0 text-dark-2">View and manage all the coupons</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="couponTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="delete-all">
                                                <input type="checkbox" id="select_all_coupons">
                                                <label class="control-label" for="select_all_coupons">
                                                    <a id="deleteAllCoupons" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{ trans('lang.all') }}
                                                    </a>
                                                </label>
                                            </th>
                                            <th>Code</th>
                                            <th>Discount</th>
                                            <th>Store</th>
                                            <th>Privacy</th>
                                            <th>Expires At</th>
                                            <th>Enabled</th>
                                            <th>Actions</th>
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
<script>
$(document).ready(function () {
    var vendorId = '{{ $vendorId ?? "" }}';

    var table = $('#couponTable').DataTable({
        processing : true,
        serverSide : true,
        responsive : true,
        ajax: {
            url : '{{ route("coupons.datatable") }}',
            type: 'GET',
            data: function (d) {
                d._token    = '{{ csrf_token() }}';
                d.vendor_id = vendorId;
            },
            dataSrc: function (json) {
                $('.total_coupon_count').text(json.recordsTotal);
                return json.data || [];
            }
        },
        columnDefs: [{ orderable: false, targets: [0, 6, 7] }],
        language: {
            zeroRecords : '{{ trans("lang.no_record_found") }}',
            emptyTable  : '{{ trans("lang.no_record_found") }}',
            processing  : ''
        },
        dom: 'lfrtipB',
        buttons: [
            {
                extend   : 'collection',
                text     : '<i class="mdi mdi-cloud-download"></i> {{ trans("lang.export_as") }}',
                className: 'btn btn-info',
                buttons  : [
                    { extend: 'excelHtml5', text: '{{ trans("lang.export_excel") }}' },
                    { extend: 'pdfHtml5',   text: '{{ trans("lang.export_pdf") }}'   },
                    { extend: 'csvHtml5',   text: '{{ trans("lang.export_csv") }}'   }
                ]
            }
        ],
        initComplete: function () {
            $('.dataTables_filter').append($('.dt-buttons').detach());
            $('.dataTables_filter input')
                .attr('placeholder', 'Search coupons...')
                .val('');
            $('.dataTables_filter label').contents().filter(function () {
                return this.nodeType === 3;
            }).remove();
        }
    });

    // Select all checkbox
    $('#select_all_coupons').on('change', function () {
        $('#couponTable .coupon-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Bulk delete
    $('#deleteAllCoupons').on('click', function () {
        var ids = [];
        $('#couponTable .coupon-checkbox:checked').each(function () {
            ids.push($(this).data('id'));
        });
        if (!ids.length) {
            alert('{{ trans("lang.select_delete_alert") }}');
            return;
        }
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;

        $.ajax({
            url   : '{{ route("coupons.bulk-destroy") }}',
            method: 'POST',
            data  : { _token: '{{ csrf_token() }}', ids: ids },
            success: function () { table.ajax.reload(); },
            error  : function (xhr) { alert('Error: ' + xhr.responseText); }
        });
    });

    // Single row delete
    $(document).on('click', '.btn-delete-coupon', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;
        $.ajax({
            url   : '{{ url("coupons") }}/' + id,
            method: 'DELETE',
            data  : { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error  : function (xhr) { alert('Error: ' + xhr.responseText); }
        });
    });

    // Toggle enabled/disabled
    $(document).on('change', '.toggle-enabled', function () {
        var id  = $(this).data('id');
        var chk = $(this);
        $.ajax({
            url   : '{{ url("coupons/toggle") }}/' + id,
            method: 'POST',
            data  : { _token: '{{ csrf_token() }}' },
            success: function (res) {
                // Server returns current state; reflect it
                chk.prop('checked', res.enabled);
            },
            error: function () {
                // Revert on error
                chk.prop('checked', !chk.prop('checked'));
                alert('Failed to update status.');
            }
        });
    });
});
</script>

<style>
/* Toggle Switch */
.coupon-toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    margin: 0;
}
.coupon-toggle-switch input { opacity: 0; width: 0; height: 0; }
.coupon-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    border-radius: 26px;
    transition: .3s;
}
.coupon-slider:before {
    position: absolute;
    content: "";
    height: 20px; width: 20px;
    left: 3px; bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: .3s;
}
.coupon-toggle-switch input:checked + .coupon-slider { background-color: #28a745; }
.coupon-toggle-switch input:checked + .coupon-slider:before { transform: translateX(22px); }
</style>
@endsection
