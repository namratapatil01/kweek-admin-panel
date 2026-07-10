@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Top Section -->
        <div class="admin-top-section pt-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/currency-icon.png') }}" alt="Currencies" onerror="this.src='{{ asset('images/default_user.png') }}';"></span>
                            <div class="top-title-breadcrumb">
                                <div class="d-flex align-items-center mb-1">
                                    <h3 class="mb-0 restaurantTitle">Currencies List</h3>
                                    <span class="badge badge-light-danger ml-2" id="total_records" style="background-color: #fee2e2; color: #ef4444; border-radius: 20px; font-weight: bold; padding: 5px 12px;">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="d-flex align-items-center">
                                <!-- No global filters based on image -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card border rounded-lg">
            <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0 pt-4 px-4">
                <div class="card-header-title">
                    <h3 class="text-dark mb-1 h5 font-weight-bold">Currencies List</h3>
                    <p class="mb-0 text-muted small">View and manage all the currency</p>
                </div>
                <div>
                    <a class="btn btn-dark text-white rounded-pill px-4 py-2" href="{{ route('settings.currencies.create') }}" style="font-weight: 500;">
                        <i class="mdi mdi-plus mr-1"></i> Create a Currency
                    </a>
                </div>
            </div>
            <div class="card-body px-4">
                <div class="table-responsive">
                    <table id="moduleTable" class="display nowrap table table-hover" width="100%">
                        <thead>
                            <tr style="background-color: #f9f9f9; color: #333;">
                                <th style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;">Country</th>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th>Code</th>
                                <th>Symbol At Right</th>
                                <th>Active</th>
                                <th style="border-top-right-radius: 10px; border-bottom-right-radius: 10px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    var table = $('#moduleTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("settings.currencies.datatable") }}',
            type: 'GET'
        },
        order: [[1, 'asc']], 
        pageLength: 10,
        language: {
            zeroRecords: "{{ trans('lang.no_record_found') }}",
            emptyTable: "{{ trans('lang.no_record_found') }}",
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
        },
        dom: '<"row align-items-center mb-3"<"col-md-3"l><"col-md-9 d-flex justify-content-end align-items-center"f>>rt<"row align-items-center mt-3"<"col-md-6"i><"col-md-6"p>>',
        columnDefs: [
            { orderable: false, targets: [4, 5, 6] },
            { className: 'align-middle', targets: '_all' }
        ]
    });

    // Update total count
    table.on('draw.dt', function () {
        var info = table.page.info();
        $('#total_records').text(info.recordsTotal);
    });

    // Toggle Active Status
    $(document).on('change', '.toggle-status', function () {
        var id = $(this).data('id');
        var isActive = $(this).is(':checked') ? 1 : 0;
        
        $.ajax({
            url: '{{ route("settings.currencies.toggle-status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                isActive: isActive
            },
            success: function(response) {
                if(!response.success) {
                    alert('Failed to update status');
                    table.ajax.reload(null, false);
                }
            },
            error: function() {
                alert('An error occurred');
                table.ajax.reload(null, false);
            }
        });
    });

    // Delete single action
    $(document).on('click', '.delete-row', function () {
        var id = $(this).data('id');
        if (!confirm('{{ trans("lang.delete_alert") }}')) return;

        $.ajax({
            url: '{{ route("settings.currencies.destroy") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', id: id },
            success: function () { table.ajax.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Delete failed'); }
        });
    });

    // Move search and buttons slightly to match UI
    table.on('init.dt', function() {
        var searchInput = $('.dataTables_filter input');
        searchInput.addClass('rounded-pill border-0 px-4 py-2').css({
            'background-color': '#f3f4f6',
            'min-width': '220px',
            'outline': 'none',
            'background-image': 'none' 
        }).attr('placeholder', 'Search...');
        
        $('.dataTables_filter label').contents().filter(function() { return this.nodeType === 3; }).remove();
        
        $('.dataTables_filter i').remove();
        
        var searchIcon = $('<i class="mdi mdi-magnify" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;"></i>');
        $('.dataTables_filter').css('position', 'relative').append(searchIcon);
        
        $('.dataTables_length select').addClass('rounded-pill border-0 px-3 py-1 mx-2').css('background-color', '#f3f4f6');
    });
});
</script>
<style>
/* Switch Toggle Styles */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  margin-bottom: 0;
}
.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ef4444;
  -webkit-transition: .4s;
  transition: .4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
input:checked + .slider {
  background-color: #22c55e;
}
input:focus + .slider {
  box-shadow: 0 0 1px #22c55e;
}
input:checked + .slider:before {
  -webkit-transform: translateX(20px);
  -ms-transform: translateX(20px);
  transform: translateX(20px);
}
.slider.round {
  border-radius: 24px;
}
.slider.round:before {
  border-radius: 50%;
}
</style>
@endsection
