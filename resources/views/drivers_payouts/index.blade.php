@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                    <h3 class="mb-0 page-title">{{trans('lang.drivers_payout_plural')}}</h3>
                    <span class="counter ml-3 total_count"></span>
                </div>
                
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.drivers_payout_plural')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                
            </div>
        </div> 
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
                <?php if ($id != '') { ?>
                    <div class="menu-tab vendorMenuTab">
                        <ul>
                            <li>
                                <a href="{{route('drivers.view',$id)}}"><i class="ri-list-indefinite"></i>{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li class="vehicle_tab" style="display:none">
                                <a href="{{route('drivers.vehicle',$id)}}"><i class="ri-car-line"></i>{{trans('lang.vehicle')}}</a>
                            </li>
                            <li class="service_type_orders">

                            </li>
                            <li class="active">
                                <a href="{{route('driver.payouts',$id)}}"><i class="ri-bank-card-line"></i>{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li>
                                <a href="{{route('payoutRequests.drivers.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i>{{trans('lang.tab_payout_request')}}</a>
                            </li>
                            <li>
                                <a href="{{route('users.walletstransaction',$id)}}"
                                           class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                             </li>
                        </ul>
                    </div>
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.drivers_payout_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.driver_payouts_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <?php if ($id != '') { ?>
                            <a class="btn-primary btn rounded-full" href="{{ url('driversPayouts/create/'.$id) }}/"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.drivers_payout_create')}}</a>
                        <?php } else { ?>
                            <a class="btn-primary btn rounded-full" href="{!! route('driversPayouts.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.drivers_payout_create')}}</a>
                        <?php } ?>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                          <div class="table-responsive m-t-10">
                          <table id="example24"
                                 class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                 cellspacing="0" width="100%">
                                 <thead>
                                     <tr>
                                     <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                     class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                             <?php if ($id == '') { ?>
                                                 <th>{{ trans('lang.driver')}}</th>
                                             <?php }  ?>
                                                 <th>{{trans('lang.paid_amount')}}</th>

                                                 <th>{{trans('lang.drivers_payout_paid_date')}}</th>
                                                 <th>{{trans('lang.drivers_payout_note')}}</th>
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

<script type="text/javascript">

    var id="{{$id}}";
    var serviceType = getCookie('service_type'); 

    if(id!=''){
        var wallet_route = "{{route('users.walletstransaction','id')}}";
        $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID='+id));
    }

    $(document).ready(function () {
        if(id!=''){
            jQuery("#data-table_processing").show();
            $.get("{{ route('drivers.get-driver', '') }}/" + id, function(res) {
                jQuery("#data-table_processing").hide();
                if (res && res.data) {
                    var driver_data = res.data;
                    var payoutDriver = driver_data.firstName + " " + driver_data.lastName;
                    $('.page-title').html("{{trans('lang.drivers_payout_plural')}}"+" - "+payoutDriver);
                    
                    var url = '';
                    if (driver_data.serviceType == "cab-service") {
                        url = "{{route('drivers.rides','driverId')}}".replace('driverId', driver_data.id);
                    } else if (driver_data.serviceType == "rental-service") {
                        url = "{{route('rental_orders.driver','id')}}".replace("id", driver_data.id);
                    } else if (driver_data.serviceType == "delivery-service" || driver_data.serviceType == "ecommerce-service") {
                        url = "{{route('orders','id')}}".replace("id", 'driverId=' + driver_data.id);
                    } else if (driver_data.serviceType == "parcel_delivery") {
                        url = "{{route('parcel_orders.driver','id')}}".replace("id", driver_data.id);
                    }
                    if (url) {
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{trans('lang.order_plural')}}</a>');
                    }
                }
            });
        }
        if(serviceType !== 'delivery-service' && serviceType !== 'parcel_delivery'){
            $('.vehicle_tab').show();
        }else{
            $('.vehicle_tab').hide();
        }
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

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
        var fieldConfig = {
            columns: [
                { key: 'title', header: "{{ trans('lang.driver')}}" },
                { key: 'amount', header: "{{ trans('lang.total_amount')}}" },
                { key: 'paidDate', header: "{{trans('lang.drivers_payout_paid_date')}}" },
                { key: 'note', header: "{{trans('lang.drivers_payout_note')}}" },
            ],
            fileName: "{{trans('lang.drivers_payout_table')}}",
        };
        const table = $('#example24').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('driversPayouts.datatable') }}",
                data: function(d) {
                    d.driver_id = id;
                },
                dataSrc: function (json) {
                    jQuery("#data-table_processing").hide();
                    $('.total_count').text(json.recordsTotal);
                    return json.data;
                }
            },
            columnDefs: [
                {orderable: false, targets: (id == '') ? [0,5] : [0,4]},
            ],
            order: (id == '') ? [3, "desc"] : [2, "desc"],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            },
            dom: 'lfrtipB',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                    className: 'btn btn-info',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '{{ trans('lang.export_excel') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'excel',fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '{{ trans('lang.export_pdf') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'pdf',fieldConfig);
                            }
                        },   
                        {
                            extend: 'csvHtml5',
                            text: '{{ trans('lang.export_csv') }}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'csv',fieldConfig);
                            }
                        }
                    ]
                }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; 
                }).remove();
            }
        });
    });

    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#example24 .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var ids = [];
                $('#example24 .is_open:checked').each(function () {
                    ids.push($(this).attr('dataId'));
                });
                $.post("{{ route('driversPayouts.bulk-destroy') }}", {
                    _token: "{{ csrf_token() }}",
                    ids: ids
                }, function(res) {
                    window.location.reload();
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='driver_payouts-delete']", function (e) {
        var id = this.id;
        if (confirm("{{trans('lang.selected_delete_alert')}}")) {
            jQuery("#data-table_processing").show();
            $.post("{{ route('driversPayouts.destroy') }}", {
                _token: "{{ csrf_token() }}",
                id: id
            }, function(res) {
                window.location.reload();
            });
        }
    });
</script>

@endsection