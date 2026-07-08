@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
         <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/wallet.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.wallet_transaction_plural')}} <span class="userTitle"></span></h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <?php if ($id != '') { ?>
                        <div class="menu-tab" id="user_view">
                            <ul>
                                <li><a href="{{ route('users.view', $id) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li><a href="{{ route('orders', 'userId=' . $id) }}"><i class="ri-shopping-bag-line"></i>{{ trans('lang.tab_orders') }}</a></li>
                                <li class="active">
                                    <a href="#"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="menu-tab d-none" id="store_view">
                            <ul>
                                <li>
                                    <a class="vendor_basic"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_item"><i class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_order"><i class="ri-shopping-bag-line"></i>{{ trans('lang.tab_orders') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_review"><i class="ri-shield-star-fill"></i>{{ trans('lang.tab_reviews') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_promo"><i class="ri-discount-percent-fill"></i>{{ trans('lang.tab_promos') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_payout"><i class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                                </li>
                                <li>
                                    <a class="vendor_payout_request"><i class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                                </li>
                                <li class="dine_in_future" style="display:none;">
                                    <a href="{{route('vendors.booktable',$id)}}" class="vendor_booktable"><i class="ri-restaurant-line"></i>{{ trans('lang.dine_in_booking_history') }}</a>
                                </li>
                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions')))) { ?>
                                <li class="active">
                                    <a href="#" class="wallet_transaction"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="#" class="subscription"><i class="ri-chat-history-fill"></i>{{ trans('lang.subscription_history') }}</a>
                                </li>
                                <li>
                                    <a class="advertisement_tab"><i class="mdi mdi-newspaper"></i>{{ trans('lang.advertisement_plural') }}</a>
                                </li>
                                @php
                                    $sectionType = request()->cookie('service_type') ?? '';
                                    
                                @endphp
                                <?php if($sectionType = 'ecommerce-service'){ ?>
                               
                                <?php }else{ ?>
                                <li class="active">
                                    <a href="{{ route('restaurants.deliveryman', $id) }}"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                </li>
                                    <?php }?>
                            </ul>
                        </div>
                        <div class="menu-tab d-none" id="driver_view">
                            <ul>
                                <li>
                                    <a href="#" class="basic"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li class="vehicle_tab" style="display:none">
                                    <a href="#" class="vehicle"><i class="ri-car-line"></i>{{ trans('lang.vehicle') }}</a>
                                </li>
                                <li class="service_type_orders">
                                </li>
                                <li>
                                    <a href="#" class="payout"><i class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                                </li>
                                <li>
                                    <a href="#" class="driver_payout_request"><i class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                                </li>
                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions')))) { ?>
                                <li class="active">
                                    <a href="#" class="wallet_transaction"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="menu-tab d-none" id="provider_view">
                            <ul>
                                <li><a href="#" class="provider_basic"><img src="{{ asset('images/provider.png') }}"><i class="ri-list-indefinite"></i> {{ trans('lang.tab_basic') }}</a>
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
                                <li class="active">
                                    <a href="#" class="wallet_transaction"><img src="{{ asset('images/wallet.png') }}"> {{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <li>
                                    <a href="#" class="subscription"><img src="{{ asset('images/subscription.png') }}"> {{ trans('lang.subscription_history') }}</a>
                                </li>
                            </ul>
                        </div>
                        <?php } ?>
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0 top-title-section">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.wallet_transaction_plural')}}</h3>
                                    <p class="mb-0 text-dark-2">{{trans('lang.wallet_transactions_table_text')}}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="walletTransactionTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php if ($id == '') { ?>
                                                <th>{{ trans('lang.users') }}</th>
                                                <th>{{ trans('lang.role') }}</th>
                                                <?php } ?>
                                                <th>{{ trans('lang.amount') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.note') }}</th>
                                                <th>{{ trans('lang.payment_method') }}</th>
                                                <th>{{ trans('lang.payment_status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
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
        var id = '{{ $id }}';
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var vendorId = '';
        var search = jQuery("#search").val();
        $(document.body).on('keyup', '#search', function() {
            search = jQuery(this).val();
        });
        var serviceType = getCookie('service_type');   

        var storeID = (window.location.href.indexOf("storeID=") > -1) ? window.location.href.split("storeID=")[1] : "";
        var driverID = (window.location.href.indexOf("driverID=") > -1) ? window.location.href.split("driverID=")[1] : "";
        var providerID = (window.location.href.indexOf("providerID=") > -1) ? window.location.href.split("providerID=")[1] : "";
        var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
        var subscription_route = "{{ route('subscription.subscriptionPlanHistory', 'id') }}";
        var append_list = '';
        $(document).ready(async function() {
            if (storeID != '') {
                id = storeID;
                vendorId = '{{ isset($user) ? $user->vendorID : '' }}';
                $('#user_view').addClass('d-none');
                $('#store_view').removeClass('d-none');
                var basic = "{{ route('stores.view', $id) }}";
                var items = "{{ route('vendors.items', $id) }}";
                var vendor_orders = "{{ route('vendors.orders', $id) }}";
                var vendor_review = "{{ route('vendors.reviews', $id) }}";
                var ven_promo = "{{ route('vendors.coupons', $id) }}";
                var ven_payout = "{{ route('vendors.payout', $id) }}";
                var ven_payoutReq = "{{ route('payoutRequests.vendor.view', $id) }}";
                // var ven_dinein = "{{ route('vendors.booktable', $id) }}";
                var ven_payoutReq = "{{ route('payoutRequests.vendor.view', $id) }}";
                // var ven_dinein = "{{ route('vendors.booktable', $id) }}";
                $(".vendor_basic").attr("href", basic);
                $(".vendor_item").attr("href", items);
                $(".vendor_order").attr("href", vendor_orders);
                $(".vendor_review").attr("href", vendor_review);
                $(".vendor_promo").attr("href", ven_promo);
                $(".vendor_payout").attr("href", ven_payout);
                $(".vendor_payout_request").attr("href", ven_payoutReq);
                // $(".vendor_booktable").attr("href", ven_dinein);
                // $(".vendor_booktable").attr("href", ven_dinein);
                // $(".vendor_booktable").attr("href", ven_dinein);
                $(".subscription").attr("href", subscription_route.replace('id', "storeID=" + vendorId));
                await getStoreNameFunction(storeID);
            } else if (driverID != '') {
                id = driverID;
                $('#user_view').addClass('d-none');
                $('#driver_view').removeClass('d-none');
                var basic = "{{ route('drivers.view', 'id') }}";
                var vehicle = "{{ route('drivers.vehicle', 'id') }}";
                var payouts = "{{ route('driver.payouts', 'id') }}";
                var driver_payout_request = "{{ route('payoutRequests.drivers.view', 'id') }}";
                $(".basic").attr("href", basic.replace('id', driverID));
                $(".vehicle").attr("href", vehicle.replace('id', driverID));
                $(".payout").attr("href", payouts.replace('id', driverID));
                $(".driver_payout_request").attr("href", driver_payout_request.replace('id', driverID));
                $(".subscription").attr("href", subscription_route.replace('id', "{{ $id }}"));
                if(serviceType !== 'delivery-service' && serviceType !== 'parcel_delivery'){
                    $('.vehicle_tab').show();
                }else{
                    $('.vehicle_tab').hide();
                }
            } else if (providerID != '') {
                id = providerID;
                $('#user_view').addClass('d-none');
                $('#provider_view').removeClass('d-none');
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
                $(".subscription").attr("href", subscription_route.replace('id', "{{ $id }}"));
            }
            if (id) {
                $(".wallet_transaction").attr("href", wallet_route.replace('id', "{{ $id }}"));
            }
            if (id) {
                var driver = {
                    firstName: "{!! isset($user) ? addslashes($user->firstName) : '' !!}",
                    lastName: "{!! isset($user) ? addslashes($user->lastName) : '' !!}",
                    role: "{!! isset($user) ? addslashes($user->role) : '' !!}",
                    vendorID: "{!! isset($user) ? addslashes($user->vendorID) : '' !!}",
                    id: "{!! isset($user) ? addslashes($user->id) : '' !!}",
                    serviceType: "{!! isset($user) ? addslashes($user->serviceType) : '' !!}"
                };
                if (driver.firstName) {
                    $(".userTitle").text(' - ' + driver.firstName + " " + driver.lastName);
                    if (driver.role == "vendor") {
                        var vendor_basic = "{{ route('stores.view', 'id') }}";
                        var vendor_item = "{{ route('vendors.items', 'id') }}";
                        var vendor_order = "{{ route('vendors.orders', 'id') }}";
                        var vendor_review = "{{ route('vendors.reviews', 'id') }}";
                        var vendor_promo = "{{ route('vendors.coupons', 'id') }}";
                        var vendor_payout = "{{ route('vendors.payout', 'id') }}";
                        var vendor_payout_request = "{{ route('payoutRequests.vendor.view', 'id') }}";
                        // var vendor_booktable = "{{ route('vendors.booktable', 'id') }}";
                        var advRoute = "{{ route('restaurants.advertisements', 'id') }}";
                        var deliveryRoute = "{{ route('restaurants.deliveryman', 'id') }}";
                        $(".vendor_basic").attr("href", vendor_basic.replace('id', driver.vendorID));
                        $(".vendor_item").attr("href", vendor_item.replace('id', driver.vendorID));
                        $(".vendor_order").attr("href", vendor_order.replace('id', driver.vendorID));
                        $(".vendor_review").attr("href", vendor_review.replace('id', driver.vendorID));
                        $(".vendor_promo").attr("href", vendor_promo.replace('id', driver.vendorID));
                        $(".vendor_payout").attr("href", vendor_payout.replace('id', driver.vendorID));
                        $(".vendor_payout_request").attr("href", vendor_payout_request.replace('id', driver.vendorID));
                        // $(".vendor_booktable").attr("href", vendor_booktable.replace('id', driver.vendorID));
                        $(".advertisement_tab").attr("href", advRoute.replace('id', driver.vendorID));
                        $(".deliveryman_tab").attr("href", deliveryRoute.replace('id', driver.vendorID));
                    }
                    if (driver.serviceType == "cab-service") {
                        var url = "{{ route('drivers.rides', 'driverId') }}";
                        url = url.replace('driverId', driver.id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "rental-service") {
                        var url = "{{ route('rental_orders.driver', 'id') }}";
                        url = url.replace("id", driver.id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "delivery-service" || driver.serviceType == "ecommerce-service") {
                        var url = "{{ route('orders', 'id') }}";
                        url = url.replace("id", 'driverId=' + driver.id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "parcel_delivery") {
                        var url = "{{ route('parcel_orders.driver', 'id') }}";
                        url = url.replace("id", driver.id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    }
                }
            }
            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            $(document).on('click', '.dt-button-collection .dt-button', function() {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            });
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                    $('.dt-button-collection').hide();
                    $('.dt-button-background').hide();
                }
            });
            var fieldConfig = {
                columns: [
                    <?php if ($id == '') { ?> {
                        key: 'Name',
                        header: "{{ trans('lang.user') }}"
                    },
                    {
                        key: 'role',
                        header: "{{ trans('lang.role') }}"
                    },
                    <?php } ?>
                    {
                        key: 'amount',
                        header: "{{ trans('lang.amount') }}"
                    },
                    {
                        key: 'payment_method',
                        header: "{{ trans('lang.payment_method') }}"
                    },
                    {
                        key: 'payment_status',
                        header: "{{ trans('lang.payment_status') }}"
                    },
                    {
                        key: 'date',
                        header: "{{ trans('lang.date') }}"
                    },
                ],
                fileName: "{{ trans('lang.wallet_transaction_plural') }}",
            };
            const table = $('#walletTransactionTable').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('wallet.transactions.datatable') }}",
                    data: function(d) {
                        d.user_id = '{{ $parsedId }}';
                        d.search.value = $('.dataTables_filter input').val() || '';
                    }
                },
                drawCallback: function(settings) {
                    $('#data-table_processing').hide();
                },
                <?php if($id == '') { ?>
                order: [4, 'desc'],
                columnDefs: [{
                        targets: 4,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: [0, 5,6, 7, 8]
                    },
                ],
                <?php } else { ?>
                order: [2, "desc"],
                columnDefs: [{
                        targets: 2,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: [0, 3, 4, 5,6]
                    },
                ],
                <?php } ?>
                "language": {
                    "zeroRecords": "{{ trans('lang.no_record_found') }}",
                    "emptyTable": "{{ trans('lang.no_record_found') }}",
                    "processing": "" // Remove default loader
                },
                dom: 'lfrtipB',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                    className: 'btn btn-info',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: "{{ trans('lang.export_excel') }}",
                            action: function(e, dt, button, config) {
                                exportData(dt, 'excel', fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: "{{ trans('lang.export_pdf') }}",
                            action: function(e, dt, button, config) {
                                exportData(dt, 'pdf', fieldConfig);
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: "{{ trans('lang.export_csv') }}",
                            action: function(e, dt, button, config) {
                                exportData(dt, 'csv', fieldConfig);
                            }
                        }
                    ]
                }],
                initComplete: function() {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3;
                    }).remove();
                }
            });
            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
        });
        $("#is_active").click(function() {
            $("#walletTransactionTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#walletTransactionTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    var ids = [];
                    $('#walletTransactionTable .is_open:checked').each(function() {
                        ids.push($(this).attr('dataId'));
                    });
                    
                    $.ajax({
                        url: "{{ route('wallet.transactions.bulk-destroy') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: ids
                        },
                        success: function(response) {
                            jQuery("#data-table_processing").hide();
                            $('#walletTransactionTable').DataTable().ajax.reload();
                        },
                        error: function(err) {
                            jQuery("#data-table_processing").hide();
                            alert("Error bulk deleting transactions");
                        }
                    });
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });
        $(document).on("click", "a[name='transaction-delete']", function(e) {
            var id = this.id;
            if (confirm("{{ trans('lang.delete_alert') }}")) {
                jQuery("#data-table_processing").show();
                $.ajax({
                    url: "{{ route('wallet.transactions.destroy') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        jQuery("#data-table_processing").hide();
                        $('#walletTransactionTable').DataTable().ajax.reload();
                    },
                    error: function(err) {
                        jQuery("#data-table_processing").hide();
                        alert("Error deleting transaction");
                    }
                });
            }
        });

        async function getStoreNameFunction(vendorId) {
            var vendorName = '{!! isset($vendor) ? addslashes($vendor->title) : '' !!}';
            if (vendorName) {
                $('.userTitle').text(" - " + vendorName);
                @if(isset($vendor) && $vendor->dine_in_active)
                    $(".dine_in_future").show();
                @endif
                
                var wallet_route = "{{route('users.walletstransaction','id')}}";
                $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID={{ isset($user) ? $user->id : "" }}'));
                
                @if(isset($vendor) && $vendor->section && $vendor->section->dine_in_active)
                    $(".dine_in_future").show();
                @endif
            }
            return vendorName;
        }
    </script>
@endsection
