@extends('layouts.app')

@section('style')
<style>
.ad-form-card {
    background: #fff;
    border: 1px solid #e0e4ea;
    border-radius: 6px;
    padding: 24px 28px 28px;
    margin-bottom: 20px;
}
.ad-form-badge {
    display: inline-block;
    background: #2b3542;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.7px;
    padding: 5px 14px;
    border-radius: 4px;
    margin-bottom: 18px;
}
.ad-label {
    display: block;
    font-weight: 700;
    font-size: 13px;
    color: #222;
    margin-bottom: 6px;
}
.ad-input {
    display: block;
    width: 100%;
    border: 1px solid #d3d8e0;
    border-radius: 4px;
    font-size: 13px;
    color: #444;
    background: #fff;
    padding: 8px 12px;
    height: 40px;
}
.ad-input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 2px rgba(78,115,223,.12);
    outline: none;
}
textarea.ad-input { height: auto; resize: vertical; min-height: 90px; }
.ad-hint { font-size: 11.5px; color: #888; margin-top: 3px; }
.ad-check-row { margin-bottom: 8px; }
.ad-check-row input[type="checkbox"] + label {
    font-size: 13px;
    color: #333;
    font-weight: 500;
    margin-bottom: 0;
}
.ad-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
    margin-bottom: 30px;
}
.ad-btn-save {
    background: #1e2a35;
    border: none;
    color: #fff;
    font-size: 13px;
    padding: 8px 24px;
    border-radius: 4px;
    cursor: pointer;
}
.ad-btn-back {
    background: #fff;
    border: 1px solid #ccc;
    color: #555;
    font-size: 13px;
    padding: 8px 20px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.ad-preview-card {
    background: #fff;
    border: 1px solid #e0e4ea;
    border-radius: 6px;
    padding: 20px;
}
.ad-preview-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 16px;
}
.ad-preview-box {
    border: 1px solid #eceff3;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.ad-preview-cover {
    height: 180px;
    background: #eceff1;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.ad-preview-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ad-preview-badge {
    position: absolute;
    right: 12px;
    bottom: 12px;
    background: #ff8a00;
    color: #fff;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
}
.ad-preview-body {
    display: flex;
    align-items: center;
    padding: 14px;
    gap: 12px;
}
.ad-preview-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #eceff1;
    overflow: hidden;
    flex-shrink: 0;
}
.ad-preview-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ad-preview-meta { flex: 1; min-width: 0; }
.ad-preview-meta .t {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ad-preview-meta .d {
    color: #888;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ad-preview-heart { color: #bbb; font-size: 18px; }
.date-range-wrap { position: relative; }
.date-range-wrap .fa-calendar {
    position: absolute;
    left: 12px;
    top: 12px;
    color: #888;
    z-index: 2;
}
.date-range-wrap .ad-input { padding-left: 34px; }
</style>
@endsection

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
                <li class="breadcrumb-item active">{{ trans('lang.advertisement_create') }}</li>
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

        <form method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data" id="adCreateForm">
            @csrf
            <input type="hidden" name="sectionId" value="">
            <input type="hidden" name="startDate" id="startDate">
            <input type="hidden" name="endDate" id="endDate">

            <div class="row">
                <div class="col-lg-7">
                    <div class="ad-form-card">
                        <div class="ad-form-badge">CREATE ADVERTISEMENT</div>

                        <div class="form-group mb-3">
                            <label class="ad-label">{{ trans('lang.advertisement_plural') }} Title</label>
                            <input type="text" name="title" id="ad_title" class="ad-input" value="{{ old('title') }}" required>
                            <div class="ad-hint">Enter Advertisement Title</div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">{{ trans('lang.short_description') }}</label>
                            <textarea name="description" id="ad_description" class="ad-input" rows="4">{{ old('description') }}</textarea>
                            <div class="ad-hint">{{ trans('lang.short_description_help') }}</div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">Store</label>
                            <select name="vendorId" id="ad_vendor" class="ad-input" required>
                                <option value="">Select Store</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}"
                                        data-photo="{{ $vendor->photo }}"
                                        {{ old('vendorId', $vendorId ?? '') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">{{ trans('lang.priority') }}</label>
                            <select name="priority" class="ad-input">
                                <option value="">{{ trans('lang.select_priority') }}</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('priority') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">{{ trans('lang.advertisement_type') }}</label>
                            <select name="type" id="ad_type" class="ad-input" required>
                                <option value="">{{ trans('lang.select_advertisement_type') }}</option>
                                <option value="restaurant_promotion" {{ old('type') == 'restaurant_promotion' ? 'selected' : '' }}>{{ trans('lang.restaurant_promotion') }}</option>
                                <option value="video_promotion" {{ old('type') == 'video_promotion' ? 'selected' : '' }}>{{ trans('lang.video_promotion') }}</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">{{ trans('lang.validity') }}</label>
                            <div class="date-range-wrap">
                                <i class="fa fa-calendar"></i>
                                <input type="text" id="validity_range" class="ad-input" value="" autocomplete="off" readonly>
                            </div>
                            <div class="ad-hint">{{ trans('lang.validity_help') }}</div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">Cover Image</label>
                            <input type="file" name="coverImage" id="coverImage" accept="image/*" class="form-control-file">
                        </div>

                        <div class="form-group mb-3">
                            <label class="ad-label">Profile Image</label>
                            <input type="file" name="profileImage" id="profileImage" accept="image/*" class="form-control-file">
                        </div>

                        <div class="form-group mb-2">
                            <label class="ad-label">{{ trans('lang.show_review_rating') }}</label>
                            <div class="ad-check-row">
                                <input type="checkbox" name="showReview" id="showReview" value="1" {{ old('showReview', 1) ? 'checked' : '' }}>
                                <label for="showReview">Review</label>
                            </div>
                            <div class="ad-check-row">
                                <input type="checkbox" name="showRating" id="showRating" value="1" {{ old('showRating', 1) ? 'checked' : '' }}>
                                <label for="showRating">Rating</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="ad-preview-card">
                        <div class="ad-preview-title">{{ trans('lang.advertisement_preview') }}</div>
                        <div class="ad-preview-box">
                            <div class="ad-preview-cover" id="previewCover">
                                <span class="text-muted">Cover Image</span>
                                <span class="ad-preview-badge" id="previewTypeBadge">Ad</span>
                            </div>
                            <div class="ad-preview-body">
                                <div class="ad-preview-avatar" id="previewAvatar"></div>
                                <div class="ad-preview-meta">
                                    <div class="t" id="previewTitle">Title</div>
                                    <div class="d" id="previewDesc">Description</div>
                                </div>
                                <i class="fa fa-heart-o ad-preview-heart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ad-actions">
                <button type="submit" class="ad-btn-save">
                    <i class="fa fa-save mr-1"></i> Save
                </button>
                <a href="{{ route('advertisements') }}" class="ad-btn-back">
                    <i class="fa fa-undo mr-1"></i> Back
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
$(document).ready(function () {
    $('input[name="sectionId"]').val((typeof getCookie === 'function' ? getCookie('section_id') : '') || '');

    // Simple start/end date pick using two-step selection on one field
    var startDate = null;
    var endDate = null;
    $('#validity_range').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        var selected = e.format('yyyy-mm-dd');
        if (!startDate || (startDate && endDate)) {
            startDate = selected;
            endDate = null;
            $('#startDate').val(startDate);
            $('#endDate').val('');
            $('#validity_range').val(startDate + ' - Select end');
        } else {
            if (selected < startDate) {
                endDate = startDate;
                startDate = selected;
            } else {
                endDate = selected;
            }
            $('#startDate').val(startDate);
            $('#endDate').val(endDate);
            $('#validity_range').val(startDate + ' - ' + endDate);
            $('#validity_range').datepicker('hide');
        }
    });

    function updatePreview() {
        var title = $('#ad_title').val() || 'Title';
        var desc = $('#ad_description').val() || 'Description';
        var type = $('#ad_type').val();
        $('#previewTitle').text(title);
        $('#previewDesc').text(desc);
        $('#previewTypeBadge').text(type === 'video_promotion' ? 'Video' : (type === 'restaurant_promotion' ? 'Store' : 'Ad'));
    }

    $('#ad_title, #ad_description, #ad_type').on('input change', updatePreview);

    $('#coverImage').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#previewCover').html('<img src="' + e.target.result + '" alt="cover"><span class="ad-preview-badge" id="previewTypeBadge">Ad</span>');
            updatePreview();
        };
        reader.readAsDataURL(file);
    });

    $('#profileImage').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#previewAvatar').html('<img src="' + e.target.result + '" alt="profile">');
        };
        reader.readAsDataURL(file);
    });

    $('#ad_vendor').on('change', function () {
        var photo = $(this).find(':selected').data('photo');
        if (photo) {
            $('#previewAvatar').html('<img src="' + photo + '" alt="store" onerror="this.remove()">');
        }
    });

    updatePreview();
});
</script>
@endsection
