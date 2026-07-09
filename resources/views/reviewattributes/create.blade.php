@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.reviewattribute_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reviewattributes') }}">{{ trans('lang.reviewattribute_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.reviewattribute_create') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card pb-4">
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

                    <form method="POST" action="{{ route('reviewattributes.store') }}">
                        @csrf
                        <div class="row vendor_payout_create">
                            <div class="vendor_payout_create-inner">
                                <fieldset>
                                    <legend>{{ trans('lang.reviewattribute_create') }}</legend>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">{{ trans('lang.reviewattribute_name') }}</label>
                                        <div class="col-7">
                                            <input type="text" name="title"
                                                class="form-control @error('title') is-invalid @enderror"
                                                value="{{ old('title') }}" required autofocus>
                                            <div class="form-text text-muted">{{ trans('lang.reviewattribute_name_help') }}</div>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>

                        <div class="form-group col-12 text-center btm-btn">
                            <button type="submit" class="btn btn-primary save-form-btn">
                                <i class="fa fa-save"></i> {{ trans('lang.save') }}
                            </button>
                            <a href="{{ route('reviewattributes') }}" class="btn btn-default">
                                <i class="fa fa-undo"></i> {{ trans('lang.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
