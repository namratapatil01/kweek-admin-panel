@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.category_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories') }}">{{ trans('lang.category_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.category_create') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#category_information" aria-controls="description" role="tab" data-toggle="tab"
                                class="nav-link active">{{ trans('lang.category_information') }}</a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
                                class="nav-link">{{ trans('lang.reviewattribute_plural') }}</a>
                        </li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ old('section_id', request()->cookie('section_id')) }}">

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row vendor_payout_create" role="tabpanel">
                            <div class="vendor_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="category_information">
                                    <fieldset>
                                        <legend>{{ trans('lang.category_create') }}</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_name') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                    name="title" value="{{ old('title') }}" required>
                                                <div class="form-text text-muted">{{ trans('lang.category_name_help') }}</div>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_description') }}</label>
                                            <div class="col-7">
                                                <textarea rows="7" class="form-control @error('description') is-invalid @enderror"
                                                    name="description" id="category_description">{{ old('description') }}</textarea>
                                                <div class="form-text text-muted">{{ trans('lang.category_description_help') }}</div>
                                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_image') }}</label>
                                            <div class="col-7">
                                                <input type="file" id="category_image" name="photo" accept="image/*" required>
                                                <div class="placeholder_img_thumb cat_image mt-2" id="image_preview"></div>
                                                <div class="form-text text-muted w-50">{{ trans('lang.category_image_help') }}</div>
                                                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-check width-100">
                                            <input type="hidden" name="publish" value="0">
                                            <input type="checkbox" class="item_publish" id="item_publish" name="publish" value="1"
                                                {{ old('publish', '1') == '1' ? 'checked' : '' }}>
                                            <label class="col-3 control-label" for="item_publish">{{ trans('lang.item_publish') }}</label>
                                        </div>

                                        @if($showInHomeOption)
                                            <div class="form-check row width-100" id="show_in_home">
                                                <input type="hidden" name="show_in_homepage" value="0">
                                                <input type="checkbox" id="show_in_homepage" name="show_in_homepage" value="1"
                                                    {{ old('show_in_homepage') ? 'checked' : '' }}>
                                                <label class="col-3 control-label" for="show_in_homepage">{{ trans('lang.show_in_home') }}</label>
                                                <div class="form-text text-muted w-50">
                                                    {{ trans('lang.show_in_home_desc') }}
                                                    @if($section)
                                                        <span id="forsection"> for {{ $section->name ?? $section->title }} section</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </fieldset>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                    @forelse($reviewAttributes as $attribute)
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="review_attribute_{{ $attribute->id }}"
                                                name="review_attributes[]" value="{{ $attribute->id }}"
                                                {{ in_array($attribute->id, old('review_attributes', []), true) ? 'checked' : '' }}>
                                            <label class="col-3 control-label" for="review_attribute_{{ $attribute->id }}">
                                                {{ $attribute->title ?? $attribute->name }}
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted">{{ trans('lang.no_record_found') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary save-setting-btn">
                            <i class="fa fa-save"></i> {{ trans('lang.save') }}
                        </button>
                        <a href="{{ route('categories') }}" class="btn btn-default">
                            <i class="fa fa-undo"></i> {{ trans('lang.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $('#category_image').on('change', function (event) {
        var file = event.target.files[0];
        if (!file) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image_preview').html('<img class="rounded" style="width:50px;height:50px;object-fit:cover;" src="' + e.target.result + '" alt="image">');
        };
        reader.readAsDataURL(file);
    });
</script>
@endsection
