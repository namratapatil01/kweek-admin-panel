@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ $label }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ $label }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div>
                    <h3 class="text-dark-2 mb-2 h4">{{ $label }}</h3>
                    <p class="mb-0 text-dark-2">{{ trans('lang.manage_records') }}</p>
                </div>
                @if(!$readonly)
                <a class="btn btn-primary rounded-full" href="{{ route($routePrefix . '.create') }}">
                    <i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create') }}
                </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="moduleTable" class="display nowrap table table-hover table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>{{ trans('lang.actions') }}</th>
                                @foreach($columns as $column)
                                    <th>{{ $column['label'] }}</th>
                                @endforeach
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
@include('admin.partials.crud-scripts', ['mode' => 'index'])
@endsection
