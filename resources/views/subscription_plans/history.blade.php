@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.subscription_history')}} <span class="page-title"></span></h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.subscription_history_table') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="container-fluid">
           
            <div class="table-list">
                <div class="row">
                    <div class="col-12">

                        <?php if ($id != '') {
                        ?>
                        <div class="menu-tab d-none" id="vendorhistorytab">
                            <ul>
                                <li>
                                    <a class="vendor_basic" href="{{ route('stores.view', $id) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_item" href="{{ route('vendors.items', $id) }}"><i class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_order" href="{{ route('vendors.orders', $id) }}"><i class="ri-shopping-bag-line"></i>{{ trans('lang.tab_orders') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_review" href="{{ route('vendors.reviews', $id) }}"><i class="ri-shield-star-fill"></i>{{ trans('lang.tab_reviews') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_promo" href="{{ route('vendors.coupons', $id) }}"><i class="ri-discount-percent-fill"></i>{{ trans('lang.tab_promos') }}</a>
                                <li>
                                    <a class="vendor_payout" href="{{ route('vendors.payout', $id) }}"><i class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_payout_request" href="{{ route('payoutRequests.vendor.view', $id) }}"><i class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                                </li>
                               <li class="dine_in_future" style="display:none;">

                            <a href="{{route('vendors.booktable',$id)}}"><i class="ri-restaurant-line"></i>{{trans('lang.dine_in_booking_history')}}</a>

                        </li>
                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions')))) { ?>
                                <li>
                                    <a class="wallet_transaction"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <?php }?>
                                <li class="active">
                                    <a href="{{ route('subscription.subscriptionPlanHistory', $id) }}"><i class="ri-chat-history-fill"></i>{{ trans('lang.subscription_history') }}</a>
                                </li>
                                <li>
                                    <a  class="advertisement" href="{{ route('restaurants.advertisements', $id) }}"><i class="mdi mdi-newspaper"></i>{{ trans('lang.advertisement_plural') }}</a>
                                </li>
                                 @php
                                    $sectionType = $_COOKIE['service_type'] ?? ''; 
                                    
                                @endphp
                                <?php if($sectionType == 'ecommerce-service'){ ?>
                               
                                <?php }else{ ?>
                                <li class="">
                                    <a href="{{ route('restaurants.deliveryman', $id) }}"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                </li>
                                    <?php }?>
                            </ul>
                        </div>

                        <div class="menu-tab d-none" id="providerhistorytab">

                            <ul>

                                <li><a href="#" class="provider_basic"><img src="{{ asset('images/provider.png') }}"> {{ trans('lang.tab_basic') }}</a>

                                </li>

                                <li><a href="#" class="provider_services"><img src="{{ asset('images/service.png') }}"> {{ trans('lang.services') }}</a></li>

                                <li>

                                <li><a href="#" class="provider_workers"><img src="{{ asset('images/worker.png') }}"> {{ trans('lang.workers') }}</a></li>

                                <li>

                                <li><a href="#" class="provider_bookings"><img src="{{ asset('images/booking.png') }}"> {{ trans('lang.booking_plural') }}</a></li>

                                <li>

                                <li><a href="#" class="provider_coupons"><img src="{{ asset('images/coupon.png') }}"> {{ trans('lang.coupon_plural') }}</a></li>

                                <li>

                                    <a href="#" class="provider_payout"><img src="{{ asset('images/payment.png') }}"> {{ trans('lang.tab_payouts') }}</a>

                                </li>

                                <li>

                                    <a href="#" class="provider_payout_request"><img src="{{ asset('images/payment.png') }}"> {{ trans('lang.tab_payout_request') }}</a>

                                </li>

                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions')))) { ?>
                                <li>
                                    <a class="wallet_transaction"><img src="{{ asset('images/wallet.png') }}"> {{ trans('lang.wallet_transaction') }}</a>
                                </li>

                                <?php }?>

                                <li class="active">
                                    <a class="subscription" href="#"><img src="{{ asset('images/subscription.png') }}"> {{ trans('lang.subscription_history') }}</a>
                                </li>

                            </ul>

                        </div>

                        <?php } ?>
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0 top-title-section">
                               <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.subscription_history')}}</h3>
                                    <p class="mb-0 text-dark-2">{{trans('lang.subscription_history_table')}}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="subscriptionHistoryTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label>
                                                </th>
                                                <?php if ($id == '') { ?>
                                                <th>{{ trans('lang.vendor_name') }}</th>
                                                <?php } ?>
                                                <th>{{ trans('lang.plan_name') }}</th>
                                                <th>{{ trans('lang.plan_type') }}</th>
                                                <th>{{ trans('lang.plan_expires_at') }}</th>
                                                <th>{{ trans('lang.purchase_date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
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

    <script>
        
        var section_id = getCookie('section_id') || '';
        var userId = "{{ $id }}";
        var storeID = "{{ $storeID }}";
        var providerID = "{{ $providerID }}";
        var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
        var subscription_route = "{{ route('subscription.subscriptionPlanHistory', 'id') }}";

        $(document).ready(function() {
            if (storeID != '') {
                @if($dineInActive)
                    $(".dine_in_future").show();
                @endif
                @if($vendorTitle)
                    $(".page-title").text(' - ' + "{{ $vendorTitle }}");
                @endif
                
                if (userId != '') {
                    $('#vendorhistorytab').removeClass('d-none');
                    var basic = "{{ route('stores.view', 'id') }}";
                    var items = "{{ route('vendors.items', 'id') }}";
                    var vendor_orders = "{{ route('vendors.orders', 'id') }}";
                    var vendor_review = "{{ route('vendors.reviews', 'id') }}";
                    var ven_promo = "{{ route('vendors.coupons', 'id') }}";
                    var ven_payout = "{{ route('vendors.payout', 'id') }}";
                    var ven_payoutReq = "{{ route('payoutRequests.vendor.view', 'id') }}";
                    var ven_dinein = "{{ route('vendors.booktable', 'id') }}";
                    var advRoute="{{ route('restaurants.advertisements', 'id') }}";
                    var deliverymanRoute="{{ route('restaurants.deliveryman', 'id') }}"
                    $(".vendor_basic").attr("href", basic.replace('id', storeID));
                    $(".vendor_item").attr("href", items.replace('id', storeID));
                    $(".vendor_order").attr("href", vendor_orders.replace('id', storeID));
                    $(".vendor_review").attr("href", vendor_review.replace('id', storeID));
                    $(".vendor_promo").attr("href", ven_promo.replace('id', storeID));
                    $(".vendor_payout").attr("href", ven_payout.replace('id', storeID));
                    $(".vendor_payout_request").attr("href", ven_payoutReq.replace('id', storeID));
                    $('.deliveryman').attr("href", deliverymanRoute.replace('id', storeID));
                    $('.advertisement').attr("href", advRoute.replace('id', storeID));
                    $(".vendor_booktable").attr("href", ven_dinein.replace('id', storeID));
                    $(".wallet_transaction").attr("href", wallet_route.replace('id', "storeID=" + userId));
                }
            } else if (providerID != '') {
                userId = providerID;
                $('#providerhistorytab').removeClass('d-none');
                var provider_basic = "{{ url('providers/view/{id}') }}";
                var provider_services = "{{ url('ondemand-services/{id?}') }}";
                var provider_workers = "{{ url('ondemand-workers/{id?}') }}";
                var provider_bookings = "{{ url('ondemand-bookings/{id?}') }}";
                var provider_coupons = "{{ url('ondemand-coupons/{id?}') }}";
                var provider_payout = "{{ url('providerPayouts/{id}') }}";
                var provider_payout_request = "{{ url('payoutRequests/providers/{id?}') }}";
                $(".provider_basic").attr("href", provider_basic.replace('{id}', providerID));
                $(".provider_services").attr("href", provider_services.replace('{id?}', providerID));
                $(".provider_workers").attr("href", provider_workers.replace('{id?}', providerID));
                $(".provider_bookings").attr("href", provider_bookings.replace('{id?}', providerID));
                $(".provider_coupons").attr("href", provider_coupons.replace('{id?}', providerID));
                $(".provider_payout").attr("href", provider_payout.replace('{id}', providerID));
                $(".provider_payout_request").attr("href", provider_payout_request.replace('{id?}', providerID));
                $(".wallet_transaction").attr("href", wallet_route.replace('id', "{{ $id }}"));
            }

            if (userId != '') {
                $(".subscription").attr("href", subscription_route.replace('id', "{{ $id }}"));
            }

            const table = $('#subscriptionHistoryTable').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('subscription.subscriptionPlanHistory.datatable', $id) }}",
                    data: function(d) {
                        d.section_id = section_id;
                    },
                    dataSrc: function(json) {
                        $('.total_count').text(json.recordsFiltered || 0);
                        return json.data;
                    }
                },
                order: (userId == '') ? [5, 'desc'] : [4, 'desc'],
                columnDefs: [
                    {
                        targets: [0],
                        orderable: false,
                    }
                ],
                "language": {
                    "zeroRecords": "{{ trans('lang.no_record_found') }}",
                    "emptyTable": "{{ trans('lang.no_record_found') }}",
                    "processing": "Processing..."
                },
                initComplete: function () {
                    $(function () {
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                }
            });

            $("#is_active").click(function() {
                $("#subscriptionHistoryTable .is_open").prop('checked', $(this).prop('checked'));
            });

            $("#deleteAll").click(function() {
                if ($('#subscriptionHistoryTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        var ids = [];
                        $('#subscriptionHistoryTable .is_open:checked').each(function() {
                            ids.push($(this).attr('dataId'));
                        });
                        $.post("{{ route('subscription.subscriptionPlanHistory.delete') }}", {
                            _token: "{{ csrf_token() }}",
                            ids: ids
                        }).done(function() {
                            table.ajax.reload();
                        }).always(function() {
                            jQuery("#data-table_processing").hide();
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });
        });
    </script>
@endsection
