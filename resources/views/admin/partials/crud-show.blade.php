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
                <li class="breadcrumb-item"><a href="{{ route($indexRoute) }}">{{ $label }}</a></li>
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
                    <a href="{{ route($indexRoute) }}" class="btn btn-secondary btn-sm">{{ trans('lang.back') }}</a>
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
                                        @elseif(
                                            ($column['type'] ?? null) === 'image' || 
                                            in_array(strtolower($field), ['photo', 'image', 'coverimage', 'profilepictureurl', 'flag'], true) ||
                                            (is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $val)) ||
                                            (is_string($val) && (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) && (str_contains($val, 'firebasestorage') || str_contains($val, 'storage/')))
                                        )
                                            @if($val)
                                                <img src="{{ $val }}" class="rounded shadow-sm" style="max-width:150px; max-height:150px; object-fit:cover;" onerror="this.onerror=null;this.src='/images/default_user.png'">
                                            @else
                                                <span class="text-muted">No Image</span>
                                            @endif
                                        @else
                                            @if(is_array($val))
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($val as $subKey => $subVal)
                                                        <li><strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong> {{ is_array($subVal) ? json_encode($subVal) : ($subVal === true ? 'Yes' : ($subVal === false ? 'No' : $subVal)) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {{ $val }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if(!empty($record->payload) && is_array($record->payload))
                                @foreach($record->payload as $key => $val)
                                    <tr>
                                        <th>{{ ucfirst($key) }}</th>
                                        <td>
                                            @if(
                                                in_array(strtolower($key), ['photo', 'image', 'coverimage', 'profilepictureurl', 'flag'], true) ||
                                                (is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $val)) ||
                                                (is_string($val) && (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) && (str_contains($val, 'firebasestorage') || str_contains($val, 'storage/')))
                                            )
                                                @if($val)
                                                    <img src="{{ $val }}" class="rounded shadow-sm" style="max-width:150px; max-height:150px; object-fit:cover;" onerror="this.onerror=null;this.src='/images/default_user.png'">
                                                @else
                                                    <span class="text-muted">No Image</span>
                                                @endif
                                            @else
                                                @if(is_array($val))
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($val as $subKey => $subVal)
                                                            <li><strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong> {{ is_array($subVal) ? json_encode($subVal) : ($subVal === true ? 'Yes' : ($subVal === false ? 'No' : $subVal)) }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $val }}
                                                @endif
                                            @endif
                                        </td>
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
