@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.driver_disburesement')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.driver_disburesement')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.driver_disburesement')}}</h3>
                        <span class="counter ml-3 total_count">{{ $totalPayouts ?? 0 }}</span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.driver_disburesement')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.driver_disburesement_table_text')}}</p>
                   </div>    
                   <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('driversPayouts.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.drivers_payout_create')}}</a>
                    </div>
                </div>     
                 <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="success_top" style="display:none"></div>
                        <div class="table-responsive m-t-10">
                         <table id="example24"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    @if($id == '')
                                        <th>{{ trans('lang.driver')}}</th>
                                    @endif
                                    <th>{{trans('lang.paid_amount')}}</th>
                                    <th>{{trans('lang.drivers_payout_note')}}</th>
                                    <th>{{trans('lang.drivers_payout_paid_date')}}</th>
                                    <th>{{trans('lang.status')}}</th>
                                    <th>{{trans('lang.withdraw_method')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
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
<div class="modal fade" id="bankdetailsModal" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{trans('lang.bankdetails')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="">
                    <div class="form-row">
                        <input type="hidden" name="driverId" id="driverId">
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{
                                    trans('lang.bank_name')}}</label>
                                <div class="col-12">
                                    <input type="text" name="bank_name" class="form-control" id="bankName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{
                                    trans('lang.branch_name')}}</label>
                                <div class="col-12">
                                    <input type="text" name="branch_name" class="form-control" id="branchName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-4 control-label">{{
                                    trans('lang.holer_name')}}</label>
                                <div class="col-12">
                                    <input type="text" name="holer_name" class="form-control" id="holderName">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{
                                    trans('lang.account_number')}}</label>
                                <div class="col-12">
                                    <input type="text" name="account_number" class="form-control" id="accountNumber">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{
                                    trans('lang.other_information')}}</label>
                                <div class="col-12">
                                    <input type="text" name="other_information" class="form-control" id="otherDetails">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary save-form-btn" id="submit_accept">
                        {{trans('lang.accept')}}</a>
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('lang.close')}}</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cancelRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{trans('lang.cancel_payout_request')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="data-table_processing_modal" class="dataTables_processing panel panel-default"
                        style="display: none;">{{trans('lang.processing')}}
                </div>
                <form class="">
                    <div class="form-row">
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{trans('lang.notes')}}</label>
                                <div class="col-12">
                                    <textarea name="admin_note" class="form-control" id="admin_note" cols="5" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary save-form-btn" id="submit_cancel">
                        {{trans('lang.submit')}}</a>
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('lang.close')}}</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="payoutResponseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{trans('lang.payout_response')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="payout-response"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                    {{trans('lang.close')}}</a>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var id = @json($id ?? '');
    var database = kweekDb();
    var table;
    var email_templates = database.collection('email_templates').where('type', '==', 'payout_request_status');
    var emailTemplatesData = null;
    $(document).ready(function () {
        email_templates.get().then(async function (snapshots) {
            emailTemplatesData = snapshots.docs[0].data();
        });
        $(document.body).on('click', '.redirecttopage', function () {
            window.location.href = $(this).attr('href');
        });
        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });

        if (id) {
            var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID=' + id));
            getDriverName(id);
        }

        table = $('#example24').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: @json(route('payoutRequests.driver.disbursement.datatable')),
                type: 'GET',
                data: function (d) {
                    d._token = '{{ csrf_token() }}';
                    if (id) {
                        d.driver_id = id;
                    }
                },
                dataSrc: function (json) {
                    if (json.error) {
                        console.error('Driver disbursements datatable error', json.error);
                    }
                    if (typeof json.recordsTotal !== 'undefined') {
                        $('.total_count').text(json.recordsTotal);
                    }
                    return json.data || [];
                },
                error: function (xhr) {
                    console.error('Driver disbursements datatable error', xhr.status, xhr.responseText);
                    $('#data-table_processing').hide();
                }
            },
            order: [[id ? 3 : 4, 'desc']],
            columnDefs: [{
                orderable: false,
                targets: id ? [0, 5, 6] : [0, 7]
            }],
            language: {
                zeroRecords: '{{ trans("lang.no_record_found") }}',
                emptyTable: '{{ trans("lang.no_record_found") }}',
                processing: ''
            },
            dom: 'lfrtipB',
            buttons: [{
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i> {{ trans("lang.export_as") }}',
                className: 'btn btn-info',
                buttons: [
                    { extend: 'excelHtml5', text: '{{ trans("lang.export_excel") }}' },
                    { extend: 'pdfHtml5', text: '{{ trans("lang.export_pdf") }}' },
                    { extend: 'csvHtml5', text: '{{ trans("lang.export_csv") }}' }
                ]
            }],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3;
                }).remove();
            }
        });
    });
    async function getDriverName(driverId) {
        var snapshots = await database.collection('users').doc(driverId).get();
        if(snapshots.exists){
            var driverData = snapshots.data();
            var driverName = driverData.firstName+' '+driverData.lastName;
            $('.driverTitle').html('{{trans("lang.payout_request")}} - ' + driverName);
            if (driverData.serviceType) {
                if (driverData.serviceType == "cab-service") {
                    var url = "{{route('drivers.rides','driverId')}}";
                    url = url.replace('driverId', driverData.id);
                    $('.service_type_orders').html('<a href="' + url + '">{{trans('lang.order_plural')}}</a>');
                } else if (driverData.serviceType == "rental-service") {
                    var url = "{{route('rental_orders.driver','id')}}";
                    url = url.replace("id", driverData.id);
                    $('.service_type_orders').html('<a href="' + url + '">{{trans('lang.order_plural')}}</a>');
                } else if (driverData.serviceType == "delivery-service" || driverData.serviceType == "ecommerce-service") {
                    var url = "{{route('orders','id')}}";
                    url = url.replace("id", 'driverId=' + driverData.id);
                    $('.service_type_orders').html('<a href="' + url + '">{{trans('lang.order_plural')}}</a>');
                } else if (driverData.serviceType == "parcel_delivery") {
                    var url = "{{route('parcel_orders.driver','id')}}";
                    url = url.replace("id", driverData.id);
                    $('.service_type_orders').html('<a href="' + url + '">{{trans('lang.order_plural')}}</a>');
                }
            }
        }
    }
    function buildHTML(val) {
        var html = [];
        var alldata = [];
        var number = [];
        var amount = '';
        var price = val.amount;
        if (intRegex.test(price) || floatRegex.test(price)) {
            price = parseFloat(price);
        } else {
            price = 0;
        }
        if (currencyAtRight) {
            amount = parseFloat(price).toFixed(decimal_degits) + "" + currentCurrency;
        } else {
            amount = currentCurrency + "" + parseFloat(price).toFixed(decimal_degits);
        }
        html.push('<input type="checkbox" id="is_open_' + val.recid + '" class="is_open" dataId="' + val.recid + '"><label class="col-3 control-label"\n' +
        'for="is_open_' + val.recid + '" ></label>');
        if (id == "") {
            var route_url = '{{route("drivers.view",":id")}}';
            route_url = route_url.replace(':id', val.driverID);
            html.push('<td><a href="' + route_url + '" class="redirecttopage ">'+val.title+'</a></td>');
        }
        html.push('<td>' + amount + '</td>');
        var date = val.paidDate.toDate().toDateString();
        var time = val.paidDate.toDate().toLocaleTimeString('en-US');
        html.push('<td>' + val.note + '</td>');
        html.push('<td>' + date + ' ' + time + '</td>');

        if (val.paymentStatus == 'Pending' || val.paymentStatus == 'In Process') {
            html.push('<span class="order_placed badge badge-info"><span>' + val.paymentStatus + '</span></span>');
        } else if (val.paymentStatus == 'Reject' || val.paymentStatus == 'Failed') {
            html.push('<span class="order_rejected badge badge-danger"><span>' + val.paymentStatus + '</span></span>');
        } else if (val.paymentStatus == 'Success') {
            html.push('<span class="order_completed badge badge-success"><span>' + val.paymentStatus + '</span></span>');
        } else {
            html.push('');
        }

        if (val.withdrawMethod) {
            var selectedwithdrawMethod = (val.withdrawMethod == "bank") ? "Bank Transfer" : val.withdrawMethod;
            html.push('<td><span style="text-transform:capitalize">' + selectedwithdrawMethod + '</span></td>');
        } else {
            html.push('');
        }
        var actionHtml = '';

        actionHtml = actionHtml + '<td><span class="action-btn">';
        if (val.paymentStatus != "Reject" && val.paymentStatus != "Success") {
            actionHtml = actionHtml + '<a id="' + val.id + '" name="driver_view" data-auth="' + val.driverID + '" data-amount = "' + amount + '" href="javascript:void(0)" data-toggle="modal" data-target="#bankdetailsModal" class=""><span data-toggle="tooltip" title="Manual Pay"><i class="mdi mdi-bank"></i></span></a>';
        }
        if (val.withdrawMethod && val.withdrawMethod != "bank" && val.paymentStatus != "Reject" && val.paymentStatus != "Success") {
            actionHtml = actionHtml + '<a id="' + val.id + '" name="driver_pay"  data-auth="' + val.driverID + '" data-amount="' + price + '" data-method="'+val.withdrawMethod+'" href="javascript:void(0)" class="" data-toggle="tooltip" title="Pay Online"><i class="mdi mdi-credit-card"></i></a>';
        }
        if (val.paymentStatus != "Reject" && val.paymentStatus != "Success") {
            actionHtml = actionHtml + '<a id="' + val.id + '" name="driver_reject_request" data-toggle="modal" data-target="#cancelRequestModal" data-auth="' + val.driverID + '" data-amount = "' + amount + '" data-price="' + price + '" href="javascript:void(0)" class=""><span data-toggle="tooltip" title="Cancel Request"><i class="mdi mdi-close-circle"></i></span></a>';
        }
        if (val.paymentStatus == "In Process") {
            actionHtml = actionHtml + '<a id="' + val.id + '" name="driver_check_status" data-auth="' + val.driverID + '" data-amount="' + price + '" data-method="'+val.withdrawMethod+'" href="javascript:void(0)" class="" data-toggle="tooltip" title="Check Payment Status"><i class="mdi mdi-comment-question-outline"></i></a>';
        }
        actionHtml = actionHtml + '<a id="' + val.recid + '" class="delete-btn" name="driver_payouts-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        actionHtml = actionHtml + '</span></td>';
        html.push(actionHtml);
        return html;
    }
    async function getDriverBankDetails() {
        var driverId = $('#driverId').val();
        await database.collection('users').where("id", "==", driverId).get().then(async function (snapshotss) {
            if (snapshotss.docs[0]) {
                var user_data = snapshotss.docs[0].data();
                if (user_data.userBankDetails) {
                    $('#bankName').val(user_data.userBankDetails.bankName);
                    $('#branchName').val(user_data.userBankDetails.branchName);
                    $('#holderName').val(user_data.userBankDetails.holderName);
                    $('#accountNumber').val(user_data.userBankDetails.accountNumber);
                    $('#otherDetails').val(user_data.userBankDetails.otherDetails);
                }
            }
        });
    }
    $(document).on("click", "a[name='driver_view']", function (e) {
        $('#bankName').val("");
        $('#branchName').val("");
        $('#holderName').val("");
        $('#accountNumber').val("");
        $('#otherDetails').val("");
        var id = this.id;
        var auth = $(this).attr('data-auth');
        var amount = $(this).attr('data-amount');
        $('#driverId').val(auth);
        getDriverBankDetails();
        $('#submit_accept').attr('data-id',id).attr('data-amount',amount).attr('data-auth',auth);
    });
    $(document).on("click", "a[name='driver_pay']", async function (e) {
        var $this = $(this);
        $(this).prop('disabled',true).css({'cursor':'default','opacity':'0.5'});
        var data = {};
        data['payoutId'] = this.id;
        data['method'] = $(this).data('method');
        data['amount'] = $(this).data('amount');
        data['user'] =  await getUserData($(this).data('auth'));
        data['settings'] = await getPaymentSettings();
        if(data['method'] != "undefined"){
            $.ajax({
                type: 'POST',
                data: {
                    data: btoa(JSON.stringify(data)),
                },
                url: "{{url('pay-to-user')}}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log(response);
                    if(response.success == true){
                        $(".success_top").show().html("");
                        $(".success_top").append("<p>"+response.message+"</p>");
                        window.scrollTo(0, 0);
                        database.collection('driver_payouts').doc(data['payoutId']).update({'paymentStatus': response.status,'payoutResponse' : response.result}).then(async function (result) {
                            if (data['user'] && data['user'] != undefined){
                                var emailData = await sendMailToRestaurant(data['user'], data['payoutId'], 'Approved', data['amount']);
                                if(emailData){
                                    window.location.reload();
                                }
                            }
                        });
                    }else{
                        $this.prop('disabled', false).css({'cursor': '', 'opacity': '1'});
                        $(".error_top").show().html("");
                        $(".error_top").append("<p>"+response.message+"</p>");
                        window.scrollTo(0, 0);
                    }
                }
            });
        }
    });
    $(document).on("click", "a[name='driver_check_status']", async function (e) {
        $(this).prop('disabled',true).css({'cursor':'default','opacity':'0.5'});
        var data = {};
        data['payoutId'] = this.id;
        data['method'] = $(this).data('method');
        data['amount'] = $(this).data('amount');
        data['user'] =  await getUserData($(this).data('auth'));
        data['settings'] = await getPaymentSettings();
        data['payoutDetail'] = await getPayoutDetail(data['payoutId']);
        if(data['method'] != "undefined"){
            $.ajax({
                type: 'POST',
                data: {
                    data: btoa(JSON.stringify(data)),
                },
                url: "{{url('check-payout-status')}}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if(response.success == true){
                        $(".success_top").show().html("");
                        $(".success_top").append("<p>"+response.message+"</p>");
                        window.scrollTo(0, 0);
                    }else{
                        $(".error_top").show().html("");
                        $(".error_top").append("<p>"+response.message+"</p>");
                        window.scrollTo(0, 0);
                    }
                    $(this).prop('disabled',false).css({'cursor':'pointer','opacity':'1'});
                    if(response.result && response.status){
                        database.collection('driver_payouts').doc(data['payoutId']).update({'paymentStatus': response.status,'payoutResponse' : response.result});
                        $("#payoutResponseModal .payout-response").html(JSON.stringify(JSON.parse(JSON.stringify(response.result)),null,4));
                        $("#payoutResponseModal").modal('show');
                    }
                }
            });
        }
    });
    async function getPaymentSettings() {
        var settings = {};
        await database.collection('settings').get().then(async function (snapshots) {
            snapshots.forEach((doc) => {
                if(doc.id == "flutterWave"){
                    settings["flutterwave"] = doc.data();
                }
                if(doc.id == "paypalSettings"){
                    settings["paypal"] = doc.data();
                }
                if(doc.id == "razorpaySettings"){
                    settings["razorpay"] = doc.data();
                }
                if(doc.id == "stripeSettings"){
                    settings["stripe"] = doc.data();
                }
            });
        });
        return settings;
    }
    async function payoutDriverfunction(driver) {
        var payoutDriver = '';
        var routedriver = '{{route("drivers.view",":id")}}';
        routedriver = routedriver.replace(':id', driver);
        let driverInfo = null;
        await database.collection('users').where("id", "==", driver).get().then(async function (snapshotss) {
            if (snapshotss.docs[0]) {
                var driver_data = snapshotss.docs[0].data();
                payoutDriver = driver_data.firstName + " " + driver_data.lastName;
                driverInfo = {
                    name: payoutDriver,
                    sectionId: driver_data.sectionId || null,
                    isOwner: driver_data.isOwner,
                };
                jQuery(".driver_" + driver).attr("data-url", routedriver).html(payoutDriver);
            } else {
                jQuery(".driver_" + driver).attr("data-url", "#").html('');
            }
        });
        return driverInfo;
    }
    async function getUserData(driverId) {
        var data = '';
        await database.collection('users').where("id", "==", driverId).get().then(async function (snapshotss) {
            if (snapshotss.docs[0]) {
                data = snapshotss.docs[0].data();
            }
        });
        if(data.id){
            await database.collection('withdraw_method').where("userId", "==", data.id).get().then(async function (snapshotss) {
                if (snapshotss.docs.length) {
                    data['withdrawMethod'] = snapshotss.docs[0].data();
                }
            });
        }
        return data;
    }
    async function sendMailToRestaurant(user, id, status, amount) {
        var formattedDate = new Date();
        var month = formattedDate.getMonth() + 1;
        var day = formattedDate.getDate();
        var year = formattedDate.getFullYear();
        month = month < 10 ? '0' + month : month;
        day = day < 10 ? '0' + day : day;
        formattedDate = day + '-' + month + '-' + year;
        var subject = emailTemplatesData.subject;
        subject = subject.replace(/{requestid}/g, id);
        emailTemplatesData.subject = subject;
        var message = emailTemplatesData.message;
        message = message.replace(/{username}/g, user.firstName + ' ' + user.lastName);
        message = message.replace(/{date}/g, formattedDate);
        message = message.replace(/{requestid}/g, id);
        message = message.replace(/{status}/g, status);
        message = message.replace(/{amount}/g, amount);
        message = message.replace(/{usercontactinfo}/g, user.phoneNumber);
        emailTemplatesData.message = message;
        var url = "{{url('send-email')}}";
        return await sendEmail(url, emailTemplatesData.subject, emailTemplatesData.message, [user.email]);
    }
    async function getPayoutDetail(payoutId) {
        var snapshot = await database.collection('driver_payouts').doc(payoutId).get();
        return snapshot.data();
    }
    $(document).on("click", "a[name='driver_reject_request']", function (e) {
        $('#admin_note').val("");
        var id = this.id;
        var auth = $(this).attr('data-auth');
        var amount = $(this).attr('data-amount');
        var price = $(this).attr('data-price');
        $('#submit_cancel').attr('data-id',id).attr('data-amount',amount).attr('data-price',price).attr('data-auth',auth);
    });
    $(document).on("click", "#submit_cancel", async function (e) {
        $(this).prop('disabled',true).css({'cursor':'default','opacity':'0.5'});
        var id = $(this).data('id');
        var auth = $(this).data('auth');
        var user = await getUserData(auth);
        var priceadd = $(this).data('price');
        var amount = $(this).data('amount');
        var admin_note = $("#admin_note").val();
        jQuery("#data-table_processing").show();
        database.collection('users').where("id", "==", auth).get().then(function (resultdriver) {
            if (resultdriver.docs.length) {
                var driver = resultdriver.docs[0].data();
                var wallet_amount = 0;
                if (isNaN(driver.wallet_amount) || driver.wallet_amount == undefined) {
                    wallet_amount = 0;
                } else {
                    wallet_amount = driver.wallet_amount;
                }
                price = parseFloat(wallet_amount) + parseFloat(priceadd);
                if (!isNaN(price)) {
                    database.collection('driver_payouts').doc(id).update({'paymentStatus': 'Reject','adminNote':admin_note}).then(function (result) {
                        database.collection('users').doc(driver.id).update({'wallet_amount': price}).then(async function (result) {
                            var wId = database.collection('temp').doc().id;
                            database.collection('wallet').doc(wId).set({
                                'amount': parseFloat(priceadd),
                                'date': kweekDb.FieldValue.serverTimestamp(),
                                'id': wId,
                                'isTopUp': false,
                                'order_id': id,
                                'payment_method': 'Wallet',
                                'payment_status': 'Refund success',
                                'transactionUser': 'driver',
                                'user_id': driver.id,
                                'note': 'Refund by admin'
                            });
                            if (user && user != undefined) {
                                var emailData = await sendMailToRestaurant(user, id, 'Disapproved', amount);
                                if (emailData) {
                                    window.location.reload();
                                }
                            } else {
                                window.location.reload();
                            }
                        });
                    });
                }
            } else {
                alert('Driver not found.');
            }
        });
    });
    $(document).on("click", "#submit_accept", async function (e) {
        $(this).prop('disabled',true).css({'cursor':'default','opacity':'0.5'});
        var id = $(this).data('id');
        var auth = $(this).data('auth');
        var user = await getUserData(auth);
        var amount = $(this).data('amount');
        jQuery("#data-table_processing").show();
        database.collection('driver_payouts').doc(id).update({'paymentStatus': 'Success'}).then(async function (result) {
            if (user && user != undefined) {
                var emailData = await sendMailToRestaurant(user, id, 'Approved', amount);
                if (emailData) {
                    window.location.reload();
                }
            } else {
                window.location.reload();
            }
        });
    });
    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        if (!$('#example24 .is_open:checked').length) {
            alert("{{ trans('lang.select_delete_alert') }}");
            return;
        }
        if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
            return;
        }
        var ids = [];
        $('#example24 .is_open:checked').each(function () {
            ids.push($(this).attr('dataId'));
        });
        $.post('{{ route("payoutRequests.driver.disbursement.destroy") }}', {
            _token: '{{ csrf_token() }}',
            ids: ids
        }).done(function () {
            table.ajax.reload();
        });
    });
    $(document).on('click', '.btn-delete-driver-payout', function () {
        if (!confirm('{{ trans("lang.delete_alert") }}')) {
            return;
        }
        var payoutId = $(this).attr('id');
        $.post('{{ route("payoutRequests.driver.disbursement.destroy") }}', {
            _token: '{{ csrf_token() }}',
            id: payoutId
        }).done(function () {
            table.ajax.reload();
        });
    });
</script>
@endsection
