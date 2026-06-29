@extends('layouts.app')



@section('content')


<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.car_model')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.car_model')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.car_model')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.car_model')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.carmodel_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('carModel.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_car_model')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                      
                                    <th>{{trans('lang.car_make')}}</th>
                                    <th>{{trans('lang.name')}}</th>
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



<script type="text/javascript">
    $(document).ready(function () {
        var table = $('#example24').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('carModel.datatable') }}",
                type: "GET",
            },
            order: [[1, "asc"]],
            columnDefs: [
                {orderable: false, targets: [2, 3]},
            ],
            language: {
                zeroRecords: "{{trans('lang.no_record_found')}}",
                emptyTable: "{{trans('lang.no_record_found')}}"
            },
            responsive: true,
            drawCallback: function(settings) {
                $('.total_count').text(settings.json.recordsTotal);
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $(document).on("click", "input[name='isActive']", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            var payload = {
                _token: "{{ csrf_token() }}",
                id: id,
                isActive: ischeck
            };
            $.ajax({
                url: "{{ url('carModel/update') }}/" + id,
                type: "POST",
                data: payload,
                success: function(res) {
                    console.log('Status updated');
                }
            });
        });

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        $(document).on("click", "a[name='car-model-delete']", function (e) {
            var id = this.id;
            if(confirm("{{trans('lang.confirm_delete')}}")) {
                var payload = {
                    _token: "{{ csrf_token() }}",
                    id: id
                };
                $.ajax({
                    url: "{{ route('carModel.delete') }}",
                    type: "POST",
                    data: payload,
                    success: function() {
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>



@endsection

