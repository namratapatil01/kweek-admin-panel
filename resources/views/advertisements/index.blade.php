@extends('layouts.app')

@section('style')
<style>
.btn-ad-action {
    transition: all 0.2s ease-in-out;
}
.btn-ad-action:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.btn-chat-ad:hover {
    background-color: #00c08b !important;
    color: #fff !important;
}
.btn-view-ad:hover {
    background-color: #ab2efd !important;
    color: #fff !important;
}
.btn-edit-ad:hover {
    background-color: #00b0ff !important;
    color: #fff !important;
}
.btn-delete-ad:hover {
    background-color: #ef5350 !important;
    color: #fff !important;
}
.btn-copy-ad:hover {
    background-color: #10b981 !important;
    color: #fff !important;
}
.btn-toggle-pause:hover {
    background-color: #000000 !important;
    color: #fff !important;
}
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.advertisement_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.advertisement_plural') }}</li>
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

        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center align-items-center">
                            <span class="icon mr-3" style="font-size: 24px; color: #2b354e;"><i class="fa fa-list-ul"></i></span>
                            <h3 class="mb-0" style="font-weight: 700; color: #2b354e; font-size: 22px;">{{ trans('lang.advertisement_plural') }}</h3>
                            <span class="counter ml-3 total_ad_count badge" style="background: #ffefeb; color: #ff3b30; font-size: 14px; padding: 6px 14px; border-radius: 50px; font-weight: 700;">{{ $totalAds ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <div class="card-header d-flex justify-content-between align-items-center border-0 bg-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px; padding: 20px 24px;">
                            <div class="card-header-title">
                                <h3 class="text-dark mb-1" style="font-weight: 700; color: #2b354e; font-size: 18px;">Advertisement List</h3>
                                <p class="mb-0 text-muted" style="font-size: 13px;">View and manage all the advertisements</p>
                            </div>
                            <div class="card-header-right">
                                <a href="{{ route('advertisements.create') }}" class="btn btn-dark" style="background-color: #000; border-color: #000; border-radius: 24px; padding: 10px 24px; font-weight: 700; font-size: 14px; transition: all 0.3s ease;">
                                    <i class="fa fa-plus mr-1"></i> Create Advertisement
                                </a>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 0 24px 24px 24px;">
                            <div class="table-responsive">
                                <table id="adTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%" style="border: 1px solid #eef2f6;">
                                    <thead>
                                        <tr style="background-color: #f8fafc;">
                                            <th class="delete-all" style="width: 90px; vertical-align: middle; padding: 12px 16px;">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" id="select_all_ads" class="mr-2">
                                                    <label for="select_all_ads" class="mr-2" style="margin-bottom: 0;"></label>
                                                    <a id="deleteAll" class="do_not_delete d-inline-flex align-items-center text-danger" href="javascript:void(0)" style="font-weight: 600; font-size: 13px; text-decoration: none;">
                                                        <i class="fa fa-trash mr-1"></i> All
                                                    </a>
                                                </div>
                                            </th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.ads_title') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.store_info') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.ads_type') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.duration') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.status') }}</th>
                                            <th style="padding: 12px 16px; font-weight: 600; color: #4a5568;">{{ trans('lang.priority') }}</th>
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

<!-- Pause Confirmation Modal -->
<div class="modal fade" id="pauseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
        <div class="modal-content" style="border-radius: 12px; border: none; padding: 24px;">
            <div class="modal-header border-0 p-0 justify-content-end">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; padding: 0; margin-top: -15px; margin-right: -5px; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-0">
                <div class="mb-3">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background-color: #fff2f2; color: #ff3b30; font-size: 32px;">
                        <i class="fa fa-pause"></i>
                    </span>
                </div>
                <h4 class="modal-title font-weight-bold mb-2" style="color: #2b354e; font-size: 18px;">Are you sure you want to pause the request?</h4>
                <p class="text-muted mb-4" style="font-size: 14px; line-height: 1.5;">This ad will be pause and not show in the user app & website</p>
                
                <div class="form-group mb-4">
                    <input type="text" class="form-control text-center" id="pause_note" placeholder="Your note here" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 12px; font-size: 14px; height: auto;">
                </div>
                
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn mr-3" id="confirmPauseBtn" style="background-color: #000; color: #fff; border-radius: 8px; padding: 10px 32px; font-weight: 700; border: none; min-width: 110px;">OK</button>
                    <button type="button" class="btn" data-dismiss="modal" style="background-color: #cbd5e0; color: #4a5568; border-radius: 8px; padding: 10px 32px; font-weight: 700; border: none; min-width: 110px;">Back</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
