@extends('layouts.app')

@section('content')
<style>
.page-wrapper {
    background-color: #fff !important; 
    min-height: 100vh;
}
.page-titles {
    border-bottom: 1px solid #f3f4f6;
    margin: 0 !important;
    padding: 20px 30px 15px 30px !important;
    background: #fff;
}
.custom-fieldset {
    border: 1px solid #e5e7eb !important;
    border-radius: 6px;
    padding: 50px 40px 30px 40px;
    margin: 40px auto 0 auto;
    background-color: #ffffff;
    max-width: 900px;
    position: relative;
}
.custom-legend {
    background: #000000;
    color: #ffffff;
    padding: 8px 18px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    position: absolute;
    top: -16px;
    left: 20px;
    margin: 0;
    border: none;
    line-height: 1;
    width: auto !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.custom-form-group {
    margin-bottom: 25px;
}
.custom-form-group label {
    display: block;
    font-size: 15px;
    color: #111827;
    margin-bottom: 10px;
    font-weight: 500;
}
.custom-form-group .form-control {
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    padding: 12px 15px;
    font-size: 14px;
    color: #4b5563;
    width: 100%;
    box-shadow: none;
    background-color: #fff;
}
.custom-form-group .form-control:focus {
    border-color: #d1d5db;
    outline: none;
}
.btm-btn-group {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 40px;
}
.btn-send {
    background-color: #000 !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 10px 24px !important;
    font-size: 15px;
    font-weight: 500 !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
}
.btn-back {
    background-color: #9ca3af !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 10px 24px !important;
    font-size: 15px;
    font-weight: 500 !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
}
</style>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor" style="font-weight: 600; color:#000; font-size:22px; margin-bottom:0;">Send Notification</h3>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb" style="background:transparent; padding:0; margin:0;">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}" style="color:#111827;">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.index') }}" style="color:#111827;">Send Notification</a></li>
                    <li class="breadcrumb-item active" style="color: #9ca3af;">Notification</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="container-fluid" style="padding: 0 30px;">
        <div class="card-body" style="padding: 0;">
            @if(session('success'))
                <div class="alert alert-success font-weight-bold">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger font-weight-bold">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route($routePrefix . '.store') }}" method="POST">
                @csrf
                <fieldset class="custom-fieldset">
                    <legend class="custom-legend">NOTIFICATION</legend>

                    <div class="custom-form-group">
                        <label>Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject') }}" required>
                    </div>

                    <div class="custom-form-group">
                        <label>Message</label>
                        <textarea class="form-control" name="message" rows="3" required>{{ old('message') }}</textarea>
                    </div>

                    <div class="custom-form-group">
                        <label>Send To</label>
                        <select name="role" class="form-control" required>
                            <option value="vendor" {{ old('role') == 'vendor' ? 'selected' : '' }}>Store</option>
                            <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                            <option value="provider" {{ old('role') == 'provider' ? 'selected' : '' }}>Provider</option>
                            <option value="worker" {{ old('role') == 'worker' ? 'selected' : '' }}>Worker</option>
                        </select>
                    </div>
                </fieldset>

                <div class="btm-btn-group">
                    <button type="submit" class="btn btn-send">
                        <i class="fa fa-save" style="margin-right: 5px;"></i> Send
                    </button>
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-back">
                        <i class="fa fa-undo" style="margin-right: 5px;"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
