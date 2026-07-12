@extends('layouts.app')

@section('style')
<style>
/* ============================================================
   Review Attributes – Index Page Styles
   ============================================================ */

/* Toggle Switch */
.ra-toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 22px;
    margin: 0;
}
.ra-toggle-switch input { opacity: 0; width: 0; height: 0; }
.ra-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e0;
    border-radius: 22px;
    transition: .3s;
}
.ra-slider:before {
    position: absolute;
    content: "";
    height: 16px; width: 16px;
    left: 3px; bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: .3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.ra-toggle-switch input:checked + .ra-slider { background-color: #20c997; }
.ra-toggle-switch input:checked + .ra-slider:before { transform: translateX(22px); }

/* Custom Table Checkbox styling */
#raTable input[type="checkbox"] {
    position: relative !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    width: 18px !important;
    height: 18px !important;
    border: 1.5px solid #cbd5e0 !important;
    border-radius: 4px !important;
    outline: none !important;
    cursor: pointer !important;
    background-color: #fff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease-in-out !important;
    margin: 0 !important;
    opacity: 1 !important;
}
#raTable input[type="checkbox"]:checked {
    background-color: #ff5c28 !important;
    border-color: #ff5c28 !important;
}
#raTable input[type="checkbox"]:checked::after {
    content: '' !important;
    width: 5px !important;
    height: 9px !important;
    border: solid white !important;
    border-width: 0 2.5px 2.5px 0 !important;
    transform: rotate(45deg) !important;
    margin-bottom: 2px !important;
    display: block !important;
}

/* Circle action buttons */
.action-btn-circle-container {
    display: inline-flex;
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
    font-size: 16px;
    border: 1.5px solid;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}
