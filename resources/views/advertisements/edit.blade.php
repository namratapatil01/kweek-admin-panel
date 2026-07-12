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
                <li class="breadcrumb-item active">Edit Advertisement</li>
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

            <div class="row">
                <!-- Left Column (Form fields) -->
                <div class="col-lg-7 col-md-12">
                    <div class="card border" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: relative; margin-top: 20px;">
                        <div style="position: absolute; top: -15px; left: 24px; background: #000; color: #fff; font-weight: 700; padding: 6px 16px; border-radius: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; z-index: 10;">
                            Edit Advertisement
                        </div>
                        
                        <div class="card-body" style="padding: 35px 24px 24px 24px;">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Advertisement Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $ad->title) }}" style="border-radius: 8px; border-color: #cbd5e0; padding: 10px 12px; height: auto;" required>
                                <small class="text-muted d-block mt-1">Enter Advertisement Title</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Short Description</label>
                                <textarea name="description" class="form-control" rows="4" style="border-radius: 8px; border-color: #cbd5e0; padding: 10px 12px;">{{ old('description', $payload['description'] ?? '') }}</textarea>
                                <small class="text-muted d-block mt-1">Enter short description</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Store</label>
                                <select name="vendorId" class="form-control" style="border-radius: 8px; border-color: #cbd5e0; height: auto; padding: 10px 12px;" required>
                                    <option value="">Select Store</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendorId', $ad->vendorId) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Priority</label>
                                <select name="priority" class="form-control" style="border-radius: 8px; border-color: #cbd5e0; height: auto; padding: 10px 12px;">
                                    <option value="">Select Priority</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('priority', $payload['priority'] ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Advertisement Type</label>
                                <select name="type" class="form-control" style="border-radius: 8px; border-color: #cbd5e0; height: auto; padding: 10px 12px;" required>
                                    <option value="restaurant_promotion" {{ old('type', $payload['type'] ?? '') == 'restaurant_promotion' ? 'selected' : '' }}>Store Promotion</option>
                                    <option value="video_promotion" {{ old('type', $payload['type'] ?? '') == 'video_promotion' ? 'selected' : '' }}>Video Promotion</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Validity</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e0; color: #718096;"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    <input type="text" id="validity" class="form-control bg-white border-left-0" style="border-radius: 0 8px 8px 0; border-color: #cbd5e0; height: auto; padding: 10px 12px; color: #2b354e; font-size: 14px;" readonly placeholder="Select Validity Period">
                                </div>
                                <input type="hidden" name="startDate" id="startDate" value="{{ old('startDate', !empty($payload['startDate']) ? \Carbon\Carbon::parse($payload['startDate'])->format('Y-m-d') : '') }}">
                                <input type="hidden" name="endDate" id="endDate" value="{{ old('endDate', !empty($payload['endDate']) ? \Carbon\Carbon::parse($payload['endDate'])->format('Y-m-d') : '') }}">
                            </div>

                            <div class="form-group mb-4" id="cover_image_container" style="{{ ($payload['type'] ?? '') !== 'video_promotion' ? '' : 'display: none;' }}">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Cover Image</label>
                                <input type="file" name="coverImage" class="form-control-file" accept="image/*">
                            </div>

                            <div class="form-group mb-4" id="profile_image_container" style="{{ ($payload['type'] ?? '') !== 'video_promotion' ? '' : 'display: none;' }}">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Profile Image</label>
                                <input type="file" name="profileImage" class="form-control-file" accept="image/*">
                            </div>

                            <div class="form-group mb-4" id="video_input_container" style="{{ ($payload['type'] ?? '') === 'video_promotion' ? '' : 'display: none;' }}">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Video</label>
                                <input type="file" name="video" class="form-control-file" accept="video/*">
                                <small class="text-muted d-block mt-1">Maximum 2 MB. Supports: MP4, WEBM, MKV</small>
                            </div>
                            
                            <div class="form-row mb-2">
                                <div class="form-group col-md-6 mb-0">
                                    <div class="form-check">
                                        <input type="checkbox" name="showReview" id="showReview" class="form-check-input" value="1" {{ old('showReview', $payload['showReview'] ?? false) ? 'checked' : '' }}>
                                        <label for="showReview" class="form-check-label font-weight-bold" style="color: #2b354e; font-size: 14px; cursor: pointer;">Review</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 mb-0">
                                    <div class="form-check">
                                        <input type="checkbox" name="showRating" id="showRating" class="form-check-input" value="1" {{ old('showRating', $payload['showRating'] ?? false) ? 'checked' : '' }}>
                                        <label for="showRating" class="form-check-label font-weight-bold" style="color: #2b354e; font-size: 14px; cursor: pointer;">Rating</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Live Preview) -->
                <div class="col-lg-5 col-md-12" style="margin-top: 20px;">
                    <h5 class="text-muted font-weight-bold mb-3" style="font-size: 14px;">Advertisement Preview</h5>
                    
                    <div class="card border-0" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; background: #fff;">
                        <div class="preview-media-container" style="background: #000; position: relative; height: 180px; display: flex; align-items: center; justify-content: center;">
                            <!-- Image Preview -->
                            <div id="preview_image_element_container" style="{{ ($payload['type'] ?? '') !== 'video_promotion' ? '' : 'display: none;' }} width: 100%; height: 100%;">
                                <img id="preview_image" src="{{ $payload['coverImage'] ?? asset('images/default_user.png') }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('images/default_user.png') }}'">
                            </div>
                            <!-- Video Preview -->
                            <div id="preview_video_element_container" style="{{ ($payload['type'] ?? '') === 'video_promotion' ? '' : 'display: none;' }} width: 100%; height: 100%;">
                                <video id="preview_video" src="{{ $payload['video'] ?? '' }}" controls style="width: 100%; height: 100%; object-fit: cover; background: #000;"></video>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 16px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div style="flex: 1; padding-right: 12px;">
                                    <h5 id="preview_title" class="font-weight-bold mb-1" style="color: #2d3748; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ad->title ?: 'Fashion Made Effortless' }}</h5>
                                    <p id="preview_description" class="text-muted mb-0" style="font-size: 13px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $payload['description'] ?? 'Explore a wide range of stylish clothing for every season. Enjoy exclusive online deals and upgrade your wardrobe instantly!' }}</p>
                                </div>
                                <div>
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: #000; color: #fff; font-size: 14px; cursor: pointer;">
                                        <i class="fa fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="row mt-4 mb-4">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-dark mr-3 px-5 py-2 font-weight-bold" style="background-color: #000; border-color: #000; color: #fff; border-radius: 8px;">
                        <i class="fa fa-save mr-1"></i> Save
                    </button>
                    <a href="{{ route('advertisements') }}" class="btn btn-secondary px-5 py-2 font-weight-bold" style="background-color: #cbd5e0; border-color: #cbd5e0; color: #4a5568; border-radius: 8px;">
                        <i class="fa fa-undo mr-1"></i> Back
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- DateRangePicker dependencies -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
$(document).ready(function () {
    // Initialize daterangepicker
    $('#validity').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        autoUpdateInput: false,
        startDate: $('#startDate').val() || moment().format('YYYY-MM-DD'),
        endDate: $('#endDate').val() || moment().add(30, 'days').format('YYYY-MM-DD')
    }, function (start, end, label) {
        $('#startDate').val(start.format('YYYY-MM-DD'));
        $('#endDate').val(end.format('YYYY-MM-DD'));
        $('#validity').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    });

    // Populate initial value if dates exist
    var initStart = $('#startDate').val();
    var initEnd = $('#endDate').val();
    if (initStart && initEnd) {
        $('#validity').val(moment(initStart).format('MMMM D, YYYY') + ' - ' + moment(initEnd).format('MMMM D, YYYY'));
    }

    // Toggle forms and preview elements based on Type
    $('select[name="type"]').on('change', function () {
        var val = $(this).val();
        if (val === 'video_promotion') {
            $('#video_input_container').show();
            $('#cover_image_container').hide();
            $('#profile_image_container').hide();
            $('#preview_video_element_container').show();
            $('#preview_image_element_container').hide();
        } else {
            $('#video_input_container').hide();
            $('#cover_image_container').show();
            $('#profile_image_container').show();
            $('#preview_video_element_container').hide();
            $('#preview_image_element_container').show();
        }
    });

    // Live update preview title
    $('input[name="title"]').on('input', function () {
        $('#preview_title').text($(this).val() || 'Fashion Made Effortless');
    });

    // Live update preview description
    $('textarea[name="description"]').on('input', function () {
        $('#preview_description').text($(this).val() || 'Explore a wide range of stylish clothing for every season. Enjoy exclusive online deals and upgrade your wardrobe instantly!');
    });

    // Live update image file input preview
    $('input[name="coverImage"]').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            $('#preview_image').attr('src', url);
        }
    });

    // Live update video file input preview
    $('input[name="video"]').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            $('#preview_video').attr('src', url);
        }
    });
});
</script>
@endsection
