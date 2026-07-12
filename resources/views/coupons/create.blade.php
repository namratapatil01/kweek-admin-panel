@extends('layouts.app')

@section('style')
<style>
.coupon-card {
    background: #fff;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    padding: 30px 40px;
    margin: 20px auto 40px;
    max-width: 900px;
}
.coupon-card-title {
    display: inline-block;
    background: #000;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 6px 16px;
    border-radius: 4px;
    margin-bottom: 24px;
    text-transform: uppercase;
}
.cf-label {
    display: block;
    font-weight: 600;
    font-size: 13.5px;
    color: #2b354e;
    margin-bottom: 6px;
}
.cf-label .req { color: #e53935; margin-left: 2px; }
.cf-input {
    display: block;
    width: 100%;
    border: 1.5px solid #cbd5e0;
    border-radius: 8px;
    font-size: 14px;
    color: #2b354e;
    background: #fff;
    padding: 10px 16px;
    transition: all 0.2s ease-in-out;
    height: 46px;
}
.cf-input:focus {
    border-color: #2b3542;
    box-shadow: 0 0 0 3px rgba(43,53,66,0.1);
    outline: none;
}
textarea.cf-input {
    height: auto;
    resize: vertical;
    border-radius: 8px;
    padding: 12px 16px;
}
select.cf-input {
    appearance: auto;
}
.cf-hint {
    font-size: 12px;
    color: #718096;
    margin-top: 4px;
}
.cf-file-btn {
    display: inline-block;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    padding: 8px 16px;
    cursor: pointer;
    color: #4a5568;
    background: #f7fafc;
    transition: all 0.2s;
}
.cf-file-btn:hover {
    background: #edf2f7;
}
.cf-file-label {
    display: block;
    font-weight: 600;
    font-size: 13.5px;
    color: #2b354e;
    margin-bottom: 6px;
}
.cf-check-row {
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cf-check-row input[type="checkbox"] {
    position: relative !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    width: 18px !important;
    height: 18px !important;
    border: 1.5px solid #cbd5e0 !important;
    border-radius: 4px !important;
    outline: none !important;
    cursor: pointer !important;
    background-color: #fff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease-in-out !important;
    margin: 0 !important;
    opacity: 1 !important;
}
.cf-check-row input[type="checkbox"]:checked {
    background-color: #ff5c28 !important;
    border-color: #ff5c28 !important;
}
.cf-check-row input[type="checkbox"]:checked::after {
    content: '' !important;
    width: 5px !important;
    height: 9px !important;
    border: solid white !important;
    border-width: 0 2.5px 2.5px 0 !important;
    transform: rotate(45deg) !important;
    margin-bottom: 2px !important;
    display: block !important;
}
.cf-check-row label {
    font-size: 14px;
    color: #2b354e;
    font-weight: 500;
    margin-bottom: 0;
    cursor: pointer;
    margin-left: 8px !important;
    padding-left: 0 !important;
}
.cf-check-row label::before,
.cf-check-row label::after {
    display: none !important;
    content: none !important;
}
.cf-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 30px;
}
.cf-btn-save {
    background: #000;
    border: 1px solid #000;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    padding: 10px 24px;
    border-radius: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}
.cf-btn-save:hover {
    background: #222;
}
.cf-btn-back {
    background: #fff;
    border: 1.5px solid #cbd5e0;
    color: #4a5568;
    font-size: 14px;
    font-weight: 700;
    padding: 10px 24px;
    border-radius: 24px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}
.cf-btn-back:hover {
    background: #edf2f7;
    color: #2d3748;
    text-decoration: none;
}
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Coupons</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('coupons') }}">Coupons</a></li>
                <li class="breadcrumb-item active">Create a Coupon</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="coupon-card">

            <div class="coupon-card-title">CREATE A COUPON</div>

            <form action="{{ route('coupons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Row 1: Code | Discount Type --}}
                <div class="form-row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="cf-label">Code</label>
                        <input type="text"
                               name="code"
                               class="cf-input @error('code') is-invalid @enderror"
                               placeholder="Insert Coupon Code"
                               value="{{ strtoupper(old('code')) }}"
                               required>
                        <div class="cf-hint">Insert Coupon Code</div>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="cf-label">Discount Type</label>
                        <select name="discount_type"
                                class="cf-input @error('discount_type') is-invalid @enderror"
                                required>
                            <option value="Percentage" {{ old('discount_type','Percentage') == 'Percentage' ? 'selected' : '' }}>Percent</option>
                            <option value="Fix Price"  {{ old('discount_type') == 'Fix Price' ? 'selected' : '' }}>Fix Price</option>
                        </select>
                        <div class="cf-hint">Select Discount Type</div>
                        @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Row 2: Discount | Expires At --}}
                <div class="form-row">
                    <div class="col-md-6 form-group mb-4">
                        <label class="cf-label">Discount <span class="req">*</span></label>
                        <input type="number"
                               name="discount"
                               class="cf-input @error('discount') is-invalid @enderror"
                               placeholder="Insert Discount Amount"
                               step="0.01" min="0"
                               value="{{ old('discount') }}"
                               required>
                        <div class="cf-hint">Insert Discount Fixed Amount (Ex: 8 for 8$) or Percent (Ex: 10 for 10%)</div>
                        @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-4">
                        <label class="cf-label">Expires At</label>
                        <div class="input-group date" id="datetimepicker1">
                            <input type="text"
                                   name="expires_at"
                                   id="expires_at_input"
                                   class="cf-input date_picker @error('expires_at') is-invalid @enderror"
                                   placeholder="Select Expiry Date"
                                   value="{{ old('expires_at') }}"
                                   autocomplete="off">
                        </div>
                        <div class="cf-hint">Insert Expires At</div>
                        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Row 3: Stores --}}
                <div class="form-group mb-4">
                    <label class="cf-label">Stores</label>
                    <select name="vendor_id" class="cf-input">
                        <option value="">Select Store</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}"
                                {{ old('vendor_id', $vendorId ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->title }}
                            </option>
                        @endforeach
                    </select>
                    <div class="cf-hint">The coupon will applied on selected vendors</div>
                </div>

                {{-- Row 4: Description --}}
                <div class="form-group mb-4">
                    <label class="cf-label">Description</label>
                    <textarea name="description"
                              class="cf-input"
                              placeholder="Insert Description"
                              rows="6">{{ old('description') }}</textarea>
                    <div class="cf-hint">Insert Description</div>
                </div>

                {{-- Row 5: Image --}}
                <div class="form-group mb-4">
                    <label class="cf-file-label">Image</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="file" name="image" id="cf_image_file"
                               accept="image/*" class="d-none"
                               onchange="cfPreviewImg(this)">
                        <label for="cf_image_file" class="cf-file-btn">Choose File</label>
                        <span id="cf_img_name" style="font-size:13px; color:#888;">No File chosen</span>
                    </div>
                    <div id="cf_img_preview" style="display:none; margin-top:8px;">
                        <img id="cf_preview_tag" src="" alt="Preview"
                             style="max-height:90px; border-radius:5px; border:1px solid #ddd;">
                    </div>
                </div>

                {{-- Row 6: Enabled + isPublic --}}
                <div class="form-group cf-check-row">
                    <input type="checkbox" name="isEnabled" id="coupon_enabled" value="1"
                           {{ old('isEnabled', 1) ? 'checked' : '' }}>
                    <label for="coupon_enabled">Enabled</label>
                </div>
                <div class="form-group cf-check-row mb-4">
                    <input type="checkbox" name="is_public" id="coupon_public" value="1"
                           {{ old('is_public', 1) ? 'checked' : '' }}>
                    <label for="coupon_public">isPublic</label>
                </div>

                <div class="cf-actions">
                    <button type="submit" class="cf-btn-save">
                        <i class="mdi mdi-content-save mr-1"></i> Save
                    </button>
                    <a href="{{ route('coupons') }}" class="cf-btn-back">
                        <i class="fa fa-undo mr-1"></i> Back
                    </a>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
$(document).ready(function () {
    $('#datetimepicker1').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
});
function cfPreviewImg(input) {
    var file = input.files[0];
    if (file) {
        document.getElementById('cf_img_name').textContent = file.name;
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('cf_preview_tag').src = e.target.result;
            document.getElementById('cf_img_preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
