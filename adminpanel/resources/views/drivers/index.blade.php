@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">
                    @if (request()->is('drivers/approved'))
                        @php $type = 'approved'; @endphp
                        {{ trans('lang.approved_drivers') }}
                    @elseif(request()->is('drivers/pending'))
                        @php $type = 'pending'; @endphp
                        {{ trans('lang.approval_pending_drivers') }}
                    @else
                        @php $type = 'all'; @endphp
                        {{ trans('lang.all_drivers') }}
                    @endif
                </h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.driver_table') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/driver.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.driver_plural') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="" selected>{{ trans('lang.status') }}</option>
                                        <option value="active">{{ trans('lang.active') }}</option>
                                        <option value="inactive">{{ trans('lang.in_active') }}</option>
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
                <div class="table-list">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center border-0">
                                    <div class="card-header-title">
                                        <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.driver_table') }}</h3>
                                        <p class="mb-0 text-dark-2">{{ trans('lang.driver_table_text') }}</p>
                                    </div>
                                    <div class="card-header-right d-flex align-items-center">
                                        <div class="card-header-btn mr-3">
                                            <a class="btn-primary btn rounded-full" href="{!! route('drivers.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.drivers_create') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive m-t-10">
                                        <table id="driverTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <?php if (($type == "approved" && in_array('approve.driver.delete', json_decode(@session('user_permissions'), true))) || ($type == "pending" && in_array('pending.driver.delete', json_decode(@session('user_permissions'), true))) || ($type == "all" && in_array('drivers.delete', json_decode(@session('user_permissions'), true)))) { ?>
                                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                    <?php } ?>
                                                    <th>{{ trans('lang.actions') }}</th>
                                                    <th>{{ trans('lang.driver_info') }}</th>
                                                    <th>{{ trans('lang.active') }}</th>
                                                    <th>{{ trans('lang.driver_online') }}</th>
                                                    <th>{{ trans('lang.date') }}</th>
                                                    <th>{{ trans('lang.total_orders') }}</th>
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

            var section_id = getCookie('section_id') || '';
            var type = "{{ $type }}";
            var sectionType = getCookie('service_type') || '';        
            var user_permissions = '<?php echo @session('user_permissions'); ?>';
            user_permissions = JSON.parse(user_permissions);
            var checkDeletePermission = false;

            if (
                (type == 'pending' && $.inArray('pending.driver.delete', user_permissions) >= 0) ||
                (type == 'approved' && $.inArray('approve.driver.delete', user_permissions) >= 0) ||
                (type == 'all' && $.inArray('drivers.delete', user_permissions) >= 0)
            ) {
                checkDeletePermission = true;
            }

            $('.status_selector').select2({
                placeholder: '{{ trans('lang.status') }}',
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            
            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });

            function setDate() {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
                $('#daterange').daterangepicker({
                    autoUpdateInput: false,
                }, function(start, end) {
                    $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    $('.filteredRecords').trigger('change');
                });
                $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                    $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                    $('.filteredRecords').trigger('change');
                });
                $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                    $('#daterange span').html('{{ trans('lang.select_range') }}');
                    $('.filteredRecords').trigger('change');
                });
            }

            setDate();
            $('.filteredRecords').change(function() {
                $('#driverTable').DataTable().ajax.reload();
            });

            var placeholderImage = '';

            $(document).ready(function() {
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

                const table = $('#driverTable').DataTable({
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('drivers.datatable') }}",
                        data: function (d) {
                            d.type = type;
                            d.status = $('.status_selector').val();
                            d.sectionId = section_id;
                            d.serviceType = sectionType;
                            
                            var daterangepicker = $('#daterange').data('daterangepicker');
                            if ($('#daterange span').html() != '{{ trans('lang.select_range') }}' && daterangepicker) {
                                d.fromDate = daterangepicker.startDate.format('YYYY-MM-DD');
                                d.toDate = daterangepicker.endDate.format('YYYY-MM-DD');
                            }
                        }
                    },
                    columnDefs: [
                        { orderable: false, targets: checkDeletePermission ? [0, 1, 3, 4] : [0, 2, 3] }
                    ],
                    order: checkDeletePermission ? [5, 'desc'] : [4, 'desc'],
                    language: {
                        "zeroRecords": "{{ trans('lang.no_record_found') }}",
                        "emptyTable": "{{ trans('lang.no_record_found') }}",
                        "processing": "Processing..."
                    },
                    dom: 'lfrtipB',
                    buttons: [],
                    initComplete: function() {
                        $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
                        $('.dataTables_filter label').contents().filter(function() {
                            return this.nodeType === 3;
                        }).remove();
                    }
                });
            });

            $(document).on("click", "input[name='isOnline']", function(e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;
                $.post("{{ route('drivers.toggle-status') }}", {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: 'isActive',
                    value: ischeck
                });
            });

            $(document).on("click", "input[name='isActive']", function(e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;
                $.post("{{ route('drivers.toggle-status') }}", {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: 'active',
                    value: ischeck
                });
            });

            $("#is_active").click(function() {
                $("#driverTable .is_open").prop('checked', $(this).prop('checked'));
            });

            $("#deleteAll").click(function() {
                if ($('#driverTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        var ids = [];
                        $('#driverTable .is_open:checked').each(function() {
                            ids.push($(this).attr('dataId'));
                        });
                        $.post("{{ route('drivers.bulk-destroy') }}", {
                            _token: "{{ csrf_token() }}",
                            ids: ids
                        }, function(response) {
                            jQuery("#data-table_processing").hide();
                            $('#driverTable').DataTable().ajax.reload();
                        }).fail(function(err) {
                            jQuery("#data-table_processing").hide();
                            alert("Error bulk deleting drivers");
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });

            $(document).on("click", "a[name='driver-delete']", function(e) {
                var id = this.id;
                if (confirm("{{ trans('lang.delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $.post("{{ route('drivers.destroy') }}", {
                        _token: "{{ csrf_token() }}",
                        id: id
                    }, function(response) {
                        jQuery("#data-table_processing").hide();
                        $('#driverTable').DataTable().ajax.reload();
                    }).fail(function(err) {
                        jQuery("#data-table_processing").hide();
                        alert("Error deleting driver");
                    });
                }
            });

        </script>

            var rows = document.getElementsByTagName("table")[0].rows;
        </script>
    @endsection