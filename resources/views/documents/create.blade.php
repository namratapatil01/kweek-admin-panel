@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.document_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ url('documents') }}">{{ trans('lang.document_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.document_create') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card border">
            <div class="card-body">
                <div class="error_top alert alert-danger" style="display:none"></div>
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('documents.store') }}" method="POST" id="document-form">
                    @csrf
                    
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.document_create') }}</legend>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" style="font-weight: 600; color: #2b354e;">{{ trans('lang.title') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                                        <div class="form-text text-muted" style="font-size: 12px; margin-top: 4px;">{{ trans('lang.document_title_help') }}</div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" style="font-weight: 600; color: #2b354e;">{{ trans('lang.document_for') }}</label>
                                    <div class="col-7">
                                        <select class="form-control" id="type" name="type">
                                            <option value="vendor" {{ old('type') == 'vendor' ? 'selected' : '' }}>{{ trans('lang.document_vendor') }}</option>
                                            <option value="driver" {{ old('type') == 'driver' ? 'selected' : '' }}>{{ trans('lang.document_driver') }}</option>
                                            <option value="owner" {{ old('type') == 'owner' ? 'selected' : '' }}>{{ trans('lang.document_owner') }}</option>
                                        </select>
                                        <div class="form-text text-muted" style="font-size: 12px; margin-top: 4px;">{{ trans('lang.select_document_for') }}</div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <div class="form-check">
                                        <input type="hidden" name="frontSide" value="0">
                                        <input type="checkbox" id="frontSide" name="frontSide" value="1" {{ old('frontSide') ? 'checked' : '' }}>
                                        <label class="control-label" for="frontSide" style="font-weight: 600; color: #2b354e;">{{ trans('lang.frontside') }}</label>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <div class="form-check">
                                        <input type="hidden" name="backSide" value="0">
                                        <input type="checkbox" id="backSide" name="backSide" value="1" {{ old('backSide') ? 'checked' : '' }}>
                                        <label class="control-label" for="backSide" style="font-weight: 600; color: #2b354e;">{{ trans('lang.backside') }}</label>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <div class="form-check">
                                        <input type="hidden" name="enable" value="0">
                                        <input type="checkbox" id="enable" name="enable" value="1" {{ old('enable', '1') ? 'checked' : '' }}>
                                        <label class="control-label" for="enable" style="font-weight: 600; color: #2b354e;">{{ trans('lang.enable') }}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="form-group col-12 text-center btm-btn mt-4">
                        <button type="submit" class="btn btn-save" style="background-color: #000; color: #fff; border-radius: 4px; padding: 8px 24px; font-weight: 600; border: none; min-width: 100px; margin-right: 10px;">
                            <i class="fa fa-save mr-1"></i> Save
                        </button>
                        <a href="{{ url('documents') }}" class="btn btn-back" style="background-color: #a0aec0; color: #fff; border-radius: 4px; padding: 8px 24px; font-weight: 600; min-width: 100px; text-decoration: none; display: inline-block;">
                            <i class="fa fa-undo mr-1"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $("#document-form").submit(function(e) {
        var title = $("#title").val().trim();
        if (title === "") {
            e.preventDefault();
            $(".error_top").show().html("<p>Please enter document title</p>");
            window.scrollTo(0, 0);
            return false;
        }
    });
});
</script>
@endsection
