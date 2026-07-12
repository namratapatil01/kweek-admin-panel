@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.store_disburesement') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.store_disburesement') }}</li>
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
                                <h3 class="mb-0">{{ trans('lang.store_disburesement') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.store_disburesement') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.store_disburesement_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">                                        
                                        <a class="btn-primary btn rounded-full" href="{!! route('vendorsPayouts.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.vendors_payout_create') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="error_top" style="display:none"></div>
                                <div class="success_top" style="display:none"></div>
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                @if ($id == '')
                                                    <th>{{ trans('lang.vendor') }}</th>
                                                @endif
                                                <th>{{ trans('lang.paid_amount') }}</th>
                                                <th>{{ trans('lang.vendors_payout_note') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.withdraw_method') }}</th>
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
    <div class="modal fade" id="bankdetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.bankdetails') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <input type="hidden" name="vendorId" id="vendorId">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.bank_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.branch_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{ trans('lang.holer_name') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.account_number') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="account_number" class="form-control" id="accountNumber">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.other_information') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="other_information" class="form-control" id="otherDetails">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary save-form-btn" id="submit_accept">
                            {{ trans('lang.accept') }}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('lang.close') }}</a>
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
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.cancel_payout_request') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.notes') }}</label>
                                    <div class="col-12">
                                        <textarea name="admin_note" class="form-control" id="admin_note" cols="5" rows="5"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary save-form-btn" id="submit_cancel">
                            {{ trans('lang.submit') }}</a>
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('lang.close') }}</a>
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
                    <h5 class="modal-title">{{ trans('lang.payout_response') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="payout-response"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{ trans('lang.close') }}</a>
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
        var email_templates = database.collection('email_templates').where('type', '==', 'payout_request_status');
        var emailTemplatesData = null;
        var table;
        $(document).ready(function() {
            email_templates.get().then(async function(snapshots) {
                emailTemplatesData = snapshots.docs[0].data();
            });
            $(document.body).on('click', '.redirecttopage', function() {
                window.location.href = $(this).attr('href');
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

            table = $('#example24').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: @json(route('payoutRequests.vendor.disbursement.datatable')),
                    type: 'GET',
                    data: function (d) {
                        d._token = '{{ csrf_token() }}';
                        if (id) {
                            d.vendor_id = id;
                        }
                    },
                    dataSrc: function (json) {
                        if (json.error) {
                            console.error('Vendor disbursements datatable error', json.error);
                        }
                        if (typeof json.recordsTotal !== 'undefined') {
                            $('.total_count').text(json.recordsTotal);
                        }
                        return json.data || [];
                    },
                    error: function (xhr) {
                        console.error('Vendor disbursements datatable error', xhr.status, xhr.responseText);
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

            if (id) {
                getStoreName(id);
            }
        });
        async function getStoreName(vendorId) {
            await database.collection('vendors').where('id', '==', vendorId).get().then(async function(snapshots) {
                if (!snapshots.empty) {
                    var vendorData = snapshots.docs[0].data();
                    vendorName = vendorData.title;
                    $('.vendorTitle').html('{{ trans('lang.payout_request') }} - ' + vendorName);
                    var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
                    $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID=' + vendorData.author));
                }
            });
        }
        async function getVendorBankDetails() {
            var vendorId = $('#vendorId').val();
            await database.collection('users').where("vendorID", "==", vendorId).where('role','==','vendor').get().then(async function(snapshotss) {
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
        $(document).on("click", "a[name='vendor_view']", function(e) {
            $('#bankName').val("");
            $('#branchName').val("");
            $('#holderName').val("");
            $('#accountNumber').val("");
            $('#otherDetails').val("");
            var id = this.id;
            var auth = $(this).attr('data-auth');
            var amount = $(this).attr('data-amount');
            $('#vendorId').val(auth);
            getVendorBankDetails();
            $('#submit_accept').attr('data-id', id).attr('data-amount', amount).attr('data-auth', auth);
        });
        $(document).on("click", "a[name='vendor_pay']", async function(e) {
            $(this).prop('disabled', true).css({
                'cursor': 'default',
                'opacity': '0.5'
            });
            var data = {};
            data['payoutId'] = this.id;
            data['method'] = $(this).data('method');
            data['amount'] = $(this).data('amount');
            data['user'] = await getUserData($(this).data('auth'));
            data['settings'] = await getPaymentSettings();
            if (data['method'] != "undefined") {
                $.ajax({
                    type: 'POST',
                    data: {
                        data: btoa(JSON.stringify(data)),
                    },
                    url: "{{ url('pay-to-user') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success == true) {
                            $(".success_top").show().html("");
                            $(".success_top").append("<p>" + response.message + "</p>");
                            window.scrollTo(0, 0);
                            database.collection('payouts').doc(data['payoutId']).update({
                                'paymentStatus': response.status,
                                'payoutResponse': response.result
                            }).then(async function(result) {
                                if (data['user'] && data['user'] != undefined) {
                                    var emailData = await sendMailToRestaurant(data['user'], data['payoutId'], 'Approved', data['amount']);
                                    if (emailData) {
                                        window.location.reload();
                                    }
                                }
                            });
                        } else {
                            $(".error_top").show().html("");
                            $(".error_top").append("<p>" + response.message + "</p>");
                            window.scrollTo(0, 0);
                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);
                        }
                    }
                });
            }
        });
        $(document).on("click", "a[name='vendor_check_status']", async function(e) {
            $(this).prop('disabled', true).css({
                'cursor': 'default',
                'opacity': '0.5'
            });
            var data = {};
            data['payoutId'] = this.id;
            data['method'] = $(this).data('method');
            data['amount'] = $(this).data('amount');
            data['user'] = await getUserData($(this).data('auth'));
            data['settings'] = await getPaymentSettings();
            data['payoutDetail'] = await getPayoutDetail(data['payoutId']);
            if (data['method'] != "undefined") {
                $.ajax({
                    type: 'POST',
                    data: {
                        data: btoa(JSON.stringify(data)),
                    },
                    url: "{{ url('check-payout-status') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success == true) {
                            $(".success_top").show().html("");
                            $(".success_top").append("<p>" + response.message + "</p>");
                            window.scrollTo(0, 0);
                        } else {
                            $(".error_top").show().html("");
                            $(".error_top").append("<p>" + response.message + "</p>");
                            window.scrollTo(0, 0);
                        }
                        $(this).prop('disabled', false).css({
                            'cursor': 'pointer',
                            'opacity': '1'
                        });
                        if (response.result && response.status) {
                            database.collection('payouts').doc(data['payoutId']).update({
                                'paymentStatus': response.status,
                                'payoutResponse': response.result
                            });
                            $("#payoutResponseModal .payout-response").html(JSON.stringify(JSON.parse(JSON.stringify(response.result)), null, 4));
                            $("#payoutResponseModal").modal('show');
                        }
                    }
                });
            }
        });
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
            var url = "{{ url('send-email') }}";
            return await sendEmail(url, emailTemplatesData.subject, emailTemplatesData.message, [user.email]);
        }
        async function getUserData(vendorId) {
            var data = '';
            await database.collection('users').where("vendorID", "==", vendorId).where('role','==','vendor').get().then(async function(snapshotss) {
                if (snapshotss.docs[0]) {
                    data = snapshotss.docs[0].data();
                }
            });
            if (data.id) {
                await database.collection('withdraw_method').where("userId", "==", data.id).get().then(async function(snapshotss) {
                    if (snapshotss.docs.length) {
                        data['withdrawMethod'] = snapshotss.docs[0].data();
                    }
                });
            }
            return data;
        }
        async function getPaymentSettings() {
            var settings = {};
            await database.collection('settings').get().then(async function(snapshots) {
                snapshots.forEach((doc) => {
                    if (doc.id == "flutterWave") {
                        settings["flutterwave"] = doc.data();
                    }
                    if (doc.id == "paypalSettings") {
                        settings["paypal"] = doc.data();
                    }
                    if (doc.id == "razorpaySettings") {
                        settings["razorpay"] = doc.data();
                    }
                    if (doc.id == "stripeSettings") {
                        settings["stripe"] = doc.data();
                    }
                });
            });
            return settings;
        }
        async function getPayoutDetail(payoutId) {
            var snapshot = await database.collection('payouts').doc(payoutId).get();
            return snapshot.data();
        }
        $(document).on("click", "a[name='vendor_reject_request']", function(e) {
            $('#admin_note').val("");
            var id = this.id;
            var auth = $(this).attr('data-auth');
            var amount = $(this).attr('data-amount');
            var price = $(this).attr('data-price');
            $('#submit_cancel').attr('data-id', id).attr('data-amount', amount).attr('data-price', price).attr('data-auth', auth);
        });
        $(document).on("click", "#submit_cancel", async function(e) {
            $(this).prop('disabled', true).css({
                'cursor': 'default',
                'opacity': '0.5'
            });
            var id = $(this).data('id');
            var auth = $(this).data('auth');
            var user = await getUserData(auth);
            var priceadd = $(this).data('price');
            var amount = $(this).data('amount');
            var admin_note = $("#admin_note").val();
            jQuery("#data-table_processing").show();
            database.collection('users').where("vendorID", "==", auth).where('role','==','vendor').get().then(function(resultvendor) {
                if (resultvendor.docs.length) {
                    var vendor = resultvendor.docs[0].data();
                    var wallet_amount = 0;
                    if (isNaN(vendor.wallet_amount) || vendor.wallet_amount == undefined) {
                        wallet_amount = 0;
                    } else {
                        wallet_amount = vendor.wallet_amount;
                    }
                    price = parseFloat(wallet_amount) + parseFloat(priceadd);
                    if (!isNaN(price)) {
                        database.collection('payouts').doc(id).update({
                            'paymentStatus': 'Reject',
                            'adminNote': admin_note
                        }).then(function(result) {
                            database.collection('users').doc(vendor.id).update({
                                'wallet_amount': price
                            }).then(async function(result) {
                                var wId = database.collection('temp').doc().id;
                                database.collection('wallet').doc(wId).set({
                                    'amount': parseFloat(priceadd),
                                    'date': kweekDb.FieldValue.serverTimestamp(),
                                    'id': wId,
                                    'isTopUp': false,
                                    'order_id': id,
                                    'payment_method': 'Wallet',
                                    'payment_status': 'Refund success',
                                    'transactionUser': 'vendor',
                                    'user_id': vendor.id,
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
                    alert('Vendor not found.');
                }
            });
        });
        $(document).on("click", "#submit_accept", async function(e) {
            $(this).prop('disabled', true).css({
                'cursor': 'default',
                'opacity': '0.5'
            });
            var id = $(this).data('id');
            var auth = $(this).data('auth');
            var user = await getUserData(auth);
            var amount = $(this).data('amount');
            jQuery("#data-table_processing").show();
            database.collection('payouts').doc(id).update({
                'paymentStatus': 'Success'
            }).then(async function(result) {
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
        $("#is_active").click(function() {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if (!$('#example24 .is_open:checked').length) {
                alert("{{ trans('lang.select_delete_alert') }}");
                return;
            }
            if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
                return;
            }
            var ids = [];
            $('#example24 .is_open:checked').each(function() {
                ids.push($(this).attr('dataId'));
            });
            $.post('{{ route("payoutRequests.vendor.disbursement.destroy") }}', {
                _token: '{{ csrf_token() }}',
                ids: ids
            }).done(function () {
                table.ajax.reload();
            });
        });
        $(document).on('click', '.btn-delete-payout', function () {
            if (!confirm('{{ trans("lang.delete_alert") }}')) {
                return;
            }
            var payoutId = $(this).attr('id');
            $.post('{{ route("payoutRequests.vendor.disbursement.destroy") }}', {
                _token: '{{ csrf_token() }}',
                id: payoutId
            }).done(function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection
