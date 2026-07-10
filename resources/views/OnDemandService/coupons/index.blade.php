@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            @if($id != '')
            <h3 class="text-themecolor">{{trans('lang.provider_Detail')}} - <span id="providerName"></span></h3>
            @else
            <h3 class="text-themecolor">{{trans('lang.ondemand_plural')}} - {{trans('lang.coupon_plural')}}</h3>
            @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.coupon_plural')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section">
        <div class="row">
            <div class="col-12">
                    @if($id!='')
                        <div class="resttab-sec">
                            <div class="menu-tab tabDiv">
                                <ul>
                                    <li ><a href="{{route('providers.view', $id)}}"><img src="{{ asset('images/provider.png') }}"> {{trans('lang.tab_basic')}}</a></li>
                                    <li><a href="{{route('ondemand.services.index', $id)}}"><img src="{{ asset('images/service.png') }}"> {{trans('lang.services')}}</a></li>
                                    <li><a href="{{route('ondemand.workers.index', $id)}}"><img src="{{ asset('images/worker.png') }}"> {{trans('lang.workers')}}</a></li>
                                    <li><a href="{{route('ondemand.bookings.index',$id)}}"><img src="{{ asset('images/booking.png') }}"> {{trans('lang.booking_plural')}}</a></li>
                                    <li class="active"><a href="{{route('ondemand.coupons', $id)}}"><img src="{{ asset('images/coupon.png') }}"> {{trans('lang.coupon_plural')}}</a></li>
                                    <li><a href="{{route('providerPayouts.payout', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payouts')}}</a></li>
                                    <li><a href="{{route('payoutRequests.providers', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payout_request')}}</a></li>
                                    <li>
                                        <a href="{{route('users.walletstransaction',$id)}}" class="wallet_transaction"><img src="{{ asset('images/wallet.png') }}"> {{trans('lang.wallet_transaction')}}</a>
                                    </li>
                                    <?php
                                        $subscription =  route("subscription.subscriptionPlanHistory", ":id");
                                        $subscription =  str_replace(":id", "providerID=" . $id, $subscription);
                                    ?>
                                    <li>
                                        <a href="{{ $subscription }}"><img src="{{ asset('images/subscription.png') }}">  {{trans('lang.subscription_history')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.coupon_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.coupon_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.coupon_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                    @if($id=='')
                        <a class="btn-primary btn rounded-full" href="{!! route('ondemand.coupons.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                    @else
                    <a class="btn-primary btn rounded-full" href="{!! route('ondemand.coupons.create','id='.$id) !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                    @endif
                     </div>
                   </div>
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="couponTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                    <th>{{trans('lang.coupon_code')}}</th>
                                    <th>{{trans('lang.coupon_discount')}}</th>
                                    @unless($id != '')
                                    <th>{{trans('lang.provider')}}</th>
                                    @endunless
                                    <th>{{trans('lang.coupon_privacy')}}</th>
                                    <th>{{trans('lang.coupon_expires_at')}}</th>
                                    <th>{{trans('lang.coupon_enabled')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($couponRows as $row)
                                    <tr>
                                        <td>{!! $row['checkbox'] !!}</td>
                                        <td>{!! $row['code'] !!}</td>
                                        <td>{!! $row['discount'] !!}</td>
                                        @if($id == '')
                                            <td>{!! $row['provider'] ?? '—' !!}</td>
                                        @endif
                                        <td>{!! $row['privacy'] !!}</td>
                                        <td>{!! $row['expires_at'] !!}</td>
                                        <td>{!! $row['enabled'] !!}</td>
                                        <td>{!! $row['actions'] !!}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $id == '' ? 8 : 7 }}">{{ trans('lang.no_record_found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
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
$(document).ready(function () {
    var providerId = @json($id);
    var couponsDataUrl = '/ondemand-coupons-data';

    if (providerId !== '') {
        var wallet_route = "{{ route('users.walletstransaction','id') }}";
        $(".wallet_transaction").attr("href", wallet_route.replace('id', 'providerID=' + providerId));
        $.get('{{ route("ondemand.providers.list") }}', function (res) {
            if (res.data) {
                var provider = res.data.find(function (p) { return p.id === providerId; });
                if (provider) {
                    $('#providerName').text(provider.name);
                }
            }
        });
    }

    if ($.fn.DataTable.isDataTable('#couponTable')) {
        $('#couponTable').DataTable().clear().destroy();
    }

    var table = $('#couponTable').DataTable({
        pageLength: 10,
        processing: true,
        serverSide: false,
        responsive: true,
        autoWidth: false,
        order: [],
        columnDefs: [{ orderable: false, targets: '_all' }],
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: ""
        },
        initComplete: function () {
            $('.total_count').text(@json($couponsCount));
        }
    });

    window.reloadCouponsTable = function () {
        $.getJSON(couponsDataUrl, {
            draw: 1,
            start: 0,
            length: 1000,
            provider_id: providerId || ''
        }).done(function (json) {
            var rows = (json && Array.isArray(json.data)) ? json.data : [];
            table.clear();
            rows.forEach(function (row) {
                var cells = [
                    row.checkbox || '',
                    row.code || '',
                    row.discount || ''
                ];
                if (providerId === '') {
                    cells.push(row.provider || '—');
                }
                cells.push(row.privacy || '', row.expires_at || '', row.enabled || '', row.actions || '');
                table.row.add(cells);
            });
            table.draw();
            $('.total_count').text((json && json.recordsFiltered) ? json.recordsFiltered : rows.length);
        }).fail(function (xhr) {
            console.error('Coupons reload failed', xhr && xhr.status, xhr && xhr.responseText);
        });
    };

    $('#is_active').on('change', function () {
        $('#couponTable .is_open').prop('checked', $(this).prop('checked'));
    });

    $('#deleteAll').on('click', function () {
        var ids = [];
        $('#couponTable .is_open:checked').each(function () {
            ids.push($(this).attr('dataId'));
        });
        if (!ids.length) {
            alert('{{ trans("lang.select_delete_alert") }}');
            return;
        }
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;
        $.post('{{ route("ondemand.coupons.destroy") }}', {
            _token: '{{ csrf_token() }}',
            ids: ids
        }).done(function () {
            window.reloadCouponsTable();
        });
    });

    $(document).on('click', '.btn-delete-provider-coupon', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;
        $.post('{{ route("ondemand.coupons.destroy") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }).done(function () {
            window.reloadCouponsTable();
        });
    });

    $(document).on('change', '.toggle-provider-coupon', function () {
        var id = $(this).data('id');
        var chk = $(this);
        $.post('{{ url("ondemand-coupons/toggle") }}/' + id, {
            _token: '{{ csrf_token() }}'
        }).done(function (res) {
            chk.prop('checked', res.enabled);
        }).fail(function () {
            chk.prop('checked', !chk.prop('checked'));
        });
    });
});
</script>
@endsection
