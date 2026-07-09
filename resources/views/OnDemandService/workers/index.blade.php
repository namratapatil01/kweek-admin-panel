@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.ondemand_plural')}} - {{trans('lang.worker_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.worker_plural')}}</li>
            </ol>
        </div>
        <div>
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
                                <li ><a href="{{route('providers.view', $id)}}"><img src="{{ asset('images/provider.png') }}"> {{trans('lang.tab_basic')}}</a>
                                </li>
                                <li><a href="{{route('ondemand.services.index', $id)}}"><img src="{{ asset('images/service.png') }}"> {{trans('lang.services')}}</a></li>
                                <li>
                                <li class="active"><a href="{{route('ondemand.workers.index', $id)}}"><img src="{{ asset('images/worker.png') }}"> {{trans('lang.workers')}}</a></li>
                                <li>
                                <li><a href="{{route('ondemand.bookings.index',$id)}}"><img src="{{ asset('images/booking.png') }}"> {{trans('lang.booking_plural')}}</a></li>
                                <li>
                                <li><a href="{{route('ondemand.coupons', $id)}}"><img src="{{ asset('images/coupon.png') }}"> {{trans('lang.coupon_plural')}}</a></li>
                                 <li>
                                    <a href="{{route('providerPayouts.payout', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('payoutRequests.providers', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('users.walletstransaction',$id)}}"
                                           class="wallet_transaction"><img src="{{ asset('images/wallet.png') }}">  {{trans('lang.wallet_transaction')}}</a>
                                </li>
                                <?php 
                                    $subscription =  route("subscription.subscriptionPlanHistory", ":id");
                                    $subscription =  str_replace(":id", "providerID=" . $id, $subscription);
                                    ?>
                                <li> 
                                    <a href="{{ $subscription }}"><img src="{{ asset('images/subscription.png') }}"> {{trans('lang.subscription_history')}}</a>
                                </li>
                            </ul>
                        </div>

                    </div>
                @endif
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/worker.png') }}"></span>
                        <h3 class="mb-0 PageTitle">{{trans('lang.worker_plural')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="">{{trans("lang.status")}}</option>
                                        <option value="active"  >{{trans("lang.active")}}</option>
                                        <option value="inactive"  >{{trans("lang.in_active")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                        <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.worker_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.worker_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                    @if($id=='')
                        <a class="btn-primary btn rounded-full" href="{!! route('ondemand.workers.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.worker_create')}}</a>
                    @else
                    <a class="btn-primary btn rounded-full" href="{!! route('ondemand.workers.create','id='.$id) !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.worker_create')}}</a>
                    @endif
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="workerTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php
                                    $__workerPerms = session('user_permissions', []);
                                    if (is_string($__workerPerms)) {
                                        $__workerPerms = json_decode($__workerPerms, true) ?: [];
                                    }
                                    if (!is_array($__workerPerms)) {
                                        $__workerPerms = [];
                                    }
                                ?>
                                <?php if (in_array('ondemand.workers.delete', $__workerPerms)) { ?>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"
                                            ><a id="deleteAll" class="do_not_delete"
                                                href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                            <?php }?>
                                        <th>{{trans('lang.worker_info')}}</th>
                                        <th>{{trans('lang.email')}}</th>
                                        <th>{{trans('lang.salary')}}</th>
                                        <th>{{trans('lang.provider')}}</th>
                                        <th>{{trans('lang.onoff')}}</th>
                                        <th>{{trans('lang.status')}}</th>
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

@endsection


@section('scripts')
<script>
    var user_permissions = [];
    try {
        user_permissions = JSON.parse(@json(session('user_permissions') ?: '[]'));
        if (typeof user_permissions === 'string') {
            user_permissions = JSON.parse(user_permissions);
        }
    } catch (e) {
        user_permissions = [];
    }
    if (!Array.isArray(user_permissions)) {
        user_permissions = [];
    }
    var checkDeletePermission = $.inArray('ondemand.workers.delete', user_permissions) >= 0;
    var id = @json((string) ($id ?? ''));
    var selectRangeLabel = @json(trans('lang.select_range'));

    $('.status_selector').select2({
        placeholder: @json(trans('lang.status')),
        minimumResultsForSearch: Infinity,
        allowClear: true
    });
    $('select').on("select2:unselecting", function () {
        var self = $(this);
        setTimeout(function () {
            self.select2('close');
        }, 0);
    });

    function setDate() {
        $('#daterange span').html(selectRangeLabel);
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function () {
            $('#daterange span').html(selectRangeLabel);
            $('.filteredRecords').trigger('change');
        });
    }
    setDate();

    $('.filteredRecords').change(function () {
        if ($.fn.DataTable.isDataTable('#workerTable')) {
            $('#workerTable').DataTable().ajax.reload();
        }
    });

    if (id !== '') {
        $(".wallet_transaction").attr("href", "/walletstransaction/" + id);
        $('.tabDiv').show();
    } else {
        $('.tabDiv').hide();
    }

    $(document).ready(function () {
        $('body').tooltip({ selector: '[data-toggle="tooltip"]' });

        var orderCol = checkDeletePermission ? 1 : 0;

        $('#workerTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "/ondemand-workers-data",
                data: function (d) {
                    d.provider_id = id;
                    d.status = $('.status_selector').val();
                    var daterangepicker = $('#daterange').data('daterangepicker');
                    if ($('#daterange span').html() !== selectRangeLabel && daterangepicker) {
                        d.from_date = daterangepicker.startDate.format('YYYY-MM-DD');
                        d.to_date = daterangepicker.endDate.format('YYYY-MM-DD');
                    }
                },
                dataSrc: function (json) {
                    $('.total_count').text((json && json.recordsFiltered) ? json.recordsFiltered : 0);
                    return (json && json.data) ? json.data : [];
                },
                error: function (xhr, error, thrown) {
                    console.error('Workers datatable error:', error, thrown, xhr && xhr.responseText);
                    $('.total_count').text(0);
                }
            },
            order: [[orderCol, 'desc']],
            columnDefs: [{
                orderable: false,
                targets: checkDeletePermission ? [0, 5, 6, 7] : [4, 5, 6]
            }],
            language: {
                zeroRecords: @json(trans('lang.no_record_found')),
                emptyTable: @json(trans('lang.no_record_found')),
                processing: "Processing..."
            },
            dom: 'lfrtipB',
            buttons: [{
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i> ' + @json(trans('lang.export_as')),
                className: 'btn btn-info',
                buttons: [
                    { extend: 'excelHtml5', text: @json(trans('lang.export_excel')) },
                    { extend: 'pdfHtml5', text: @json(trans('lang.export_pdf')) },
                    { extend: 'csvHtml5', text: @json(trans('lang.export_csv')) }
                ]
            }],
            initComplete: function () {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
                $('.dataTables_filter label').contents().filter(function () {
                    return this.nodeType === 3;
                }).remove();
            }
        });
    });

    $(document).on('click', "input[name='isActive']", function () {
        $.post("/ondemand-workers/toggle-status", {
            _token: "{{ csrf_token() }}",
            id: this.id,
            value: $(this).is(':checked')
        });
    });

    $("#is_active").click(function () {
        $("#workerTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if (!$('#workerTable .is_open:checked').length) {
            alert(@json(trans('lang.select_delete_alert')));
            return;
        }
        if (!confirm(@json(trans('lang.selected_delete_alert')))) {
            return;
        }
        jQuery("#data-table_processing").show();
        var ids = [];
        $('#workerTable .is_open:checked').each(function () {
            ids.push($(this).attr('dataId'));
        });
        $.post("/ondemand-workers/bulk-delete", {
            _token: "{{ csrf_token() }}",
            ids: ids
        }).always(function () {
            jQuery("#data-table_processing").hide();
            $('#workerTable').DataTable().ajax.reload();
        });
    });

    $(document).on('click', "a[name='worker-delete']", function () {
        if (!confirm(@json(trans('lang.delete_alert')))) {
            return;
        }
        var workerId = this.id;
        jQuery("#data-table_processing").show();
        $.post("/ondemand-workers/delete", {
            _token: "{{ csrf_token() }}",
            id: workerId
        }).always(function () {
            jQuery("#data-table_processing").hide();
            $('#workerTable').DataTable().ajax.reload();
        });
    });
</script>
@endsection
