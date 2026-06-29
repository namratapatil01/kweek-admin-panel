@php
    $isEdit = isset($record);
    $action = $isEdit
        ? route($routePrefix . '.update', $record->id)
        : route($routePrefix . '.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ $isEdit ? trans('lang.edit') : trans('lang.create') }} {{ $label }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.index') }}">{{ $label }}</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? trans('lang.edit') : trans('lang.create') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border">
            <div class="card-body">
                <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="row">
                        @foreach($formFields as $field)
                            @php
                                $name = $field['name'];
                                $type = $field['type'] ?? 'text';
                                $value = old($name, $isEdit ? data_get($record, $name) : null);
                            @endphp
                            <div class="col-md-6 mb-3">
                                <label class="control-label">{{ $field['label'] }}</label>

                                @if($type === 'textarea')
                                    <textarea name="{{ $name }}" class="form-control" rows="4">{{ $value }}</textarea>
                                @elseif($type === 'checkbox')
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="{{ $name }}" value="0">
                                        <input type="checkbox" name="{{ $name }}" value="1" class="form-check-input" id="field_{{ $name }}"
                                            {{ filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="field_{{ $name }}">{{ $field['label'] }}</label>
                                    </div>
                                @elseif($type === 'select')
                                    <select name="{{ $name }}" class="form-control">
                                        <option value="">-- Select --</option>
                                        @foreach($field['options'] ?? [] as $optVal => $optLabel)
                                            <option value="{{ $optVal }}" {{ (string)$value === (string)$optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'json')
                                    <textarea name="{{ $name }}" class="form-control" rows="4">{{ is_array($value) ? json_encode($value) : $value }}</textarea>
                                @else
                                    <input type="{{ $type }}" name="{{ $name }}" class="form-control" value="{{ $value }}">
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">{{ trans('lang.save') }}</button>
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">{{ trans('lang.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
