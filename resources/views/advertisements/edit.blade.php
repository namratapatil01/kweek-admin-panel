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
                <li class="breadcrumb-item active">{{ trans('lang.advertisement_edit') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('advertisements.update', $ad->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card border mb-3">
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $ad->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ trans('lang.short_description') }}</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $payload['description'] ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Store</label>
                        <select name="vendorId" class="form-control" required>
                            <option value="">Select Store</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendorId', $ad->vendorId) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ trans('lang.priority') }}</label>
                        <select name="priority" class="form-control">
                            <option value="">{{ trans('lang.select_priority') }}</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ old('priority', $payload['priority'] ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">{{ trans('lang.advertisement_type') }}</label>
                        <select name="type" class="form-control" required>
                            <option value="restaurant_promotion" {{ old('type', $payload['type'] ?? '') == 'restaurant_promotion' ? 'selected' : '' }}>{{ trans('lang.restaurant_promotion') }}</option>
                            <option value="video_promotion" {{ old('type', $payload['type'] ?? '') == 'video_promotion' ? 'selected' : '' }}>{{ trans('lang.video_promotion') }}</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Start Date</label>
                            <input type="text" name="startDate" id="startDate" class="form-control"
                                   value="{{ old('startDate', !empty($payload['startDate']) ? \Carbon\Carbon::parse($payload['startDate'])->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">End Date</label>
                            <input type="text" name="endDate" id="endDate" class="form-control"
                                   value="{{ old('endDate', !empty($payload['endDate']) ? \Carbon\Carbon::parse($payload['endDate'])->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Cover Image</label>
                        <input type="file" name="coverImage" class="form-control-file" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Profile Image</label>
                        <input type="file" name="profileImage" class="form-control-file" accept="image/*">
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="showReview" id="showReview" value="1" {{ old('showReview', $payload['showReview'] ?? false) ? 'checked' : '' }}>
                        <label for="showReview">Review</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="showRating" id="showRating" value="1" {{ old('showRating', $payload['showRating'] ?? false) ? 'checked' : '' }}>
                        <label for="showRating">Rating</label>
                    </div>
                </div>
            </div>

            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                <a href="{{ route('advertisements') }}" class="btn btn-default"><i class="fa fa-undo"></i> Back</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
$('#startDate, #endDate').datepicker({ format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true });
</script>
@endsection
