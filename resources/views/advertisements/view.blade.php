@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.advertisement_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('advertisements') }}">{{ trans('lang.advertisement_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.view') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card border">
            <div class="card-body">
                <h4 class="mb-3">{{ $ad->title }}</h4>
                <p><strong>Status:</strong> {{ $payload['status'] ?? '—' }}</p>
                <p><strong>Type:</strong> {{ $payload['type'] ?? '—' }}</p>
                <p><strong>Priority:</strong> {{ $payload['priority'] ?? '—' }}</p>
                <p><strong>Description:</strong> {{ $payload['description'] ?? '—' }}</p>
                <p><strong>Duration:</strong>
                    {{ !empty($payload['startDate']) ? \Carbon\Carbon::parse($payload['startDate'])->format('F j, Y') : '—' }}
                    -
                    {{ !empty($payload['endDate']) ? \Carbon\Carbon::parse($payload['endDate'])->format('F j, Y') : '—' }}
                </p>
                @if(!empty($payload['coverImage']))
                    <div class="mb-3">
                        <img src="{{ $payload['coverImage'] }}" alt="cover" style="max-width:320px;border-radius:8px;">
                    </div>
                @endif
                <a href="{{ route('advertisements.edit', $ad->id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('advertisements') }}" class="btn btn-default">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
