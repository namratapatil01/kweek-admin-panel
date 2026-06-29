@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ $label }} — {{ trans('lang.view') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.index') }}">{{ $label }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.view') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card border">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">ID: {{ $record->id }}</h4>
                <div>
                    @if(!$readonly)
                        <a href="{{ route($routePrefix . '.edit', $record->id) }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-lead-pencil"></i> {{ trans('lang.edit') }}
                        </a>
                    @endif
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary btn-sm">{{ trans('lang.back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            @foreach($columns as $column)
                                @php $field = $column['field']; $val = data_get($record, $field); @endphp
                                <tr>
                                    <th width="30%">{{ $column['label'] }}</th>
                                    <td>
                                        @if(($column['type'] ?? null) === 'boolean')
                                            {{ filter_var($val, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No' }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if(!empty($record->payload) && is_array($record->payload))
                                @foreach($record->payload as $key => $val)
                                    <tr>
                                        <th>{{ ucfirst($key) }}</th>
                                        <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
