@extends('layouts.app')
@section('content')
@php
    $photo = old('photo', data_get($record, 'photo') ?? data_get($record, 'payload.photo'));
    $description = old('description', data_get($record, 'description') ?? data_get($record, 'payload.description'));
    $title = old('title', $record->title ?? '');
    $isPublished = old('publish', filter_var(data_get($record, 'publish'), FILTER_VALIDATE_BOOLEAN));
    $showInHome = old('show_in_homepage', filter_var(data_get($record, 'show_in_homepage') ?? data_get($record, 'payload.show_in_homepage'), FILTER_VALIDATE_BOOLEAN));
    $placeholderImage = asset('images/default_user.png');
    $placeholderRaw = \DB::table('settings')->where('id', 'placeHolderImage')->value('value');
    if ($placeholderRaw) {
        $decoded = json_decode($placeholderRaw, true);
        if (!empty($decoded['image'])) {
            $placeholderImage = $decoded['image'];
        }
    }
@endphp

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.category_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories') }}">{{ trans('lang.category_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.category_edit') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#category_information" aria-controls="category_information" role="tab"
                                data-toggle="tab" class="nav-link active">
                                <i class="ri-list-indefinite"></i> {{ trans('lang.category_information') }}
                            </a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
                                class="nav-link">
                                <i class="ri-list-check"></i> {{ trans('lang.reviewattribute_plural') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('categories.update', $id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section_id" value="{{ old('section_id', $record->section_id ?? data_get($record, 'payload.section_id') ?? request()->cookie('section_id')) }}">

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
                            <div class="vendor_payout_create-inner tab-content category_edit_div">
                                <div role="tabpanel" class="tab-pane active" id="category_information">
                                    <fieldset>
                                        <legend>{{ trans('lang.category_edit') }}</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_name') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                    name="title" value="{{ $title }}" required>
                                                <div class="form-text text-muted">{{ trans('lang.category_name_help') }}</div>
                                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_description') }}</label>
                                            <div class="col-7">
                                                <textarea rows="7" class="form-control @error('description') is-invalid @enderror"
                                                    name="description" id="category_description">{{ $description }}</textarea>
                                                <div class="form-text text-muted">{{ trans('lang.category_description_help') }}</div>
                                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">{{ trans('lang.category_image') }}</label>
                                            <div class="col-7">
                                                <input type="file" id="category_image" name="photo" accept="image/*">
                                                <div class="placeholder_img_thumb cat_image mt-2" id="image_preview">
                                                    @if($photo)
                                                        <img class="rounded" style="width:50px;height:50px;object-fit:cover;"
                                                            src="{{ $photo }}" alt="image"
                                                            onerror="this.onerror=null;this.src='{{ $placeholderImage }}'">
                                                    @else
                                                        <img class="rounded" style="width:50px;height:50px;object-fit:cover;"
                                                            src="{{ $placeholderImage }}" alt="image">
                                                    @endif
                                                </div>
                                                <div class="form-text text-muted w-50">{{ trans('lang.category_image_help') }}</div>
                                                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="form-check width-100">
                                            <input type="hidden" name="publish" value="0">
                                            <input type="checkbox" class="item_publish" id="item_publish" name="publish" value="1"
                                                {{ $isPublished ? 'checked' : '' }}>
                                            <label class="col-3 control-label" for="item_publish">{{ trans('lang.item_publish') }}</label>
                                        </div>

                                        @if($showInHomeOption)
                                            <div class="form-check row width-100" id="show_in_home">
                                                <input type="hidden" name="show_in_homepage" value="0">
                                                <input type="checkbox" id="show_in_homepage" name="show_in_homepage" value="1"
                                                    {{ $showInHome ? 'checked' : '' }}>
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
                                                {{ in_array($attribute->id, old('review_attributes', $selectedReviewAttributes), true) ? 'checked' : '' }}>
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
                        <button type="submit" class="btn btn-primary edit-setting-btn">
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