.table-responsive {
    overflow-x: auto;
}
#adTable {
    width: 100% !important;
}
#adTable th, #adTable td {
    vertical-align: middle;
}
#adTable_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}
#adTable_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 6px 16px;
    margin-left: 8px;
    outline: none;
}
#adTable_wrapper .dataTables_length select {
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 4px 8px;
}
</style>
<script>
$(document).ready(function () {
    var vendorId = @json($vendorId ?? '');
    var currentAdIdForPause = null;

    var table = $('#adTable').DataTable({
        pageLength: 10,
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: @json(route('advertisements.datatable')),
            type: 'GET',
            data: function (d) {
                d._token = '{{ csrf_token() }}';
                if (vendorId) {
                    d.vendor_id = vendorId;
                }
            },
            dataSrc: function (json) {
                if (json.error) {
                    console.error('Advertisements datatable error', json.error);
                }
                if (typeof json.recordsTotal !== 'undefined') {
                    $('.total_ad_count').text(json.recordsTotal);
                }
                return json.data || [];
            },
            error: function (xhr) {
                console.error('Advertisements datatable error', xhr.status, xhr.responseText);
            }
        },
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [0, 7] }],
        language: {
            zeroRecords: '{{ trans("lang.no_record_found") }}',
            emptyTable: '{{ trans("lang.no_record_found") }}',
            processing: ''
        }
    });

    $('#select_all_ads').on('change', function () {
        $('#adTable .ad-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '.btn-delete-ad', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;
        $.post('{{ route("advertisements.destroy") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }).done(function () {
            table.ajax.reload();
        }).fail(function () {
            alert('Delete failed');
        });
    });

    $('#deleteAll').on('click', function () {
        var ids = [];
        $('#adTable .ad-checkbox:checked').each(function () {
            ids.push($(this).data('id'));
        });
        if (ids.length === 0) {
            alert('Please select advertisements to delete.');
            return;
        }
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;
        $.post('{{ route("advertisements.destroy") }}', {
            _token: '{{ csrf_token() }}',
            ids: ids
        }).done(function () {
            table.ajax.reload();
            $('#select_all_ads').prop('checked', false);
        }).fail(function () {
            alert('Delete failed');
        });
    });

    // Handle Play/Pause toggle click
    $(document).on('click', '.btn-toggle-pause', function () {
        var id = $(this).data('id');
        var isPaused = $(this).data('paused'); // 1 if paused, 0 if running
        
        if (isPaused == 1) {
            if (confirm('Are you sure you want to resume this advertisement?')) {
                $.post('{{ route("advertisements.toggle-pause") }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    isPaused: 0
                }).done(function () {
                    table.ajax.reload();
                }).fail(function () {
                    alert('Failed to resume ad.');
                });
            }
        } else {
            currentAdIdForPause = id;
            $('#pause_note').val('');
            $('#pauseModal').modal('show');
        }
    });

    // Confirm pause
    $('#confirmPauseBtn').on('click', function () {
        if (!currentAdIdForPause) return;
        var note = $('#pause_note').val().trim();
        
        $.post('{{ route("advertisements.toggle-pause") }}', {
            _token: '{{ csrf_token() }}',
            id: currentAdIdForPause,
            isPaused: 1,
            note: note
        }).done(function () {
            $('#pauseModal').modal('hide');
            table.ajax.reload();
        }).fail(function () {
            alert('Failed to pause ad.');
        });
    });

    // Copy / Duplicate advertisement
    $(document).on('click', '.btn-copy-ad', function () {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to duplicate this advertisement?')) return;
        $.post('{{ route("advertisements.copy") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }).done(function () {
            table.ajax.reload();
        }).fail(function () {
            alert('Copy failed');
        });
    });
});
</script>
@endsection
