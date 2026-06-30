@extends('layouts.app')

@section('content')

    <div id="main-wrapper" class="page-wrapper" style="min-height: 207px;">


        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor" id="section_title"></h3>
            </div>
        </div>

        <div class="container-fluid">

            <div class="top-filter">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="sis-card-head-select-box d-flex align-items-center gap-2 mb-4">
                            <div class="head-select-box">
                                <label class="mb-0 text-dark-2">{{ trans('lang.filter_by') }}:</label>
                                <select id="viewFilter" name="view" class="form-control">
                                    <option value="">{{ trans('lang.all') }}</option>
                                    <option value="year">{{ trans('lang.view_full_year') }}</option>
                                    <option value="month">{{ trans('lang.view_by_month') }}</option>
                                    <option value="custom">{{ trans('lang.custom_date_range') }}</option>
                                </select>
                            </div>

                            <div id="monthYearFilters" class="head-select-box" style="display:inline-block;">
                                <select id="monthFilter" name="month" class="form-control" style="display: none;">
                                    <option value="1">{{ trans('lang.january') }}</option>
                                    <option value="2">{{ trans('lang.february') }}</option>
                                    <option value="3">{{ trans('lang.march') }}</option>
                                    <option value="4">{{ trans('lang.april') }}</option>
                                    <option value="5">{{ trans('lang.may') }}</option>
                                    <option value="6">{{ trans('lang.june') }}</option>
                                    <option value="7">{{ trans('lang.july') }}</option>
                                    <option value="8">{{ trans('lang.august') }}</option>
                                    <option value="9">{{ trans('lang.september') }}</option>
                                    <option value="10">{{ trans('lang.october') }}</option>
                                    <option value="11">{{ trans('lang.november') }}</option>
                                    <option value="12">{{ trans('lang.december') }}</option>

                                </select>
                                <select id="yearFilter" name="year" class="form-control" style="display:none">

                                </select>
                            </div>

                            <div id="customDateFilters" class="head-select-box" style="display: none;">
                                <input class="form-control" type="date" name="start_date" id="startDate" value="">
                                <input class="form-control" type="date" name="end_date" id="endDate" value="">
                            </div>
                            <button type="button" id="applyFilterBtn" class="btn btn-primary">{{trans('lang.apply_filter')}}</button>
                            <a href="#" class="btn btn-secondary" onclick="window.location.reload();">{{trans('lang.clear_filter')}}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border " onclick="javascript:void(0)">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_earnings.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_earnings') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="earnings_count"></h2>
                                        <h6 id="earning_percent" class="up-down-list font-semibold"></h6>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border " onclick="javascript:void(0)">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/admin_commission.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.admin_commission') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning"
                                            id="admincommission_count"></h2>
                                        <h6 id="commission_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('stores') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_store.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_stores') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="vendor_count"></h2>
                                        <h6 id="vendor_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('orders') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_orders1.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_orders') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="order_count"></h2>
                                        <h6 id="orders_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('users') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_customers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_clients') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="users_count"></h2>
                                        <h6 id="customer_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('drivers') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_drivers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_drivers') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="driver_count"></h2>
                                        <h6 id="driver_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="card border">

                        <div class="card-header no-border">
                            <h3 class="card-title">{{ trans('lang.order_status_overview') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_orders') }}</p>
                        </div>
                        <div class="card-body">
                            <canvas id="order_status" height="330"></canvas>
                            <input type="hidden" name="placed_count" id="placed_count">
                            <input type="hidden" name="confirmed_count" id="confirmed_count">
                            <input type="hidden" name="shipped_count" id="shipped_count">
                            <input type="hidden" name="completed_count" id="completed_count">
                            <input type="hidden" name="canceled_count" id="canceled_count">
                            <input type="hidden" name="failed_count" id="failed_count">
                            <input type="hidden" name="pending_count" id="pending_count">
                            <input type="hidden" name="intransit_count" id="intransit_count">
                            <input type="hidden" name="cancelled_count" id="cancelled_count">

                        </div>
                    </div>
                </div>

            </div>
            <div class="row daes-sec-sec">
                <div class="col-lg-5 col-md-12">
                    <div class="card border">
                        <div class="card-header no-border">

                            <h3 class="card-title">{{ trans('lang.total_sales') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_sales') }}</p>

                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <canvas id="sales-chart" height="250"></canvas>
                            </div>

                            <div class="d-flex flex-row justify-content-end">
                                <span class="mr-2"> <i class="fa fa-square" style="color:red"></i>
                                    {{ trans('lang.dashboard_this_year') }} </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-12">
                    <div class="card border">
                        <div class="card-header no-border">

                            <h3 class="card-title">{{ trans('lang.service_overview') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_sales_overview') }}</p>

                        </div>
                        <div class="card-body">
                            <div class="flex-row">
                                <canvas id="commissions" height="250"></canvas>

                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2"> <i class="fa fa-square" style="color:red"></i>
                                {{ trans('lang.dashboard_this_year') }} </span>
                        </div>
                    </div>
                </div>


            </div>
            <div class="row daes-sec-sec">

                <div class="col-md-6 col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{ trans('lang.top_stores') }}</h3>
                            <a href="{{ route('stores') }}">{{trans('lang.view_all')}}</a>
                        </div>
                        <div class=" card-body">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.vendor')}}</th>
                                        <th>{{trans('lang.vendor_review_review')}}</th>
                                        <th>{{trans('lang.actions')}}</th>

                                    </tr>
                                </thead>
                                <tbody id="append_list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{ trans('lang.top_drivers') }}</h3>
                            <a href="{!! route('drivers') !!}">{{trans('lang.view_all')}}</a>

                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.driver')}}</th>
                                        <th>{{trans('lang.order_completed')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_top_drivers">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            <div class="row daes-sec-sec">

                <div class="col-md-12 col-lg-12">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.recent_orders')}}</h3>
                            <a href="{{ route('orders') }}">{{trans('lang.view_all')}}</a>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                    <th>{{trans('lang.order_id')}}</th>
                                    <th>{{trans('lang.vendor')}}</th>
                                    <th>{{trans('lang.total_amount')}}</th>
                                    <th>{{trans('lang.quantity')}}</th>

                                    </tr>
                                </thead>
                                <tbody id="append_list_recent_order">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
            </div>
        </div>

    </div>

    </div>

@endsection

@section('scripts')

    <script src="{{asset('js/chart.js')}}"></script>
    <script src="{{asset('js/highcharts.js')}}"></script>

    <script>

        var active_id = "{{$id}}";
        var active_type = "{{$type}}";
        var db = kweekFirestore();
        var currency = db.collection('settings');

        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var refCurrency = database.collection('currencies').where('isActive', '==', true);
        refCurrency.get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                var currencyData = snapshots.docs[0].data();
                currentCurrency = currencyData.symbol;
                currencyAtRight = currencyData.symbolAtRight;
                if (currencyData.decimal_degits) {
                    decimal_degits = currencyData.decimal_degits;
                }
            }
        });

        var placeholderImage = '';
        var placeholder = db.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })

        function toStartOfDay(dateInput) {
            const d = new Date(dateInput);
            d.setHours(0, 0, 0, 0);
            return d;
        }

        function toEndOfDay(dateInput) {
            const d = new Date(dateInput);
            d.setHours(23, 59, 59, 999);
            return d;
        }

        $(document).ready(async function () {

            jQuery("#data-table_processing").show();
            const yearFilter = $('#yearFilter');
            const currentYear = new Date().getFullYear();
            const numberOfYears = 5;
            yearFilter.empty();
            for (let i = 0; i <= numberOfYears; i++) {
                yearFilter.append(`<option value="${currentYear - i}">${currentYear - i}</option>`);
            }
            $('#viewFilter').on('change', function () {
                const selected = $(this).val();

                if (selected === 'year') {
                    $('#monthFilter').hide();
                    $('#yearFilter').show();
                    $('#monthYearFilters').show();
                    $('#customDateFilters').hide();
                } else if (selected === 'month') {
                    $('#monthFilter').show();
                    $('#yearFilter').show();
                    $('#monthYearFilters').show();
                    $('#customDateFilters').hide();
                } else if (selected === 'custom') {
                    $('#monthYearFilters').hide();
                    $('#customDateFilters').show();
                }else{
                    $('#monthFilter').hide();
                    $('#yearFilter').hide();
                    $('#monthYearFilters').hide();
                    $('#customDateFilters').hide();
                }
            });

            $('#viewFilter').trigger('change');

            db.collection('sections').where('id', '==', active_id).get().then((snapshot) => {
                if (!snapshot.empty) {
                    var sectionData = snapshot.docs[0].data();
                    jQuery("#section_title").empty();
                    jQuery("#section_title").text(sectionData.name+' - '+sectionData.serviceType);
                    jQuery("#section_title").after('<p>{{trans("lang.here_quick_overview_of_your")}} ' + sectionData.name+' - '+sectionData.serviceType + ' {{trans("lang.platform_today")}}</p>')

                } else {
                    jQuery("#section_title").text('No section found');
                }
            })
                .catch((error) => {
                    console.error("Error getting section:", error);
                });
            const defaultFromDate = "{{ $fromDate }}";
            const defaultToDate = "{{ $toDate }}";
            loadVendorDashboardData('custom', null, null, defaultFromDate, defaultToDate, active_id, active_type);
            getTotalEarnings('custom', null, null, defaultFromDate, defaultToDate, active_id);
            loadOrderStatusCounts('custom', null, null, defaultFromDate, defaultToDate, active_id);
            loadDashboardLists('custom', null, null, defaultFromDate, defaultToDate, active_id, active_type);
        });

        $('#applyFilterBtn').on('click', function (e) {
            e.preventDefault();
            const view = $('#viewFilter').val();
            const year = parseInt($('#yearFilter').val()) || null;
            const month = parseInt($('#monthFilter').val()) || null;
            const startDate = $('#startDate').val() || null;
            const endDate = $('#endDate').val() || null;
            loadVendorDashboardData(view, year, month, startDate, endDate, active_id, active_type);
            getTotalEarnings(view, year, month, startDate, endDate, active_id);
            loadOrderStatusCounts(view, year, month, startDate, endDate, active_id);
            loadDashboardLists(view, year, month, startDate, endDate, active_id, active_type);
        });

        async function getTotalEarnings(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id) {
            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

            var v = Array(12).fill(0);
            var c = Array(12).fill(0);

            var vLast = Array(12).fill(0);
            var cLast = Array(12).fill(0);

            let now = new Date();
            // 🔹 Setup ranges
            let startOfThisPeriod = new Date();
            let endOfThisPeriod = new Date();
            let startOfLastPeriod = null;
            let endOfLastPeriod = null;

            if (filterType === 'year' && year) {
                startOfThisPeriod = new Date(year, 0, 1);
                endOfThisPeriod = new Date(year, 11, 31, 23, 59, 59);
                startOfLastPeriod = new Date(year - 1, 0, 1);
                endOfLastPeriod = new Date(year - 1, 11, 31, 23, 59, 59);
            } else if (filterType === 'month' && year && month) {
                startOfThisPeriod = new Date(year, month - 1, 1);
                endOfThisPeriod = new Date(year, month, 0, 23, 59, 59);
                startOfLastPeriod = new Date(year, month - 2, 1);
                endOfLastPeriod = new Date(year, month - 1, 0, 23, 59, 59);
            } else if (filterType === 'custom' && startDate && endDate) {
                startOfThisPeriod = toStartOfDay(startDate);
                endOfThisPeriod = toEndOfDay(endDate);
                startOfLastPeriod = new Date(startOfThisPeriod.getTime() - (endOfThisPeriod - startOfThisPeriod) - 1);
                endOfLastPeriod = new Date(startOfThisPeriod.getTime() - 1);
            }

            // Firestore timestamps
            const startTS = kweekFirestore.Timestamp.fromDate(startOfThisPeriod);
            const endTS = kweekFirestore.Timestamp.fromDate(endOfThisPeriod);
            const startLastTS = startOfLastPeriod ? kweekFirestore.Timestamp.fromDate(startOfLastPeriod) : null;
            const endLastTS = endOfLastPeriod ? kweekFirestore.Timestamp.fromDate(endOfLastPeriod) : null;

            let ordersQuery = db.collection('vendor_orders')
                .where('status', 'in', ["Order Completed"])
                /* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id);

            if (filterType) {
                ordersQuery = ordersQuery.where('createdAt', '>=', startTS)
                    .where('createdAt', '<=', endTS);
            }

            let ordersLastQuery = startLastTS && endLastTS
                ? db.collection('vendor_orders')
                    .where('status', 'in', ["Order Completed"])
                    /* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id)
                    .where('createdAt', '>=', startLastTS)
                    .where('createdAt', '<=', endLastTS)
                : null;

            const [ordersCurrSnap, ordersLastSnap] = await Promise.all([
                ordersQuery.get(),
                ordersLastQuery ? ordersLastQuery.get() : Promise.resolve({ docs: [] })
            ]);

            let totalEarning = 0, adminCommission = 0;
            let totalEarningLast = 0;

            function processOrders(docs, isCurrent = true) {
                let vArr = [];
                let cArr = [];
                let total = 0;
                let totalComm = 0;

                if (filterType === 'year') {
                    vArr = Array(12).fill(0);
                    cArr = Array(12).fill(0);
                } else if (filterType === 'month' && year && month) {
                    const daysInMonth = new Date(year, month, 0).getDate();
                    vArr = Array(daysInMonth).fill(0);
                    cArr = Array(daysInMonth).fill(0);
                } else if (filterType === 'custom' && startDate && endDate) {
                    const start = toStartOfDay(startDate);
                    const end = toEndOfDay(endDate);
                    const days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    vArr = Array(days).fill(0);
                    cArr = Array(days).fill(0);
                } else {
                    // default: yearly
                    vArr = Array(12).fill(0);
                    cArr = Array(12).fill(0);
                }

                docs.forEach((order) => {
                    let orderData = order.data();
                    let price = 0;
                    let minprice = 0;
                    let commission = 0;

                    if (orderData.products) {
                        orderData.products.forEach(product => {
                            if (product.price && product.quantity != 0) {
                                let extras = product.extras_price ? parseFloat(product.extras_price) * parseInt(product.quantity) : 0;
                                let productTotal = (parseFloat(product.price) * parseInt(product.quantity)) + extras;
                                price += isNaN(productTotal) ? 0 : productTotal;
                                minprice += isNaN(productTotal) ? 0 : productTotal;
                            }
                        });
                    }

                    if (orderData.discount && (intRegex.test(orderData.discount) || floatRegex.test(orderData.discount))) {
                        let discount = parseFloat(orderData.discount);
                        price -= discount;
                        minprice -= discount;
                    }

                    let tax = 0;
                    if (orderData.taxSetting?.type && orderData.taxSetting?.tax) {
                        tax = orderData.taxSetting.type === 'percentage'
                            ? (parseFloat(orderData.taxSetting.tax) * minprice) / 100
                            : parseFloat(orderData.taxSetting.tax);
                    }
                    price += isNaN(tax) ? 0 : tax;

                    if (orderData.deliveryCharge) price += parseFloat(orderData.deliveryCharge);

                    if (orderData.adminCommission) {
                        commission = orderData.adminCommissionType === 'percentage'
                            ? (price * parseFloat(orderData.adminCommission)) / 100
                            : parseFloat(orderData.adminCommission);
                    }
                    totalComm += commission;
                    total += price;

                    // 🔹 Put values in correct index
                    if (orderData.createdAt) {
                        let d = orderData.createdAt.toDate();

                        if (filterType === 'year') {
                            let monthIdx = d.getMonth();
                            vArr[monthIdx] += price;
                            cArr[monthIdx] += commission;
                        } else if (filterType === 'month') {
                            let dayIdx = d.getDate() - 1; // 0-based
                            vArr[dayIdx] += price;
                            cArr[dayIdx] += commission;
                        } else if (filterType === 'custom') {
                            let diff = Math.floor((d - toStartOfDay(startDate)) / (1000 * 60 * 60 * 24));
                            if (diff >= 0 && diff < vArr.length) {
                                vArr[diff] += price;
                                cArr[diff] += commission;
                            }
                        } else {
                            // default yearly
                            let monthIdx = d.getMonth();
                            vArr[monthIdx] += price;
                            cArr[monthIdx] += commission;
                        }
                    }
                });

                return { total, totalComm, vArr, cArr };
            }

            const currData = processOrders(ordersCurrSnap.docs, true);
            const lastData = processOrders(ordersLastSnap.docs, false);

            totalEarning = currData.total;
            adminCommission = currData.totalComm;
            totalEarningLast = lastData.total;
            totalCommLast = lastData.totalComm;
           
            let percentChange = totalEarningLast === 0
                ? (totalEarning === 0 ? 0 : 100)
                : ((totalEarning - totalEarningLast) / totalEarningLast) * 100;
            let percentCommission = totalCommLast === 0
                ? (adminCommission === 0 ? 0 : 100)
                : ((adminCommission - totalCommLast) / totalCommLast) * 100;
            if (currencyAtRight) {
                totalEarning = parseFloat(totalEarning).toFixed(decimal_degits) + "" + currentCurrency;
                adminCommission = parseFloat(adminCommission).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                totalEarning = currentCurrency + "" + parseFloat(totalEarning).toFixed(decimal_degits);
                adminCommission = currentCurrency + "" + parseFloat(adminCommission).toFixed(decimal_degits);
            }
            $("#earnings_count").text(totalEarning);
            $("#earnings_count_graph").text(totalEarning);
            $("#admincommission_count_graph").text(adminCommission);
            $("#admincommission_count").text(adminCommission);
            $("#total_earnings_header").text(totalEarning);
            $(".earnings_over_time").text(totalEarning);
            let ordersInfo = getArrowAndClass(percentChange);
            let commissionInfo = getArrowAndClass(percentCommission);
            if(filterType !== null){
                jQuery("#earning_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(percentChange).toFixed(2)}% vs last period`).removeClass('green red').addClass(ordersInfo.className);
                jQuery("#commission_percent").html(`<i class="fa ${commissionInfo.arrow}"></i> ${Math.abs(percentCommission).toFixed(2)}% vs last period`).removeClass('green red').addClass(commissionInfo.className);

            }
            let labels = [];
            let chartData = currData.vArr;
            let commissionData = currData.cArr;

            if (filterType === 'year' || !filterType) {
                labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
                    'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            } else if (filterType === 'month') {
                const daysInMonth = new Date(year, month, 0).getDate();
                labels = Array.from({ length: daysInMonth }, (_, i) => (i + 1).toString());
            } else if (filterType === 'custom') {
                const start = toStartOfDay(startDate);
                labels = chartData.map((_, i) => {
                    let d = new Date(start);
                    d.setDate(d.getDate() + i);
                    return `${d.getDate()}-${d.getMonth() + 1}`;
                });
            }
            else {
                labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                chartData = vArr;
                commissionData = cArr;
            }

            const ctx = $('#sales-chart')[0].getContext('2d');
            renderChart(ctx, chartData, labels);
            setCommision(commissionData, chartData);


            jQuery("#data-table_processing").hide();
        }
        async function loadOrderStatusCounts(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id) {
            let startTS = null;
            let endTS = null;

            if (filterType === 'year' && year) {
                let startOfYear = new Date(year, 0, 1);
                let endOfYear = new Date(year, 11, 31, 23, 59, 59);
                startTS = kweekFirestore.Timestamp.fromDate(startOfYear);
                endTS = kweekFirestore.Timestamp.fromDate(endOfYear);
            } else if (filterType === 'month' && year && month) {
                let startOfMonth = new Date(year, month - 1, 1);
                let endOfMonth = new Date(year, month, 0, 23, 59, 59);
                startTS = kweekFirestore.Timestamp.fromDate(startOfMonth);
                endTS = kweekFirestore.Timestamp.fromDate(endOfMonth);
            } else if (filterType === 'custom' && startDate && endDate) {
                let start = toStartOfDay(startDate);
                let end = toEndOfDay(endDate);
                startTS = kweekFirestore.Timestamp.fromDate(start);
                endTS = kweekFirestore.Timestamp.fromDate(end);
            }

            const statuses = {
                placed: ["Order Placed"],
                confirmed: ["Order Accepted", "Driver Accepted"],
                shipped: ["Order Shipped"/* , "In Transit" */],
                completed: ["Order Completed"],
                canceled: ["Order Rejected"],
                failed: ["Driver Rejected"],
                pending: ["Driver Pending"],
                intransit: ["In Transit"],
                cancelled: ["Order Cancelled"],
            };

            const promises = Object.entries(statuses).map(([key, statusArray]) => {
                let query = db.collection('vendor_orders')
                    .where('status', 'in', statusArray)
                    /* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id);

                if (startTS && endTS) {
                    query = query.where('createdAt', '>=', startTS)
                        .where('createdAt', '<=', endTS);
                }

                return query.get().then(snapshot => ({ key, count: snapshot.docs.length }));
            });

            const results = await Promise.all(promises);

            results.forEach(item => {
                const selector = `#${item.key}_count`;
                jQuery(selector).empty().val(item.count);
            });
            setorderStatus();

        }


        function buildHTML(snapshots) {
            var html = '';
            var count = 1;
            var rating = 0;
            snapshots.docs.forEach((listval) => {
                val = listval.data();
                val.id = listval.id;
                var route = '<?php echo route("stores.edit", ":id");?>';
                route = route.replace(':id', val.id);



                html = html + '<tr>';

                let profileSrc = val.photo ? val.photo : placeholderImage;

                html = html + '<td class="redirecttopage"><div class="top-driver-name">' +
                    '<img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" src="' + profileSrc + '" alt="image">' +
                    '<a href="' + route + '">' + val.title + '</a>' +
                    '</div></td>';
                if (val.hasOwnProperty('reviewsCount') && val.reviewsCount != 0) {
                    rating = Math.round(parseFloat(val.reviewsSum) / parseInt(val.reviewsCount));
                } else {
                    rating = 0;
                }

                html = html + '<td><ul class="rating" data-rating="' + rating + '">';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '</ul></td>';
                html = html + '<td><span class="action-btn"><a href="' + route + '" > <i class="mdi mdi-lead-pencil"></i></span></a></td>';
                html = html + '</tr>';

                rating = 0;
                count++;
            });
            return html;
        }


        function buildDriverHTML(snapshots) {
            var html = '';
            var count = 1;
            snapshots.docs.forEach((listval) => {
                val = listval.data();
                val.id = listval.id;
                var driverroute = '<?php echo route("drivers.edit", ":id");?>';
                driverroute = driverroute.replace(':id', val.id);

                var driverView = '{{route("drivers.view", ":id")}}';
                driverView = driverView.replace(':id', val.id);

                html = html + '<tr>';

                let profileSrc = val.profilePic ? val.profilePic : placeholderImage;

                html = html + '<td class="redirecttopage text-center"><div class="top-driver-name">' +
                    '<img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" src="' + profileSrc + '" alt="image">' +
                    '<a href="' + driverView + '">' + val.firstName + ' ' + val.lastName + '</a>' +
                    '</div></td>';
                html = html + '<td data-url="' + driverView + '" class="redirecttopage">' + val.orderCompleted + '</td>';
                html = html + '<td data-url="' + driverroute + '" class="redirecttopage"><span class="action-btn"><a><i class="mdi mdi-lead-pencil"></i></a></span></td>';
                html = html + '</tr>';
                count++;
            });
            return html;
        }

        function buildOrderHTML(snapshots) {
            var html = '';
            var count = 1;
            snapshots.docs.forEach((listval) => {
                val = listval.data();
                val.id = listval.id;
                var route = '<?php echo route("orders.edit", ":id"); ?>';
                route = route.replace(':id', val.id);

                var vendorroute = '<?php echo route("stores.view", ":id");?>';
                vendorroute = vendorroute.replace(':id', val.vendorID);

                html = html + '<tr>';

                html = html + '<td data-url="' + route + '" class="redirecttopage">' + val.id + '</td>';

                var price = 0;
                if (val.deliveryCharge != undefined) {
                    price = parseInt(val.deliveryCharge) + price;
                }
                if (val.tip_amount != undefined) {
                    price = parseInt(val.tip_amount) + price;
                }

                html = html + '<td data-url="' + vendorroute + '" class="redirecttopage">' + val.vendor.title + '</td>';

                var price = buildHTMLProductstotal(val);

                html = html + '<td data-url="' + route + '" class="redirecttopage">' + price + '</td>';
                html = html + '<td data-url="' + route + '" class="redirecttopage"><i class="fa fa-shopping-cart"></i> ' + val.products.length + '</td>';
                html = html + '</a></tr>';
                count++;
            });
            return html;
        }


        function renderChart(ctx, data, labels) {
            var gradientStroke = ctx.createLinearGradient(0, 0, 0, 300);
            gradientStroke.addColorStop(0, "rgba(255,94,0,0.4)");
            gradientStroke.addColorStop(1, "rgba(255,94,0,0)");

            var ticksStyle = {
                fontColor: '#495057',
                fontStyle: 'bold'
            };

            var mode = 'index';
            var intersect = true;

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Sales",
                        data: data,
                        borderColor: "#FF5E00",
                        backgroundColor: gradientStroke,
                        pointRadius: 0,
                        borderWidth: 3,
                        fill: true,
                        lineTension: 0.3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        mode: mode,
                        intersect: intersect,
                        callbacks: {
                            label: function (tooltipItems, data) {
                                let val = data.datasets[0].data[tooltipItems.index];
                                val = Number.isInteger(val) ? val : val.toFixed(2);

                                if (currencyAtRight) {
                                    return val + currentCurrency;
                                } else {
                                    return currentCurrency + val;
                                }
                            }
                        }
                    },
                    hover: {
                        mode: mode,
                        intersect: intersect
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontColor: '#495057',
                            fontSize: 12
                        }
                    },
                    scales: {
                        yAxes: [{
                            gridLines: {
                                display: true,
                                lineWidth: '1px',
                                color: 'rgba(0, 0, 0, .05)',
                                zeroLineColor: 'transparent'
                            },
                            ticks: $.extend({
                                beginAtZero: true,
                                callback: function (value) {
                                    // no long decimals on axis
                                    value = Number.isInteger(value) ? value : value.toFixed(2);

                                    if (value >= 1000) {
                                        value = (value / 1000) + "k";
                                    }

                                    return currencyAtRight ? value + currentCurrency : currentCurrency + value;
                                }
                            }, ticksStyle)
                        }],
                        xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle
                        }]
                    }
                }
            });
        }

        $(document).ready(function () {
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
        });


        function buildHTMLProductstotal(snapshotsProducts) {

            var adminCommission = snapshotsProducts.adminCommission;
            var discount = snapshotsProducts.discount;
            var couponCode = snapshotsProducts.couponCode;
            var extras = snapshotsProducts.extras;
            var extras_price = snapshotsProducts.extras_price;
            var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
            var takeAway = snapshotsProducts.takeAway;
            var tip_amount = snapshotsProducts.tip_amount;
            var status = snapshotsProducts.status;
            var products = snapshotsProducts.products;
            var deliveryCharge = snapshotsProducts.deliveryCharge;
            var totalProductPrice = 0;
            var total_price = 0;

            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

            if (products) {

                products.forEach((product) => {

                    var val = product;

                    price_item = parseFloat(val.price).toFixed(decimal_degits);
                    extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(decimal_degits);

                    totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                    var extras_price = 0;
                    if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                        extras_price = extras_price_item;
                    }
                    totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                    totalProductPrice = parseFloat(totalProductPrice).toFixed(decimal_degits);

                    total_price += parseFloat(totalProductPrice);

                });
            }

            if (intRegex.test(discount) || floatRegex.test(discount)) {

                discount = parseFloat(discount).toFixed(decimal_degits);
                total_price -= parseFloat(discount);

                if (currencyAtRight) {
                    discount_val = discount + "" + currentCurrency;
                } else {
                    discount_val = currentCurrency + "" + discount;
                }

            }

            if (intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) {

                deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
                total_price += parseFloat(deliveryCharge);

                if (currencyAtRight) {
                    deliveryCharge_val = deliveryCharge + "" + currentCurrency;
                } else {
                    deliveryCharge_val = currentCurrency + "" + deliveryCharge;
                }
            }


            if (intRegex.test(tip_amount) || floatRegex.test(tip_amount)) {

                tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
                total_price += parseFloat(tip_amount);
                total_price = parseFloat(total_price).toFixed(decimal_degits);

                if (currencyAtRight) {
                    tip_amount_val = tip_amount + "" + currentCurrency;
                } else {
                    tip_amount_val = currentCurrency + "" + tip_amount;
                }
            }

            if (currencyAtRight) {
                var total_price_val = total_price + "" + currentCurrency;
            } else {
                var total_price_val = currentCurrency + "" + total_price;
            }


            return total_price_val;
        }

        async function getSections() {
            var sections = database.collection('sections').where('isActive', '==', true);

            sections.get().then(async function (sectionsSnapshot) {
                sections = document.getElementById('sections');
                sections.innerHTML = '';
                sectionshtml = buildHTMLSections(sectionsSnapshot);
                sections.innerHTML = sectionshtml;
            })
        }

        function buildHTMLSections(sectionsSnapshot) {
            var html = '';
            var alldata = [];
            sectionsSnapshot.docs.forEach((listval) => {
                var datas = listval.data();
                datas.id = listval.id;
                alldata.push(datas);
            });

            var all_route = "{{ route('dashboard')}}";
            var img_url = "{{asset('images/shopping_cart.png')}}";
            var active_section = ''
            if (active_id == '') {
                active_section = 'section-selected';
            }
            html = html + '<div class="cat-item px-2 py-1 select_section ' + active_section + '"><a href="' + all_route + '" class="bg-white d-block p-2 text-center shadow-sm cat-link"><img alt="#" src="' + img_url + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"  class="img-fluid mb-2"><p class="m-0 small">All</p></a></div>';

            alldata.forEach((listval) => {
                var val = listval;
                var section_id = val.id;

                if (val.sectionImage) {
                    photo = val.sectionImage;
                } else {
                    photo = placeholderImage;
                }

                var active_section = '';
                if (active_id != undefined && active_id == section_id) {
                    active_section = 'section-selected';
                }

                var section_route = "{{ route('dashboard')}}?id=" + val.id + "&type=" + val.serviceTypeFlag;

                html = html + '<div class="cat-item px-2 py-1 select_section ' + active_section + '"><a href="' + section_route + '" class="bg-white d-block p-2 text-center shadow-sm cat-link"><img alt="#" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"  class="img-fluid mb-2"><p class="m-0 small">' + val.name + '</p></a></div>';
            });
            return html;
        }


        async function setorderStatus() {
            var placed = parseInt($('#placed_count').val()) || 0;
            var confirmed = parseInt($('#confirmed_count').val()) || 0;
            var shipped = parseInt($('#shipped_count').val()) || 0;
            var completed = parseInt($('#completed_count').val()) || 0;
            var canceled = parseInt($('#canceled_count').val()) || 0;
            var failed = parseInt($('#failed_count').val()) || 0;
            var pending = parseInt($('#pending_count').val()) || 0;
            var intransit = parseInt($('#intransit_count').val()) || 0;
            var cancelled = parseInt($('#cancelled_count').val()) || 0;

            var dataValues = [placed, confirmed, shipped, completed, canceled, failed, pending, intransit,cancelled];
            var totalOrders = dataValues.reduce((a, b) => a + b, 0);
            var ctx = document.getElementById("order_status").getContext("2d");

            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

            Chart.plugins.unregister(Chart.plugins.getAll().find(p => p.id === 'totalOrdersPlugin'));

            Chart.plugins.register({
                id: 'totalOrdersPlugin',
                beforeDraw: function (chart) {
                    if (chart.config.type === 'doughnut') {
                        var ctx = chart.chart.ctx;
                        var chartArea = chart.chartArea;

                        var centerX = (chartArea.left + chartArea.right) / 2;
                        var centerY = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.font = "16px Arial";
                        ctx.fillStyle = "#111";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillText("{{trans('lang.total_orders')}}", centerX, centerY - 20);

                        ctx.font = "bold 26px Arial";
                        ctx.fillText(totalOrders, centerX, centerY + 15);
                        ctx.restore();
                    }
                }
            });

            if (window.orderStatusChart) {
                window.orderStatusChart.data.datasets[0].data = dataValues;
                window.orderStatusChart.update();
            } else {
                window.orderStatusChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: [
                            "{{trans('lang.order_placed')}}",
                            "{{trans('lang.dashboard_order_confirmed')}}",
                            "{{trans('lang.dashboard_order_shipped')}}",
                            "{{trans('lang.dashboard_order_completed')}}",
                            "{{trans('lang.order_rejected')}}",
                            "{{trans('lang.dashboard_order_failed')}}",
                            "{{trans('lang.dashboard_order_pending')}}",
                            "{{trans('lang.in_transit')}}",
                            "{{trans('lang.order_canceled')}}"
                        ],
                        datasets: [{
                            data: dataValues,
                            backgroundColor: [
                                "#854cf0ff",
                                "#90E0EF",
                                "#F9C74F",
                                "#43AA8B",
                                "#F3722C",
                                "#B5179E",
                                "#577590",
                                "#0f85ecff",
                                "#e64d11ff",
                            ],
                            borderWidth: 2
                        }]
                    },

                    legend: {
                        position: "right",
                        labels: {
                            boxWidth: 20,
                            fontSize: 12,
                            generateLabels: function (chart) {
                                var data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function (label, i) {
                                        var value = data.datasets[0].data[i];
                                        var bgColor = data.datasets[0].backgroundColor[i];
                                        return {
                                            text: label + " - " + value,
                                            fillStyle: bgColor,
                                            strokeStyle: bgColor,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    }

                });
            }
        }

        function setCommision(commissionsData, earningsData) {
            const earnings = parseFloat(jQuery("#earnings_count").text().replace(currentCurrency, ""));
            const adminCommission = parseFloat(jQuery("#admincommission_count").text().replace(currentCurrency, ""));

            const data = {
                labels: ["Total Sales", "Admin Commissions"],
                datasets: [{
                    data: [earnings, adminCommission],
                    backgroundColor: ["#2EC7D9", "#28a745"],
                    borderWidth: 2
                }]
            };
            var labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            return new Chart(document.getElementById("commissions"), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Total Sales",
                            data: earningsData,
                            borderColor: "#2EC7D9",
                            backgroundColor: "transparent",
                            borderWidth: 3,
                            fill: false,
                        },
                        {
                            label: "Admin Commissions",
                            data: commissionsData,
                            borderColor: "#28a745",
                            backgroundColor: "transparent",
                            borderWidth: 3,
                            fill: false,
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, chartData) {
                                let datasetLabel = chartData.datasets[tooltipItem.datasetIndex].label || '';
                                let value = tooltipItem.yLabel;
                                if (currencyAtRight) {
                                    return datasetLabel + ": " + value.toFixed(2) + currentCurrency;
                                } else {
                                    return datasetLabel + ": " + currentCurrency + value.toFixed(2);
                                }
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            fontColor: '#333',
                            generateLabels: function (chart) {
                                let datasets = chart.data.datasets;
                                return datasets.map(function (ds, i) {
                                    let value = ds.data.reduce((a, b) => a + b, 0);
                                    return {
                                        text: ds.label + " $" + value.toLocaleString(),
                                        fillStyle: ds.borderColor,
                                        strokeStyle: ds.borderColor,
                                        hidden: !chart.isDatasetVisible(i),
                                        datasetIndex: i
                                    };
                                });
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            gridLines: {
                                color: "rgba(0,0,0,0.1)"
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }]
                    }
                }
            });
        }

        async function loadDashboardLists(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id, active_type) {
            let ref, snapshots, html;

            let startTS = null;
            let endTS = null;
            if (filterType === 'year' && year) {
                startTS = kweekFirestore.Timestamp.fromDate(new Date(year, 0, 1));
                endTS = kweekFirestore.Timestamp.fromDate(new Date(year, 11, 31, 23, 59, 59));
            } else if (filterType === 'month' && year && month) {
                startTS = kweekFirestore.Timestamp.fromDate(new Date(year, month - 1, 1));
                endTS = kweekFirestore.Timestamp.fromDate(new Date(year, month, 0, 23, 59, 59));
            } else if (filterType === 'custom' && startDate && endDate) {
                startTS = kweekFirestore.Timestamp.fromDate(toStartOfDay(startDate));
                endTS = kweekFirestore.Timestamp.fromDate(toEndOfDay(endDate));
            }

            const append_listvendors = document.getElementById('append_list');
            append_listvendors.innerHTML = '';

            let refVendor = db.collection('vendors').where('section_id', '==', active_id).orderBy('reviewsCount', 'desc').limit(5);

            /* if (startTS && endTS) {
                ref = ref
                    .where('createdAt', '>=', startTS)
                    .where('createdAt', '<=', endTS)
                    .orderBy('createdAt', 'desc')
                    .orderBy('reviewsCount', 'desc');
            } else {
                ref = ref.orderBy('reviewsCount', 'desc');
            } */
            
            let snapshotsVendor = await refVendor.get();
            html = buildHTML(snapshotsVendor);
            if(html == ''){
                append_listvendors.innerHTML = '<tr><td colspan="3">{{trans("lang.no_record_found")}}</td></tr>';
            }else{
                append_listvendors.innerHTML = html;
            }
            

            const append_listrecent_order = document.getElementById('append_list_recent_order');
            append_listrecent_order.innerHTML = '';

            ref = db.collection('vendor_orders')
                /* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id)
                .where('status', 'in', ["Order Placed", "Order Accepted", "Driver Pending", "Driver Accepted", "Order Shipped", "In Transit"]);

            /* if (startTS && endTS) {
                ref = ref
                    .where('createdAt', '>=', startTS)
                    .where('createdAt', '<=', endTS)
                    .orderBy('createdAt', 'desc');
            } else {
                ref = ref.orderBy('createdAt', 'desc');
            } */
            ref = ref.orderBy('createdAt', 'desc');
            snapshots = await ref.limit(10).get();
            html = buildOrderHTML(snapshots);
            if(html == ''){
                append_listrecent_order.innerHTML = '<tr><td colspan="3">{{trans("lang.no_record_found")}}</td></tr>';

            }else{
                append_listrecent_order.innerHTML = html;
            }

            const append_listtop_drivers = document.getElementById('append_list_top_drivers');
            append_listtop_drivers.innerHTML = '';

            ref = db.collection('users').where('role', '==', 'driver').where('serviceType', '==', active_type);

            /* if (startTS && endTS) {
                ref = ref
                    .where('createdAt', '>=', startTS)
                    .where('createdAt', '<=', endTS)
                    .orderBy('createdAt', 'desc')
                    .orderBy('orderCompleted', 'desc');
            } else {
                ref = ref.orderBy('orderCompleted', 'desc');
            } */
            ref = ref.orderBy('orderCompleted', 'desc');

            snapshots = await ref.limit(5).get();
            html = buildDriverHTML(snapshots);
            append_listtop_drivers.innerHTML = html;

            $('#storesTable, #orderTable, #driverTable').each(function () {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        order: [],
                        responsive: true,
                        paging: false,
                        info: false,
                        "language": {
                            "zeroRecords": "{{trans('lang.no_record_found')}}",
                            "emptyTable": "{{trans('lang.no_record_found')}}"
                        },
                    });
                }
            });
        }

        function loadVendorDashboardData(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id, active_type) {
            let startOfThisPeriod = new Date();
            let endOfThisPeriod = new Date();
            let startOfLastPeriod = null;
            let endOfLastPeriod = null;

            // Setup ranges
            if (filterType === 'year' && year) {
                startOfThisPeriod = new Date(year, 0, 1);
                endOfThisPeriod = new Date(year, 11, 31, 23, 59, 59);
                startOfLastPeriod = new Date(year - 1, 0, 1);
                endOfLastPeriod = new Date(year - 1, 11, 31, 23, 59, 59);
            } else if (filterType === 'month' && year && month) {
                startOfThisPeriod = new Date(year, month - 1, 1);
                endOfThisPeriod = new Date(year, month, 0, 23, 59, 59);
                startOfLastPeriod = new Date(year, month - 2, 1);
                endOfLastPeriod = new Date(year, month - 1, 0, 23, 59, 59);
            } else if (filterType === 'custom' && startDate && endDate) {
                startOfThisPeriod = toStartOfDay(startDate);
                endOfThisPeriod = toEndOfDay(endDate);
            }

            // Firestore Timestamps
            const startThisTS = kweekFirestore.Timestamp.fromDate(startOfThisPeriod);
            const endThisTS = kweekFirestore.Timestamp.fromDate(endOfThisPeriod);
            const startLastTS = startOfLastPeriod ? kweekFirestore.Timestamp.fromDate(startOfLastPeriod) : null;
            const endLastTS = endOfLastPeriod ? kweekFirestore.Timestamp.fromDate(endOfLastPeriod) : null;
            Promise.all([
                // All-time
                db.collection('vendor_orders')/* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id).orderBy('createdAt', 'desc').get(),
                db.collection('users').where("role", "==", "customer").orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "driver").where('isOwner','==',false).where('serviceType', '==', active_type).orderBy('createdAt', 'desc').get(),
                db.collection('vendors').where('section_id', '==', active_id).get(),

                // Current period
                db.collection('vendor_orders')/* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id).orderBy('createdAt', 'desc').where('createdAt', '<=', endThisTS).get(),
                db.collection('users').where("role", "==", "customer").where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "driver").where('isOwner','==',false).where('serviceType', '==', active_type).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy('createdAt', 'desc').get(),
                db.collection('vendors').where('section_id', '==', active_id).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).get(),

                // Last period
                startLastTS ? db.collection('vendor_orders')/* .where('vendor.section_id', '==', active_id) */.where('section_id','==',active_id).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('users').where("role", "==", "customer").where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy("createdAt").get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('users').where("role", "==", "driver").where('isOwner','==',false).where('serviceType', '==', active_type).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy('createdAt', 'desc').get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('vendors').where('section_id', '==', active_id).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).get() : Promise.resolve({ docs: [] })
            ])
                .then(([allOrders, allUsers, allDrivers, allVendors,
                    ordersCurr, usersCurr, driversCurr, vendorsCurr,
                    ordersLast, usersLast, driversLast, vendorsLast]) => {


                    let allVendorsFiltered = allVendors.docs.filter(doc => doc.data().title && doc.data().title !== '');
                    let vendorsCurrFiltered = vendorsCurr.docs.filter(doc => doc.data().title && doc.data().title !== '');
                    let vendorsLastFiltered = vendorsLast.docs.filter(doc => doc.data().title && doc.data().title !== '');

                    let totalOrdersDisplay = filterType ? ordersCurr.docs.length : allOrders.docs.length;
                    let totalUsersDisplay = filterType ? usersCurr.docs.length : allUsers.docs.length;
                    let totalDriversDisplay = filterType ? driversCurr.docs.length : allDrivers.docs.length;
                    let totalVendorsDisplay = filterType ? vendorsCurrFiltered.length : allVendors.docs.length;

                    let totalOrdersLastDisplay = filterType ? ordersLast.docs.length : 0;
                    let totalUsersLastDisplay = filterType ? usersLast.docs.length : 0;
                    let totalDriversLastDisplay = filterType ? driversLast.docs.length : 0;
                    let totalVendorsLastDisplay = filterType ? vendorsLastFiltered.length : 0;

                    function calcPercent(curr, last) {
                        return last === 0 ? (curr === 0 ? 0 : 100) : ((curr - last) / last) * 100;
                    }
           
                    let ordersPercent = calcPercent(totalOrdersDisplay, totalOrdersLastDisplay);
                    let usersPercent = calcPercent(totalUsersDisplay, totalUsersLastDisplay);
                    let driversPercent = calcPercent(totalDriversDisplay, totalDriversLastDisplay);
                    let vendorsPercent = calcPercent(totalVendorsDisplay, totalVendorsLastDisplay);

                    let ordersInfo = getArrowAndClass(ordersPercent);
                    let usersInfo = getArrowAndClass(usersPercent);
                    let driversInfo = getArrowAndClass(driversPercent);
                    let vendorsInfo = getArrowAndClass(vendorsPercent);

                    jQuery("#order_count").text(totalOrdersDisplay);
                    jQuery("#users_count").text(totalUsersDisplay);
                    jQuery("#driver_count").text(totalDriversDisplay);
                    jQuery("#vendor_count").text(totalVendorsDisplay);
                    if(filterType !== null){
                        jQuery("#orders_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(ordersPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(ordersInfo.className);
                        jQuery("#customer_percent").html(`<i class="fa ${usersInfo.arrow}"></i> ${Math.abs(usersPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(usersInfo.className);
                        jQuery("#driver_percent").html(`<i class="fa ${driversInfo.arrow}"></i> ${Math.abs(driversPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(driversInfo.className);
                        jQuery("#vendor_percent").html(`<i class="fa ${vendorsInfo.arrow}"></i> ${Math.abs(vendorsPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(vendorsInfo.className);
                    }

                })
                .catch(err => console.error(err));
        }
        function getArrowAndClass(percent) {
            return {
                arrow: percent > 0 ? 'fa-arrow-up' : 'fa-arrow-down',
                className: percent > 0 ? 'green' : 'red'
            };
        }

    </script>
@endsection