@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.vehicle_type')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vehicle_type')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.vehicle_type')}}</h3>
                        <span class="counter ml-3 total_count"></span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.vehicle_type')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.vehicle_type_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('vehicleType.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_vehicle_type')}}</a>
                     </div>
                   </div>            
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                    <th>{{trans('lang.vehicle_info')}}</th>
                                    <th>{{trans('lang.status')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
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

    $(document).ready(function () {
        var table = $('#example24').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('vehicleType.datatable') }}",
                type: "GET",
                data: function (d) {
                    d.section_id = section_id;
                }
            },
            order: [[0, "asc"]],
            columnDefs: [
                {orderable: false, targets: [1, 2]},
            ],
            language: {
                zeroRecords: "{{trans('lang.no_record_found')}}",
                emptyTable: "{{trans('lang.no_record_found')}}"
            },
            responsive: true,
            drawCallback: function (settings) {
                $('.total_count').text(settings.json.recordsFiltered);
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $(document).on("click", "input[name='isSwtich']", function () {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            $.ajax({
                url: "{{ url('vehicleType/update') }}/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    isActive: ischeck
                }
            });
        });

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('href');
            if (url) {
                window.location.href = url;
            }
        });

        $(document).on("click", "a[name='vehicleType-delete']", function () {
            if (!confirm("{{ trans('lang.delete_alert') }}")) {
                return;
            }

            var id = this.id;
            $.ajax({
                url: "{{ route('vehicleType.delete') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function () {
                    table.ajax.reload();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Delete failed');
                }
            });
        });
    });
</script>
@endsection
