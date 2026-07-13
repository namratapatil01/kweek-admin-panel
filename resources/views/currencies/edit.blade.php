@extends('layouts.app')

@section('style')
<style>
    .card-tab-badge {
        position: absolute;
        top: -18px;
        left: 20px;
        background-color: #000;
        color: #fff;
        padding: 8px 24px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 6px;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    
    .btn-save {
        background-color: #000 !important;
        border-color: #000 !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 30px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, opacity 0.2s;
    }
    
    .btn-save:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .btn-back {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 30px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, opacity 0.2s;
    }
    
    .btn-back:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    
    .form-control:focus {
        border-color: #000 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.05) !important;
    }

    .row.vendor_payout_create #country_selector + .select2-container {
        width: 100% !important;
    }
    .row.vendor_payout_create #country_selector + .select2-container .select2-selection--single {
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        height: 38px !important;
        background: #fff !important;
    }
    .row.vendor_payout_create #country_selector + .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        max-width: none !important;
        font-size: 13px !important;
        color: #444 !important;
        padding-left: 10px !important;
    }
    .row.vendor_payout_create #country_selector + .select2-container .select2-selection--single .select2-selection__rendered > span {
        font-size: 13px !important;
        font-weight: normal !important;
        color: #444 !important;
    }
    .row.vendor_payout_create #country_selector + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .img-flag { width: 20px; height: auto; margin-right: 5px; }

    /* Custom checkbox styling to match Image 1 (only checkmark, no blue box container when checked) */
    .custom-checkbox {
        padding-left: 0 !important;
    }
    .custom-checkbox .custom-control-input {
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    .custom-checkbox .custom-control-label {
        padding-left: 24px !important;
        position: relative;
        cursor: pointer;
        user-select: none;
    }
    .custom-checkbox .custom-control-label::before {
        display: none !important;
    }
    .custom-checkbox .custom-control-label::after {
        display: none !important;
    }
    /* Add a custom checkmark element when checked */
    .custom-checkbox .custom-control-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 3px;
        width: 15px;
        height: 15px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        background-color: #fff;
        display: block !important;
    }
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        content: '✔' !important;
        border: none !important;
        background-color: transparent !important;
        color: #000 !important;
        font-size: 14px;
        line-height: 15px;
        text-align: center;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $country) {
    $newcountries[$country->countryName] = $country;
    $newcountriesjs[$country->countryName] = $country->code;
}
?>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Currency</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('currencies') }}">Currencies List</a></li>
                <li class="breadcrumb-item active">Edit Currency</li>
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

        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <form action="{{ route('settings.currencies.update', $record->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card border-0" style="position: relative; margin-top: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                        <div class="card-tab-badge">CURRENCIES SETTINGS</div>
                        
                        <div class="card-body pt-5">
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label class="font-weight-bold" style="color: #2b354e;">Country</label>
                                    <select name="country" id="country_selector">
                                        <option value="">Select Country</option>
                                        @foreach ($newcountries as $name => $val)
                                            <option value="{{ $name }}" {{ old('country', $record->country) == $name ? 'selected' : '' }}>
                                                {{ $name }} (+{{ $val->phoneCode }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold" style="color: #2b354e;">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $record->name) }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold" style="color: #2b354e;">Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $record->code) }}" required>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold" style="color: #2b354e;">Symbol</label>
                                    <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $record->symbol) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold" style="color: #2b354e;">Digit After Decimal Point</label>
                                    <input type="number" name="decimal_degits" class="form-control" value="{{ old('decimal_degits', $record->decimal_degits) }}" min="0" max="10">
                                    <span class="form-text text-muted mt-1 d-block" style="font-size: 11.5px;">Enter Digit After Decimal Point ( Ex: insert 2 then it shows 0.00 amount)</span>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-12 form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="symbolAtRight" value="0">
                                        <input type="checkbox" class="custom-control-input" name="symbolAtRight" id="symbolAtRight" value="1" {{ old('symbolAtRight', $record->symbolAtRight) ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="symbolAtRight" style="color: #2b354e; cursor: pointer;">Symbol At Right</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-12 form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="isActive" value="0">
                                        <input type="checkbox" class="custom-control-input" name="isActive" id="isActive" value="1" {{ old('isActive', $record->isActive) ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="isActive" style="color: #2b354e; cursor: pointer;">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group col-12 text-center mt-4">
                        <button type="submit" class="btn btn-save"><i class="fa fa-save mr-1"></i> Save</button>
                        <a href="{{ route('currencies') }}" class="btn btn-back ml-2"><i class="fa fa-undo mr-1"></i> Back</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    var newcountriesjs = {!! json_encode($newcountriesjs) !!};
    
    $(document).ready(function() {
        $("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState,
            placeholder: "Select Country",
            allowClear: true,
            width: '100%'
        });
    });

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
        var isoCode = newcountriesjs[state.id];
        
        if (!isoCode) {
            return state.text;
        }
        
        var $state = $(
            '<span><img src="' + baseUrl + '/' + isoCode.toLowerCase() + '.svg" class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        return $state;
    }
</script>
@endsection
