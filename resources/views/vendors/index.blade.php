@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">
                @if ($type === 'approved')
                    {{ trans('lang.approved_vendors') }}
                @elseif ($type === 'pending')
                    {{ trans('lang.approval_pending_vendors') }}
                @else
                    {{ trans('lang.all_vendors') }}
                @endif
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.vendor_list') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/vendor.png') }}" alt=""></span>
                            <h3 class="mb-0">{{ trans('lang.vendor_list') }}</h3>
                            <span class="counter ml-3 total_count">0</span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                                <select class="form-control status_selector filteredRecords">
                                    <option value="">{{ trans('lang.status') }}</option>
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
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_list') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.vendor_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{{ route('vendors.create') }}">
                                        <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.createe_vendor') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="userTable" class="display table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            @php
                                                $permissions = json_decode(@session('user_permissions'), true) ?: [];
                                                $checkDelete = ($type === 'approved' && in_array('approve.vendors.delete', $permissions))
                                                    || ($type === 'pending' && in_array('pending.vendors.delete', $permissions))
                                                    || ($type === 'all' && in_array('vendors.delete', $permissions));
                                            @endphp
                                            @if ($checkDelete)
                                                <th class="delete-all">
                                                    <input type="checkbox" id="is_active">
                                                    <label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                            <i class="mdi mdi-delete"></i> {{ trans('lang.all') }}
                                                        </a>
                                                    </label>
                                                </th>
                                            @endif
                                            <th>{{ trans('lang.vendor_info') }}</th>
                                            <th>{{ trans('lang.store') }}</th>
                                            <th>{{ trans('lang.contact_info') }}</th>
                                            <th>{{ trans('lang.current_plan') }}</th>
                                            <th>{{ trans('lang.expiry_date') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
                                            <th>{{ trans('lang.active') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
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
<script type="text/javascript">
    var section_id = getCookie('section_id') || '';
    var type = "{{ $type }}";
    var user_permissions = @json(json_decode(@session('user_permissions') ?: '[]', true));
    var checkDeletePermission = false;

    if (
        (type === 'pending' && user_permissions.indexOf('pending.vendors.delete') >= 0) ||
        (type === 'approved' && user_permissions.indexOf('approve.vendors.delete') >= 0) ||
        (type === 'all' && user_permissions.indexOf('vendors.delete') >= 0)
    ) {
        checkDeletePermission = true;
    }

    $('.status_selector').select2({
        placeholder: '{{ trans('lang.status') }}',
        minimumResultsForSearch: Infinity,
        allowClear: true
    });

    $('select').on('select2:unselecting', function () {
        var self = $(this);
        setTimeout(function () { self.select2('close'); }, 0);
    });

    function setDate() {
        $('#daterange span').html('{{ trans('lang.select_range') }}');
        $('#daterange').daterangepicker({ autoUpdateInput: false }, function (start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function () {
            $('#daterange span').html('{{ trans('lang.select_range') }}');
            $('.filteredRecords').trigger('change');
        });
    }
    setDate();

    $('.filteredRecords').change(function () {
        $('#userTable').DataTable().ajax.reload();
    });

    $(document).ready(function () {
        $('body').tooltip({ selector: '[data-toggle="tooltip"]' });

        var orderCol = checkDeletePermission ? 6 : 5;

        const table = $('#userTable').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            responsive: false,
            ajax: {
                url: "{{ route('vendors.datatable') }}",
                data: function (d) {
                    d.type = type;
                    d.section_id = section_id;
                    d.status = $('.status_selector').val();
                    var daterangepicker = $('#daterange').data('daterangepicker');
                    if ($('#daterange span').html() !== '{{ trans('lang.select_range') }}' && daterangepicker) {
                        d.from_date = daterangepicker.startDate.format('YYYY-MM-DD');
                        d.to_date = daterangepicker.endDate.format('YYYY-MM-DD');
                    }
                },
                dataSrc: function (json) {
                    $('.total_count').text(json.recordsFiltered || 0);
                    return json.data;
                }
            },
            order: [[orderCol, 'desc']],
            columnDefs: [{
                orderable: false,
                targets: checkDeletePermission ? [0, 7, 8] : [6, 7]
            }],
            language: {
                zeroRecords: "{{ trans('lang.no_record_found') }}",
                emptyTable: "{{ trans('lang.no_record_found') }}",
                processing: "Processing..."
            },
            dom: 'lfrtipB',
            buttons: [{
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                className: 'btn btn-info',
                buttons: [
                    { extend: 'excelHtml5', text: '{{ trans('lang.export_excel') }}' },
                    { extend: 'pdfHtml5', text: '{{ trans('lang.export_pdf') }}' },
                    { extend: 'csvHtml5', text: '{{ trans('lang.export_csv') }}' }
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
        $.post("{{ route('vendors.toggle-status') }}", {
            _token: "{{ csrf_token() }}",
            id: this.id,
            value: $(this).is(':checked')
        });
    });

    $("#is_active").click(function () {
        $("#userTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if (!$('#userTable .is_open:checked').length) {
            alert("{{ trans('lang.select_delete_alert') }}");
            return;
        }
        if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
            return;
        }
        jQuery("#data-table_processing").show();
        var items = [];
        $('#userTable .is_open:checked').each(function () {
            items.push({ id: $(this).attr('dataId'), vendorId: $(this).attr('data-vendorid') || null });
        });
        $.post("{{ route('vendors.bulk-destroy') }}", {
            _token: "{{ csrf_token() }}",
            items: items
        }).always(function () {
            jQuery("#data-table_processing").hide();
            $('#userTable').DataTable().ajax.reload();
        });
    });

    $(document).on('click', "a[name='user-delete']", function () {
        if (!confirm("{{ trans('lang.delete_alert') }}")) {
            return;
        }
        var id = this.id;
        var vendorId = $(this).attr('data-vendorid') || null;
        jQuery("#data-table_processing").show();
        $.post("{{ route('vendors.destroy') }}", {
            _token: "{{ csrf_token() }}",
            id: id,
            vendorId: vendorId
        }).always(function () {
            jQuery("#data-table_processing").hide();
            $('#userTable').DataTable().ajax.reload();
        });
    });
</script>
<style>
    .delete-all {
        min-width: 80px !important;
        white-space: nowrap !important;
    }
    .delete-all label {
        display: inline-flex !important;
        align-items: center !important;
        margin: 0 !important;
        padding: 0 !important;
        width: auto !important;
        max-width: none !important;
        flex: none !important;
    }
    .action-btn-circle-container {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: transparent;
        transition: all 0.2s ease;
        text-decoration: none !important;
        font-size: 14px;
    }
    .btn-circle-document {
        border: 1px solid #3b82f6;
        color: #3b82f6 !important;
    }
    .btn-circle-document:hover {
        background-color: #3b82f6;
        color: #fff !important;
    }
    .btn-circle-subscription {
        border: 1px solid #a855f7;
        color: #a855f7 !important;
    }
    .btn-circle-subscription:hover {
        background-color: #a855f7;
        color: #fff !important;
    }
    .btn-circle-edit {
        border: 1px solid #06b6d4;
        color: #06b6d4 !important;
    }
    .btn-circle-edit:hover {
        background-color: #06b6d4;
        color: #fff !important;
    }
    .btn-circle-delete {
        border: 1px solid #ef4444;
        color: #ef4444 !important;
    }
    .btn-circle-delete:hover {
        background-color: #ef4444;
        color: #fff !important;
    }
</style>
@endsection