.btn-circle:hover { transform: scale(1.1); }
.btn-circle-edit {
    color: #00b0ff;
    border-color: #00b0ff;
    background: #fff;
}
.btn-circle-edit:hover { background: #00b0ff; color: #fff; }
.btn-circle-delete {
    color: #ef5350;
    border-color: #ef5350;
    background: #fff;
}
.btn-circle-delete:hover { background: #ef5350; color: #fff; }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.reviewattribute_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.reviewattribute_table') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- Top header bar --}}
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center align-items-center">
                            <span class="icon mr-3" style="font-size: 24px; color: #2b354e;">
                                <i class="mdi mdi-star-circle"></i>
                            </span>
                            <h3 class="mb-0" style="font-weight: 700; color: #2b354e; font-size: 22px;">
                                {{ trans('lang.reviewattribute_plural') }}
                            </h3>
                            <span class="ml-3 total_ra_count badge" style="background: #ff5c28; color: #fff; font-size: 14px; padding: 6px 14px; border-radius: 50px; font-weight: 700;">0</span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <a href="{{ route('reviewattributes.create') }}" class="btn btn-dark"
                               style="background-color: #000; border-color: #000; border-radius: 24px; padding: 10px 24px; font-weight: 700; font-size: 14px; transition: all 0.3s ease;">
                                <i class="mdi mdi-plus mr-1"></i> {{ trans('lang.reviewattribute_create') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <div class="card-header d-flex justify-content-between align-items-center border-0 bg-white"
                             style="border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 20px 24px;">
                            <div class="card-header-title">
                                <h3 class="text-dark mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">
                                    {{ trans('lang.reviewattribute_table') }}
                                </h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">{{ trans('lang.attribute_table_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 0 24px 24px 24px;">
                            <div class="table-responsive">
                                <table id="raTable"
                                       class="display nowrap table table-hover table-striped table-bordered"
                                       cellspacing="0" width="100%"
                                       style="border: 1px solid #eef2f6;">
                                    <thead>
                                        <tr style="background-color: #f8fafc;">
                                            <th class="delete-all" style="width: 80px; vertical-align: middle; padding: 12px 16px;">
                                                <div class="d-flex align-items-center" style="gap: 8px;">
                                                    <input type="checkbox" id="ra_select_all" style="margin: 0;">
                                                    <a id="raDeleteAll" href="javascript:void(0)"
                                                       class="d-inline-flex align-items-center text-danger"
                                                       style="font-weight: 700; font-size: 13.5px; text-decoration: none; gap: 4px;">
                                                        <i class="mdi mdi-delete" style="font-size: 16px;"></i> All
                                                    </a>
                                                </div>
                                            </th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.reviewattribute_name') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.actions') }}</th>
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
<script>
$(document).ready(function () {

    var table = $('#raTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("reviewattributes.datatable") }}',
            type: 'GET',
            data: function (d) {
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function (json) {
                $('.total_ra_count').text(json.recordsTotal);
                return json.data || [];
            }
        },
        columnDefs: [
            { orderable: false, targets: [0, 2] },
            {
                targets: '_all',
                createdCell: function (td) {
                    $(td).css({ 'padding': '12px 16px', 'vertical-align': 'middle' });
                }
            }
        ],
        language: {
            zeroRecords: '{{ trans("lang.no_record_found") }}',
            emptyTable:  '{{ trans("lang.no_record_found") }}',
            processing:  '',
            search: ''
        },
        dom: 'lfrtipB',
        buttons: [
            {
                extend:    'collection',
                text:      '<i class="mdi mdi-cloud-download"></i> {{ trans("lang.export_as") }}',
                className: 'btn btn-outline-secondary btn-sm',
                buttons: [
                    { extend: 'excelHtml5', text: '{{ trans("lang.export_excel") }}' },
                    { extend: 'pdfHtml5',   text: '{{ trans("lang.export_pdf") }}'   },
                    { extend: 'csvHtml5',   text: '{{ trans("lang.export_csv") }}'   },
                ]
            }
        ],
        drawCallback: function () {
            $('.dataTables_paginate .paginate_button').addClass('btn btn-outline-light btn-sm mx-1');
        },
        initComplete: function () {
            $('.dataTables_filter').append($('.dt-buttons').detach());
            $('.dataTables_filter input')
                .attr('placeholder', 'Search...')
                .css({
                    'border': '1px solid #e2e8f0',
                    'border-radius': '20px',
                    'padding': '8px 16px',
                    'outline': 'none',
                    'font-size': '14px',
                    'margin-left': '10px'
                }).val('');
            $('.dataTables_filter label').contents().filter(function () {
                return this.nodeType === 3;
            }).remove();
        }
    });

    // Select all
    $('#ra_select_all').on('change', function () {
        $('#raTable .ra-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Bulk delete
    $('#raDeleteAll').on('click', function () {
        var ids = [];
        $('#raTable .ra-checkbox:checked').each(function () {
            ids.push($(this).data('id'));
        });
        if (!ids.length) {
            alert('{{ trans("lang.select_delete_alert") }}');
            return;
        }
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;

        $.ajax({
            url:    '{{ route("reviewattributes.bulk-destroy") }}',
            method: 'POST',
            data:   { _token: '{{ csrf_token() }}', ids: ids },
            success: function () { table.ajax.reload(); },
            error:   function (xhr) { alert('Error: ' + xhr.responseText); }
        });
    });

    // Single delete
    $(document).on('click', '.ra-delete-btn', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.selected_delete_alert") }}')) return;

        $.ajax({
            url:    '{{ url("reviewattributes") }}/' + id,
            method: 'DELETE',
            data:   { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error:   function (xhr) { alert('Error: ' + xhr.responseText); }
        });
    });

    // Toggle active status
    $(document).on('change', '.ra-toggle-enabled', function () {
        var id  = $(this).data('id');
        var chk = $(this);
        $.ajax({
            url:    '{{ url("reviewattributes/toggle") }}/' + id,
            method: 'POST',
            data:   { _token: '{{ csrf_token() }}' },
            success: function (res) {
                chk.prop('checked', res.isActive);
            },
            error: function () {
                chk.prop('checked', !chk.prop('checked'));
                alert('Failed to update status.');
            }
        });
    });

});
</script>
@endsection
