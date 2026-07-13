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
                <li class="breadcrumb-item active">Create Advertisement</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data" id="adCreateForm">
            @csrf
            <input type="hidden" name="sectionId" value="">

            <div class="row">
                <!-- Left Column (Form fields) -->
                <div class="col-lg-7 col-md-12">
                    <div class="card border" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: relative; margin-top: 20px;">
                        <div style="position: absolute; top: -15px; left: 24px; background: #000; color: #fff; font-weight: 700; padding: 6px 16px; border-radius: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; z-index: 10;">
                            Create Advertisement
                        </div>
                        
                        <div class="card-body" style="padding: 35px 24px 24px 24px;">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Advertisement Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" style="border-radius: 8px; border-color: #cbd5e0; padding: 10px 12px; height: auto;" required>
                                <small class="text-muted d-block mt-1">Enter Advertisement Title</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Short Description</label>
                                <textarea name="description" class="form-control" rows="4" style="border-radius: 8px; border-color: #cbd5e0; padding: 10px 12px;">{{ old('description') }}</textarea>
                                <small class="text-muted d-block mt-1">Enter short description</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Store</label>
                                <select name="vendorId" class="form-control" style="border-radius: 8px; border-color: #cbd5e0; height: auto; padding: 10px 12px;" required>
                                    <option value="">Select Store</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendorId') == $vendor->id ? 'selected' : '' }}>
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
                                        <option value="{{ $i }}" {{ old('priority') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Advertisement Type</label>
                                <select name="type" class="form-control" style="border-radius: 8px; border-color: #cbd5e0; height: auto; padding: 10px 12px;" required>
                                    <option value="">Select Type</option>
                                    <option value="restaurant_promotion" {{ old('type') == 'restaurant_promotion' ? 'selected' : '' }}>Store Promotion</option>
                                    <option value="video_promotion" {{ old('type') == 'video_promotion' ? 'selected' : '' }}>Video Promotion</option>
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
                                <input type="hidden" name="startDate" id="startDate" value="{{ old('startDate') }}">
                                <input type="hidden" name="endDate" id="endDate" value="{{ old('endDate') }}">
                            </div>

                            <div class="form-group mb-4" id="cover_image_container" style="display: none;">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Cover Image</label>
                                <input type="file" name="coverImage" class="form-control-file" accept="image/*">
                            </div>

                            <div class="form-group mb-4" id="profile_image_container" style="display: none;">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Profile Image</label>
                                <input type="file" name="profileImage" class="form-control-file" accept="image/*">
                            </div>

                            <div class="form-group mb-4" id="video_input_container" style="display: none;">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px;">Video</label>
                                <input type="file" name="video" class="form-control-file" accept="video/*">
                                <small class="text-muted d-block mt-1">Maximum 2 MB. Supports: MP4, WEBM, MKV</small>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="font-weight-bold" style="color: #2b354e; font-size: 14px; margin-bottom: 8px;">Show review & rating</label>
                                <div>
                                    <div class="form-check d-flex align-items-center mb-2">
                                        <i class="fa fa-check text-dark mr-2"></i>
                                        <label class="form-check-label font-weight-bold" style="color: #2b354e; font-size: 14px; margin-bottom: 0;">Review</label>
                                        <input type="hidden" name="showReview" value="1">
                                    </div>
                                    <div class="form-check d-flex align-items-center">
                                        <i class="fa fa-check text-dark mr-2"></i>
                                        <label class="form-check-label font-weight-bold" style="color: #2b354e; font-size: 14px; margin-bottom: 0;">Rating</label>
                                        <input type="hidden" name="showRating" value="1">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer Buttons Inside Card -->
                            <div class="d-flex justify-content-center mt-5 mb-2" style="gap: 12px;">
                                <button type="submit" class="btn btn-dark" style="background-color: #000; border-color: #000; border-radius: 4px; padding: 8px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; color: #fff; font-size: 14px;">
                                    <i class="fa fa-save"></i> Save
                                </button>
                                <a href="{{ route('advertisements') }}" class="btn btn-secondary" style="background-color: #9ca3af; border-color: #9ca3af; border-radius: 4px; padding: 8px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; color: #fff; font-size: 14px;">
                                    <i class="fa fa-undo"></i> Back
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right Column (Live Preview) -->
                <div class="col-lg-5 col-md-12" style="margin-top: 20px;">
                    <h5 class="text-muted font-weight-bold mb-3" style="font-size: 14px; color: #a0aec0!important;">Advertisement Preview</h5>
                    
                    <div class="card border-0" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; background: #fff; max-width: 450px;">
                        <!-- Grey Top Area -->
                        <div style="background-color: #e2e8f0; height: 160px; position: relative;">
                            <img id="preview_image" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            <video id="preview_video" src="" style="width: 100%; height: 100%; object-fit: cover; background: #000; display: none;"></video>
                            
                            <!-- Orange toggle on right -->
                            <div style="position: absolute; right: 12px; bottom: 12px;">
                                <span style="display: inline-block; width: 26px; height: 14px; background: #ed8936; border-radius: 12px; position: relative;">
                                    <span style="display: inline-block; width: 16px; height: 16px; background: #fff; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.2); position: absolute; right: -2px; top: -1px;"></span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- White Bottom Area -->
                        <div class="card-body" style="padding: 16px; display: flex; align-items: flex-start;">
                            <!-- White circle on left -->
                            <div style="width: 44px; height: 44px; background: #f7fafc; border-radius: 50%; margin-right: 12px; overflow: hidden;">
                                <img id="preview_profile" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            </div>
                            
                            <div style="flex: 1; padding-top: 2px;">
                                <h5 id="preview_title" class="font-weight-bold mb-1" style="color: #2d3748; font-size: 14px;">Title</h5>
                                <div id="preview_description" class="text-muted mb-0" style="font-size: 12px; line-height: 1.4; background: #e2e8f0; height: 8px; width: 80%; border-radius: 4px; margin-top: 6px;"></div>
                                <div class="text-muted mb-0" style="font-size: 12px; line-height: 1.4; background: #e2e8f0; height: 8px; width: 50%; border-radius: 4px; margin-top: 6px;"></div>
                            </div>
                            
                            <div style="padding-top: 4px;">
                                <i class="fa fa-heart-o" style="color: #a0aec0; font-size: 16px;"></i>
                            </div>
                        </div>
                    </div>
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
    // Read section_id cookie
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
    $('input[name="sectionId"]').val(getCookie('section_id') || '');

    // Initialize daterangepicker
    $('#validity').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        autoUpdateInput: false,
        startDate: moment().format('YYYY-MM-DD'),
        endDate: moment().add(30, 'days').format('YYYY-MM-DD')
    }, function (start, end, label) {
        $('#startDate').val(start.format('YYYY-MM-DD'));
        $('#endDate').val(end.format('YYYY-MM-DD'));
        $('#validity').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    });

    // Toggle forms and preview elements based on Type
    $('select[name="type"]').on('change', function () {
        var val = $(this).val();
        if (val === 'video_promotion') {
            $('#video_input_container').show();
            $('#cover_image_container').hide();
            $('#profile_image_container').hide();
            $('#preview_video').show();
            $('#preview_image').hide();
        } else {
            $('#video_input_container').hide();
            $('#cover_image_container').show();
            $('#profile_image_container').show();
            $('#preview_video').hide();
            $('#preview_image').show();
        }
    });

    // Live update preview title
    $('input[name="title"]').on('input', function () {
        var title = $(this).val();
        $('#preview_title').text(title || 'Title');
    });

    // Handle checkboxes to look like checkmarks from image
    // The image just shows checkmarks instead of inputs
    // We already hid the input and just show the checkmark.

    // Live update image file input preview
    $('input[name="coverImage"]').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            $('#preview_image').attr('src', url).show();
        }
    });

    $('input[name="profileImage"]').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            $('#preview_profile').attr('src', url).show();
        }
    });

    // Live update video file input preview
    $('input[name="video"]').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            $('#preview_video').attr('src', url).show();
        }
    });
});
</script>
@endsection
