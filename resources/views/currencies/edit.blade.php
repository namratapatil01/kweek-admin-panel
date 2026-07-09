@extends('layouts.app')

@section('style')
<style>
.currency-card {
    background: #fff;
    border: 1px solid #e0e4ea;
    border-radius: 6px;
    padding: 24px 28px 28px;
    margin-bottom: 30px;
}
.currency-card-title {
    display: inline-block;
    background: #111;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 6px 14px;
    border-radius: 4px;
    margin-bottom: 24px;
}
.currency-card-title i { margin-right: 4px; }
.cf-label {
    display: block;
    font-weight: 500;
    font-size: 13px;
    color: #333;
    margin-bottom: 6px;
}
.cf-input {
    display: block;
    width: 100%;
    border: 1px solid #d3d8e0;
    border-radius: 4px;
    font-size: 13px;
    color: #444;
    background: #fff;
    padding: 7px 10px;
    transition: border-color .18s, box-shadow .18s;
    height: 38px;
}
.cf-input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 2px rgba(78,115,223,.12);
    outline: none;
}
.cf-hint { font-size: 11.5px; color: #aaa; margin-top: 4px; }
.cf-check-row {
    margin-bottom: 12px;
}
.cf-check-row input[type="checkbox"] {
    margin-right: 6px;
    vertical-align: middle;
}
.cf-check-row label {
    font-size: 13px;
    color: #333;
    font-weight: 500;
    margin-bottom: 0;
    vertical-align: middle;
}
.cf-actions { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
.cf-btn-save {
    background: #111;
    border: none;
    color: #fff;
    font-size: 13px;
    padding: 8px 24px;
    border-radius: 4px;
    cursor: pointer;
}
.cf-btn-save:hover { background: #333; }
.cf-btn-back {
    background: #888;
    border: none;
    color: #fff;
    font-size: 13px;
    padding: 8px 24px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.cf-btn-back:hover { background: #666; color: #fff; text-decoration: none; }

#country_selector + .select2-container .select2-selection--single {
    border: 1px solid #d3d8e0 !important;
    border-radius: 4px !important;
    height: 38px !important;
    background: #fff !important;
}
#country_selector + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    font-size: 13px !important;
    color: #444 !important;
    padding-left: 10px !important;
}
#country_selector + .select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.img-flag { width: 20px; height: auto; margin-right: 5px; }
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

        <div class="currency-card">

            <div class="currency-card-title"><i class="mdi mdi-cash"></i> CURRENCIES SETTINGS</div>

            <form action="{{ route('settings.currencies.update', $record->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-4 position-relative">
                    <label class="cf-label">Country</label>
                    <select name="country" id="country_selector">
                        <option value="">Select Country</option>
                        @foreach ($newcountries as $name => $val)
                            <option value="{{ $name }}" {{ old('country', $record->country) == $name ? 'selected' : '' }}>
                                {{ $name }} (+{{ $val->phoneCode }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row mb-4">
                    <div class="col-md-6 form-group mb-0">
                        <label class="cf-label">Name</label>
                        <input type="text"
                               name="name"
                               class="cf-input"
                               value="{{ old('name', $record->name) }}"
                               required>
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="cf-label">Code</label>
                        <input type="text"
                               name="code"
                               class="cf-input"
                               value="{{ old('code', $record->code) }}"
                               required>
                    </div>
                </div>

                <div class="form-row mb-4">
                    <div class="col-md-6 form-group mb-0">
                        <label class="cf-label">Symbol</label>
                        <input type="text"
                               name="symbol"
                               class="cf-input"
                               value="{{ old('symbol', $record->symbol) }}">
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="cf-label">Digit After Decimal Point</label>
                        <input type="number"
                               name="decimal_degits"
                               class="cf-input"
                               value="{{ old('decimal_degits', $record->decimal_degits) }}"
                               min="0" max="10">
                        <div class="cf-hint">Enter Digit After Decimal Point ( Ex: insert 2 then it shows 0.00 amount)</div>
                    </div>
                </div>

                <div class="form-group cf-check-row">
                    <input type="hidden" name="symbolAtRight" value="0">
                    <input type="checkbox" name="symbolAtRight" id="symbolAtRight" value="1" {{ old('symbolAtRight', $record->symbolAtRight) ? 'checked' : '' }}>
                    <label for="symbolAtRight">Symbol At Right</label>
                </div>
                
                <div class="form-group cf-check-row">
                    <input type="hidden" name="isActive" value="0">
                    <input type="checkbox" name="isActive" id="isActive" value="1" {{ old('isActive', $record->isActive) ? 'checked' : '' }}>
                    <label for="isActive">Active</label>
                </div>

                <div class="cf-actions">
                    <button type="submit" class="cf-btn-save">
                        <i class="mdi mdi-content-save mr-1"></i> Save
                    </button>
                    <a href="{{ route('currencies') }}" class="cf-btn-back">
                        <i class="mdi mdi-arrow-left-bold-circle-outline mr-1"></i> Back
                    </a>
                </div>

            </form>
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
