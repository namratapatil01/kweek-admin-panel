@extends('layouts.app')

@section('style')
<style>
/* ===== Coupon Form – matches reference design ===== */

/* Outer wrapper centers the card with left/right space */
.coupon-form-outer {
    padding: 0 10px;
}

/* White card with border */
.coupon-card {
    background: #fff;
    border: 1px solid #dde2e8;
    border-radius: 5px;
    padding: 20px 28px 24px;
    margin-bottom: 0;
}

/* Dark badge at top */
.coupon-card-badge {
    display: inline-block;
    background: #2b3542;
    color: #fff;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.8px;
    padding: 5px 13px;
    border-radius: 3px;
    margin-bottom: 16px;
}

/* Labels – bold, slightly dark blue */
.cf-label {
    display: block;
    font-weight: 700;
    font-size: 13px;
    color: #3260a8;
    margin-bottom: 4px;
    line-height: 1.3;
}
.cf-label .req { color: #d32f2f; margin-left: 2px; font-weight: 700; }

/* Input / Select / Textarea */
.cf-input {
    display: block;
    width: 100%;
    height: 34px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 13px;
    color: #495057;
    background: #fff;
    padding: 5px 10px;
    transition: border-color .15s;
}
.cf-input::placeholder { color: #b5bcc5; font-size: 12px; }
.cf-input:focus { border-color: #80bdff; outline: 0; box-shadow: 0 0 0 0.1rem rgba(0,123,255,.2); }
select.cf-input { height: 34px; padding: 4px 10px; }
textarea.cf-input { height: auto; resize: vertical; }

/* Hint text below fields */
.cf-hint {
    font-size: 11.5px;
    color: #8c96a2;
    margin-top: 3px;
    line-height: 1.4;
}

/* Choose File button */
.cf-choose-btn {
    display: inline-block;
    font-size: 12px;
    color: #444;
    background: #f1f3f5;
    border: 1px solid #bbb;
    border-radius: 3px;
    padding: 3px 10px;
    cursor: pointer;
    line-height: 1.8;
}
.cf-choose-btn:hover { background: #e8eaed; }
.cf-no-file { font-size: 12.5px; color: #888; margin-left: 8px; }

/* Checkbox row */
.cf-check-row {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 7px;
    cursor: pointer;
}
.cf-check-row input[type="checkbox"] {
    width: 14px; height: 14px;
    flex-shrink: 0;
    cursor: pointer;
    margin-top: 0;
    accent-color: #3260a8;
}
.cf-check-text { font-size: 13px; color: #333; font-weight: 500; }
.cf-check-text em { font-style: italic; }

/* Buttons — OUTSIDE the card, centered */
.coupon-form-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 18px;
    padding-bottom: 10px;
}
.cf-btn-save {
    background: #1e2a35;
    color: #fff;
    border: none;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 22px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.cf-btn-save:hover { background: #2e3f50; color: #fff; }
.cf-btn-back {
    background: #fff;
    color: #555;
    border: 1px solid #c8cdd3;
    font-size: 13px;
    font-weight: 500;
    padding: 7px 18px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.cf-btn-back:hover { background: #f5f6f7; color: #333; text-decoration: none; }
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
                <li class="breadcrumb-item active">Create Coupon</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="coupon-form-outer">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- ===== WHITE CARD ===== --}}
            <div class="coupon-card">

                <div class="coupon-card-badge">CREATE A COUPON</div>

                <form id="couponCreateForm" action="{{ route('coupons.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Row 1: Code | Discount Type --}}
                    <div class="form-row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="cf-label">Code</label>
                            <input type="text"
                                   name="code"
                                   class="cf-input @error('code') is-invalid @enderror"
                                   placeholder="Insert Coupon Code"
                                   value="{{ strtoupper(old('code')) }}"
                                   required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 form-group mb-3">
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
                        <div class="col-md-6 form-group mb-3">
                            <label class="cf-label">Discount <span class="req">*</span></label>
                            <input type="number"
                                   name="discount"
                                   class="cf-input @error('discount') is-invalid @enderror"
                                   step="0.01" min="0"
                                   value="{{ old('discount') }}"
                                   required>
                            <div class="cf-hint">Insert Discount (Fixed Amount (e.g. ₹50.00) or Percent (e.g. 10) for 10%)</div>
                            @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 form-group mb-3">
                            <label class="cf-label">Expires At</label>
                            <input type="text"
                                   name="expires_at"
                                   id="expires_at_input"
                                   class="cf-input @error('expires_at') is-invalid @enderror"
                                   value="{{ old('expires_at') }}"
                                   autocomplete="off"
                                   placeholder="YYYY-MM-DD HH:MM:SS">
                            <div class="cf-hint">Insert Expires At</div>
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Row 3: Stores --}}
                    <div class="form-group mb-3">
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
                        <div class="cf-hint">The coupon will be applied on selected vendors.</div>
                    </div>

                    {{-- Row 4: Description --}}
                    <div class="form-group mb-3">
                        <label class="cf-label">Description</label>
                        <textarea name="description"
                                  class="cf-input"
                                  rows="7"
                                  placeholder="Insert Description">{{ old('description') }}</textarea>
                    </div>

                    {{-- Row 5: Image --}}
                    <div class="form-group mb-3">
                        <label class="cf-label">Image</label>
                        <div style="display:flex; align-items:center;">
                            <input type="file" name="image" id="cf_img_file" accept="image/*" class="d-none"
                                   onchange="cfPreviewImg(this)">
                            <label for="cf_img_file" class="cf-choose-btn">Choose File</label>
                            <span id="cf_img_name" class="cf-no-file">No File chosen</span>
                        </div>
                        <div id="cf_img_preview" style="display:none; margin-top:8px;">
                            <img id="cf_preview_tag" src="" alt="Preview"
                                 style="max-height:85px; border-radius:4px; border:1px solid #ddd;">
                        </div>
                    </div>

                    {{-- Row 6: Checkboxes --}}
                    <div class="form-group mb-1">
                        <label class="cf-check-row">
                            <input type="checkbox" name="isEnabled" value="1"
                                   {{ old('isEnabled', 1) ? 'checked' : '' }}>
                            <span class="cf-check-text">Enabled</span>
                        </label>
                    </div>
                    <div class="form-group mb-2">
                        <label class="cf-check-row">
                            <input type="checkbox" name="is_public" value="1"
                                   {{ old('is_public', 1) ? 'checked' : '' }}>
                            <span class="cf-check-text"><em>is</em>Public</span>
                        </label>
                    </div>

                </form>
            </div>
            {{-- ===== END WHITE CARD ===== --}}

            {{-- Buttons OUTSIDE the card --}}
            <div class="coupon-form-actions">
                <button type="submit" form="couponCreateForm" class="cf-btn-save">
                    <i class="mdi mdi-content-save"></i> Save
                </button>
                <a href="{{ route('coupons') }}" class="cf-btn-back">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function cfPreviewImg(input) {
    var file = input.files[0];
    if (!file) return;
    document.getElementById('cf_img_name').textContent = file.name;
    var reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('cf_preview_tag').src = e.target.result;
        document.getElementById('cf_img_preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>
@endsection
